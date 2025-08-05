<?php

namespace App\Models\Vrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Vrm\Model\HasSlugs;
use App\Traits\Vrm\Model\HasTaxonomyMeta;

class Taxonomy extends Model
{
    use SoftDeletes, HasSlugs, HasTaxonomyMeta;

    // Todo: Table name
    public function getTable()
    {
        return config('vormia.table_prefix') . 'taxonomies';
    }

    protected $fillable = [
        'type',
        'parent_id',
        'name',
        'position',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'position' => 'integer',
        'parent_id' => 'integer'
    ];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(Taxonomy::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Taxonomy::class, 'parent_id');
    }

    public function meta()
    {
        return $this->hasMany(TaxonomyMeta::class, 'taxonomy_id');
    }

    // Slug relationship is handled by HasSlugs trait
    // Use $this->slugs() to access slugs with proper entity_type filtering

    // Helper methods to work with meta
    public function getMetaValue($key, $default = null)
    {
        $meta = $this->meta()->where('key', $key)->first();
        return $meta ? $meta->value : $default;
    }

    public function setMetaValue($key, $value)
    {
        $meta = $this->meta()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        return $meta;
    }

    // Set multiple meta values at once
    public function syncMeta(array $metadata)
    {
        foreach ($metadata as $key => $value) {
            $this->setMetaValue($key, $value);
        }

        return $this;
    }

    // Get metadata as array
    public function getMetaArray()
    {
        return $this->meta->pluck('value', 'key')->toArray();
    }

    // Scope to filter by type
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Scope to get only active items
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Get all descendants (recursive)
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    // Get full hierarchy path (breadcrumb)
    public function getPathAttribute()
    {
        $path = collect([$this]);
        $parent = $this->parent;

        while ($parent) {
            $path->prepend($parent);
            $parent = $parent->parent;
        }

        return $path;
    }

    /* -------------------------------------------------------------------------------- */
    // Slug Methods
    /* -------------------------------------------------------------------------------- */

    /**
     * Define which field should be used for generating slugs.
     *
     * @return string
     */
    public function getSluggableField()
    {
        return 'name';
    }

    /**
     * Enable automatic slug updates for this model.
     *
     * @return bool
     */
    public function shouldAutoUpdateSlug()
    {
        // Development: Allow automatic updates
        if (app()->environment('local', 'development')) {
            return false;
        }

        // Production: Require manual approval
        return config('vormia.auto_update_slugs', false);
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field === 'slug' || $field === null) {
            return static::findBySlug($value);
        }

        return parent::resolveRouteBinding($value, $field);
    }
}
