<?php

namespace Vormia\Vormia\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vormia\Vormia\Traits\Model\HasSlugs;
use Vormia\Vormia\Traits\Model\HasTaxonomyMeta;

class Taxonomy extends Model
{
    use SoftDeletes;
    use HasSlugs;
    use HasTaxonomyMeta;

    public function getTable()
    {
        return config('vormia.table_prefix') . 'taxonomies';
    }

    protected $fillable = [
        'type',
        'parent_id',
        'name',
        'position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'position' => 'integer',
        'parent_id' => 'integer',
    ];

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

    public function getMeta($key, $default = null)
    {
        $meta = $this->meta()->where('key', $key)->first();

        return $meta ? $meta->value : $default;
    }

    public function setMeta($key, $value, $is_active = 1)
    {
        return $this->meta()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'is_active' => $is_active]
        );
    }

    public function syncMeta(array $metadata)
    {
        foreach ($metadata as $key => $value) {
            $this->setMeta($key, $value);
        }

        return $this;
    }

    public function getMetaArray()
    {
        return $this->meta->pluck('value', 'key')->toArray();
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function descendants()
    {
        return $this->children()->with('descendants');
    }

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

    public function getSluggableField(): string
    {
        return 'name';
    }

    public function shouldAutoUpdateSlug(): bool
    {
        if (app()->environment('local', 'development')) {
            return false;
        }

        return config('vormia.auto_update_slugs', false);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if ($field === 'slug' || $field === null) {
            return static::findBySlug($value);
        }

        return parent::resolveRouteBinding($value, $field);
    }
}
