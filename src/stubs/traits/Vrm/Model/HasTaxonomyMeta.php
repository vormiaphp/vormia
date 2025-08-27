<?php

namespace App\Traits\Vrm\Model;

trait HasTaxonomyMeta
{
    /**
     * Boot the trait.
     */
    public static function bootHasTaxonomyMeta()
    {
        static::created(function ($model) {
            $model->createDefaultTaxonomyMeta();
        });

        static::updated(function ($model) {
            $model->syncTaxonomyMeta();
        });
    }

    /**
     * Create default meta fields for new taxonomies.
     */
    public function createDefaultTaxonomyMeta()
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

    /**
     * Sync taxonomy data with meta fields.
     */
    public function syncTaxonomyMeta()
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

        // Update children count
        $this->updateChildrenCount();
    }

    /**
     * Get parent path as string.
     *
     * @return string
     */
    protected function getParentPath()
    {
        if (!$this->parent_id) {
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

    /**
     * Update children count in meta.
     *
     * @return void
     */
    public function updateChildrenCount()
    {
        $count = $this->children()->count();
        $this->setMeta('children_count', $count);
    }

    /**
     * Get taxonomy description.
     *
     * @return string|null
     */
    public function getDescriptionAttribute()
    {
        return $this->getMeta('description');
    }

    /**
     * Set taxonomy description.
     *
     * @param string $description
     * @return void
     */
    public function setDescriptionAttribute($description)
    {
        $this->setMeta('description', $description);
    }

    /**
     * Get taxonomy icon.
     *
     * @return string|null
     */
    public function getIconAttribute()
    {
        return $this->getMeta('icon');
    }

    /**
     * Set taxonomy icon.
     *
     * @param string $icon
     * @return void
     */
    public function setIconAttribute($icon)
    {
        $this->setMeta('icon', $icon);
    }

    /**
     * Get taxonomy color.
     *
     * @return string
     */
    public function getColorAttribute()
    {
        return $this->getMeta('color', '#6B7280');
    }

    /**
     * Set taxonomy color.
     *
     * @param string $color
     * @return void
     */
    public function setColorAttribute($color)
    {
        $this->setMeta('color', $color);
    }

    /**
     * Get taxonomy image.
     *
     * @return string|null
     */
    public function getImageAttribute()
    {
        return $this->getMeta('image');
    }

    /**
     * Set taxonomy image.
     *
     * @param string $image
     * @return void
     */
    public function setImageAttribute($image)
    {
        $this->setMeta('image', $image);
    }

    /**
     * Get SEO title.
     *
     * @return string
     */
    public function getSeoTitleAttribute()
    {
        return $this->getMeta('seo_title', $this->name);
    }

    /**
     * Set SEO title.
     *
     * @param string $title
     * @return void
     */
    public function setSeoTitleAttribute($title)
    {
        $this->setMeta('seo_title', $title);
    }

    /**
     * Get SEO description.
     *
     * @return string|null
     */
    public function getSeoDescriptionAttribute()
    {
        return $this->getMeta('seo_description');
    }

    /**
     * Set SEO description.
     *
     * @param string $description
     * @return void
     */
    public function setSeoDescriptionAttribute($description)
    {
        $this->setMeta('seo_description', $description);
    }

    /**
     * Get SEO keywords.
     *
     * @return string|null
     */
    public function getSeoKeywordsAttribute()
    {
        return $this->getMeta('seo_keywords');
    }

    /**
     * Set SEO keywords.
     *
     * @param string $keywords
     * @return void
     */
    public function setSeoKeywordsAttribute($keywords)
    {
        $this->setMeta('seo_keywords', $keywords);
    }

    /**
     * Get display order.
     *
     * @return int
     */
    public function getDisplayOrderAttribute()
    {
        return (int) $this->getMeta('display_order', $this->position ?? 0);
    }

    /**
     * Set display order.
     *
     * @param int $order
     * @return void
     */
    public function setDisplayOrderAttribute($order)
    {
        $this->setMeta('display_order', $order);
    }

    /**
     * Check if taxonomy is featured.
     *
     * @return bool
     */
    public function getIsFeaturedAttribute()
    {
        return (bool) $this->getMeta('is_featured', false);
    }

    /**
     * Set featured status.
     *
     * @param bool $featured
     * @return void
     */
    public function setIsFeaturedAttribute($featured)
    {
        $this->setMeta('is_featured', $featured);
    }

    /**
     * Get parent path.
     *
     * @return string
     */
    public function getParentPathAttribute()
    {
        return $this->getMeta('parent_path', $this->getParentPath());
    }

    /**
     * Get children count.
     *
     * @return int
     */
    public function getChildrenCountAttribute()
    {
        return (int) $this->getMeta('children_count', 0);
    }

    /**
     * Get items count.
     *
     * @return int
     */
    public function getItemsCountAttribute()
    {
        return (int) $this->getMeta('items_count', 0);
    }

    /**
     * Set items count.
     *
     * @param int $count
     * @return void
     */
    public function setItemsCountAttribute($count)
    {
        $this->setMeta('items_count', $count);
    }

    /**
     * Get template.
     *
     * @return string
     */
    public function getTemplateAttribute()
    {
        return $this->getMeta('template', 'default');
    }

    /**
     * Set template.
     *
     * @param string $template
     * @return void
     */
    public function setTemplateAttribute($template)
    {
        $this->setMeta('template', $template);
    }

    /**
     * Get redirect URL.
     *
     * @return string|null
     */
    public function getRedirectUrlAttribute()
    {
        return $this->getMeta('redirect_url');
    }

    /**
     * Set redirect URL.
     *
     * @param string $url
     * @return void
     */
    public function setRedirectUrlAttribute($url)
    {
        $this->setMeta('redirect_url', $url);
    }

    /**
     * Get external link.
     *
     * @return string|null
     */
    public function getExternalLinkAttribute()
    {
        return $this->getMeta('external_link');
    }

    /**
     * Set external link.
     *
     * @param string $link
     * @return void
     */
    public function setExternalLinkAttribute($link)
    {
        $this->setMeta('external_link', $link);
    }

    /**
     * Get meta robots.
     *
     * @return string
     */
    public function getMetaRobotsAttribute()
    {
        return $this->getMeta('meta_robots', 'index,follow');
    }

    /**
     * Set meta robots.
     *
     * @param string $robots
     * @return void
     */
    public function setMetaRobotsAttribute($robots)
    {
        $this->setMeta('meta_robots', $robots);
    }
}
