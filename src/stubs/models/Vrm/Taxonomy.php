<?php

namespace App\Models\Vrm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Taxonomy extends Model
{
    use SoftDeletes;

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
        return $this->belongsTo(Taxonomy::class, config('vormia.table_prefix') . 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Taxonomy::class, config('vormia.table_prefix') . 'parent_id');
    }

    public function meta()
    {
        return $this->hasMany(TaxonomyMeta::class, config('vormia.table_prefix') . 'taxonomy_id');
    }

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
}
