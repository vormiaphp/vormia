<?php

namespace Vormia\Vormia\Traits\Model;

trait HasTaxonomyMeta
{
    public static function bootHasTaxonomyMeta(): void
    {
        static::created(function ($model) {
            $model->createDefaultTaxonomyMeta();
        });

        static::updated(function ($model) {
            $model->syncTaxonomyMeta();
        });
    }

    public function createDefaultTaxonomyMeta(): void
    {
        $defaultMeta = [
            'icon' => null,
            'is_featured' => false,
            'children_count' => 0,
            'items_count' => 0,
        ];

        foreach ($defaultMeta as $key => $value) {
            if ($value !== null || in_array($key, ['description', 'icon', 'image'])) {
                $this->setMeta($key, $value);
            }
        }
    }

    public function syncTaxonomyMeta(): void
    {
        $syncFields = [
            'seo_title' => $this->name,
            'display_order' => $this->position ?? 0,
            'parent_path' => $this->getParentPath(),
            'updated_at_formatted' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];

        foreach ($syncFields as $key => $value) {
            $this->setMeta($key, $value);
        }

        $this->updateChildrenCount();
    }

    protected function getParentPath(): string
    {
        if (! $this->parent_id) {
            return '';
        }

        $path = [];
        $parent = $this->parent;

        while ($parent) {
            $path[] = $parent->name;
            $parent = $parent->parent;
        }

        return implode(' > ', array_reverse($path));
    }

    public function updateChildrenCount(): void
    {
        $count = $this->children()->count();
        $this->setMeta('children_count', $count);
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->getMeta('description');
    }

    public function setDescriptionAttribute($description): void
    {
        $this->setMeta('description', $description);
    }

    public function getIconAttribute(): ?string
    {
        return $this->getMeta('icon');
    }

    public function setIconAttribute($icon): void
    {
        $this->setMeta('icon', $icon);
    }

    public function getColorAttribute(): string
    {
        return $this->getMeta('color', '#6B7280');
    }

    public function setColorAttribute($color): void
    {
        $this->setMeta('color', $color);
    }

    public function getImageAttribute(): ?string
    {
        return $this->getMeta('image');
    }

    public function setImageAttribute($image): void
    {
        $this->setMeta('image', $image);
    }

    public function getSeoTitleAttribute(): string
    {
        return $this->getMeta('seo_title', $this->name);
    }

    public function setSeoTitleAttribute($title): void
    {
        $this->setMeta('seo_title', $title);
    }

    public function getSeoDescriptionAttribute(): ?string
    {
        return $this->getMeta('seo_description');
    }

    public function setSeoDescriptionAttribute($description): void
    {
        $this->setMeta('seo_description', $description);
    }

    public function getSeoKeywordsAttribute(): ?string
    {
        return $this->getMeta('seo_keywords');
    }

    public function setSeoKeywordsAttribute($keywords): void
    {
        $this->setMeta('seo_keywords', $keywords);
    }

    public function getDisplayOrderAttribute(): int
    {
        return (int) $this->getMeta('display_order', $this->position ?? 0);
    }

    public function setDisplayOrderAttribute($order): void
    {
        $this->setMeta('display_order', $order);
    }

    public function getIsFeaturedAttribute(): bool
    {
        return (bool) $this->getMeta('is_featured', false);
    }

    public function setIsFeaturedAttribute($featured): void
    {
        $this->setMeta('is_featured', $featured);
    }

    public function getParentPathAttribute(): string
    {
        return $this->getMeta('parent_path', $this->getParentPath());
    }

    public function getChildrenCountAttribute(): int
    {
        return (int) $this->getMeta('children_count', 0);
    }

    public function getItemsCountAttribute(): int
    {
        return (int) $this->getMeta('items_count', 0);
    }

    public function setItemsCountAttribute($count): void
    {
        $this->setMeta('items_count', $count);
    }

    public function getTemplateAttribute(): string
    {
        return $this->getMeta('template', 'default');
    }

    public function setTemplateAttribute($template): void
    {
        $this->setMeta('template', $template);
    }

    public function getRedirectUrlAttribute(): ?string
    {
        return $this->getMeta('redirect_url');
    }

    public function setRedirectUrlAttribute($url): void
    {
        $this->setMeta('redirect_url', $url);
    }

    public function getExternalLinkAttribute(): ?string
    {
        return $this->getMeta('external_link');
    }

    public function setExternalLinkAttribute($link): void
    {
        $this->setMeta('external_link', $link);
    }

    public function getMetaRobotsAttribute(): string
    {
        return $this->getMeta('meta_robots', 'index,follow');
    }

    public function setMetaRobotsAttribute($robots): void
    {
        $this->setMeta('meta_robots', $robots);
    }
}
