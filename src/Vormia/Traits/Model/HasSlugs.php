<?php

namespace Vormia\Vormia\Traits\Model;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Vormia\Vormia\Models\SlugRegistry;

trait HasSlugs
{
    public static function bootHasSlugs(): void
    {
        static::created(function ($model) {
            if (method_exists($model, 'getSluggableField')) {
                $field = $model->getSluggableField();
                if (! empty($model->{$field})) {
                    $model->generateSlug($model->{$field});
                }
            }
        });

        static::updated(function ($model) {
            if ($model->shouldAutoUpdateSlug() && method_exists($model, 'getSluggableField')) {
                $field = $model->getSluggableField();
                if ($model->isDirty($field)) {
                    $model->updateSlug($model->{$field});
                }
            }
        });

        static::deleting(function ($model) {
            if ($model->isForceDeleting()) {
                $model->slugs()->forceDelete();
            } else {
                $model->slugs()->update(['is_active' => false]);
            }
        });
    }

    public function getSluggableField(): string
    {
        return 'name';
    }

    public function shouldAutoUpdateSlug(): bool
    {
        return false;
    }

    public function slugs(): MorphMany
    {
        return $this->morphMany(SlugRegistry::class, 'entity');
    }

    public function getSlug(): ?string
    {
        if ($this->relationLoaded('slugs')) {
            $slug = $this->slugs
                ->where('is_active', true)
                ->where('is_primary', true)
                ->first();

            if (! $slug) {
                return $this->ensurePrimarySlug();
            }

            return $slug->slug;
        }

        $slug = $this->slugs()
            ->where('is_active', true)
            ->where('is_primary', true)
            ->first();

        if (! $slug) {
            return $this->ensurePrimarySlug();
        }

        return $slug->slug;
    }

    public function generateSlug($text, $isPrimary = true): string
    {
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

    public function ensurePrimarySlug(): ?string
    {
        $primarySlug = $this->slugs()
            ->where('is_active', true)
            ->where('is_primary', true)
            ->first();

        if (! $primarySlug) {
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

    public function updateSlug($text): string
    {
        $this->slugs()
            ->where('is_primary', true)
            ->update(['is_primary' => false]);

        return $this->generateSlug($text, true);
    }

    public static function findBySlug($slug): ?static
    {
        $registry = SlugRegistry::where('slug', $slug)
            ->where('entity_type', self::class)
            ->where('is_active', true)
            ->first();

        return $registry ? $registry->entity : null;
    }
}
