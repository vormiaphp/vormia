<?php

namespace App\Models\Vrm;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SlugRegistry extends Model
{
    use SoftDeletes;

    // Todo: Table name
    public function getTable()
    {
        return config('vormia.table_prefix') . 'slug_registry';
    }

    protected $fillable = [
        'entity_type',
        'entity_id',
        'slug',
        'original_text',
        'is_active',
        'is_primary',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_primary' => 'boolean',
    ];

    /**
     * Get the related model instance.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function entity()
    {
        return $this->morphTo();
    }

    /**
     * Find a model by its slug.
     *
     * @param string $slug
     * @return mixed
     */
    public static function findBySlug($slug)
    {
        $registry = static::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$registry) {
            return null;
        }

        return $registry->entity;
    }

    /**
     * Generate a unique slug based on text.
     *
     * @param string $text
     * @param string|null $entityType
     * @param int|null $entityId
     * @return string
     */
    public static function generateUniqueSlug($text, $entityType = null, $entityId = null)
    {
        $baseSlug = Str::slug($text);
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)
            ->where(function ($query) use ($entityType, $entityId) {
                if ($entityType && $entityId) {
                    $query->where(function ($q) use ($entityType, $entityId) {
                        $q->where('entity_type', '!=', $entityType)
                            ->orWhere('entity_id', '!=', $entityId);
                    });
                }
            })
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
