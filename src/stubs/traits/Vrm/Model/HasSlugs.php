<?php

namespace App\Traits\Vrm\Model;

use App\Models\Vrm\SlugRegistry;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasSlugs
{
    /**
     * Boot the trait.
     */
    public static function bootHasSlugs()
    {
        static::created(function ($model) {
            // Auto-generate a slug on model creation if sluggable field is defined
            if (method_exists($model, 'getSluggableField')) {
                $field = $model->getSluggableField();
                if (!empty($model->{$field})) {
                    $model->generateSlug($model->{$field});
                }
            }
        });

        static::updated(function ($model) {
            // Update slug if sluggable field has changed and auto-updates are enabled
            if ($model->shouldAutoUpdateSlug() && method_exists($model, 'getSluggableField')) {
                $field = $model->getSluggableField();
                if ($model->isDirty($field)) {
                    $model->updateSlug($model->{$field});
                }
            }
        });

        static::deleting(function ($model) {
            // Handle slug when model is deleted
            if ($model->isForceDeleting()) {
                $model->slugs()->forceDelete();
            } else {
                $model->slugs()->update(['is_active' => false]);
            }
        });
    }

    /**
     * Get the sluggable field for this model.
     * Override this method in your models.
     *
     * @return string
     */
    public function getSluggableField()
    {
        return 'name';
    }

    /**
     * Determine whether slugs should automatically update when the sluggable field changes.
     * By default, auto-updating is disabled. Override this in models to enable.
     *
     * @return bool
     */
    public function shouldAutoUpdateSlug()
    {
        return false;
    }

    /**
     * Define the relationship to slugs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function slugs(): MorphMany
    {
        return $this->morphMany(SlugRegistry::class, 'entity');
    }

    /**
     * Get the primary slug for this model.
     *
     * @return string|null
     */
    public function getSlug()
    {
        // If slugs are already loaded, use the collection
        if ($this->relationLoaded('slugs')) {
            $slug = $this->slugs
                ->where('is_active', true)
                ->where('is_primary', true)
                ->first();

            if (!$slug) {
                // No primary slug found in loaded collection, try to ensure one exists
                return $this->ensurePrimarySlug();
            }

            return $slug->slug;
        }

        // Otherwise, query the database and ensure primary slug exists
        $slug = $this->slugs()
            ->where('is_active', true)
            ->where('is_primary', true)
            ->first();

        if (!$slug) {
            // No primary slug found, try to ensure one exists
            return $this->ensurePrimarySlug();
        }

        return $slug->slug;
    }

    /**
     * Generate a new slug for this model.
     *
     * @param string $text
     * @param bool $isPrimary
     * @return string
     */
    public function generateSlug($text, $isPrimary = true)
    {
        // Make existing primary slugs non-primary if this is a new primary slug
        if ($isPrimary) {
            $this->slugs()
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        }

        $slug = SlugRegistry::generateUniqueSlug(
            $text,
            get_class($this),
            $this->getKey()
        );

        $this->slugs()->create([
            'slug' => $slug,
            'original_text' => $text,
            'is_primary' => $isPrimary,
            'is_active' => true,
        ]);

        return $slug;
    }

    /**
     * Ensure there's exactly one primary slug for this model.
     * If no primary slug exists, make the first active slug primary.
     *
     * @return string|null
     */
    public function ensurePrimarySlug()
    {
        $primarySlug = $this->slugs()
            ->where('is_active', true)
            ->where('is_primary', true)
            ->first();

        if (!$primarySlug) {
            // No primary slug found, make the first active slug primary
            $firstActiveSlug = $this->slugs()
                ->where('is_active', true)
                ->first();

            if ($firstActiveSlug) {
                $firstActiveSlug->update(['is_primary' => true]);
                return $firstActiveSlug->slug;
            }
        }

        return $primarySlug ? $primarySlug->slug : null;
    }

    /**
     * Update the primary slug for this model.
     *
     * @param string $text
     * @return string
     */
    public function updateSlug($text)
    {
        // Deactivate current primary slug
        $this->slugs()
            ->where('is_primary', true)
            ->update(['is_primary' => false]);

        // Generate new primary slug
        return $this->generateSlug($text, true);
    }

    /**
     * Find a model by its slug.
     *
     * @param string $slug
     * @return self|null
     */
    public static function findBySlug($slug)
    {
        $registry = SlugRegistry::where('slug', $slug)
            ->where('entity_type', self::class)
            ->where('is_active', true)
            ->first();

        return $registry ? $registry->entity : null;
    }
}
