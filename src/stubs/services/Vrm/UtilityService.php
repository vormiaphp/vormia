<?php

namespace App\Services\Vrm;

use Illuminate\Support\Facades\Cache;
use App\Models\Vrm\Utility;

class UtilityService
{

    protected $utilities = [];
    protected $loaded = false;
    protected $cacheKey = 'vrm_utility';
    protected $cacheTtl = 3600; // 1 hour

    /**
     * Get a utility value by key with optional default
     */
    public function get(string $key, $default = null, string $type = 'general')
    {
        $this->loadUtilitiesIfNeeded($type);

        return $this->utilities[$type][$key] ?? $default;
    }

    /**
     * Get a setting value directly from database and update cache
     * Bypasses cache completely for fresh data
     */
    public function fresh(string $key, $default = null, string $type = 'general')
    {
        // Get single setting directly from database
        $setting = Utility::where('type', $type)
            ->where('key', $key)
            ->where('flag', 1)
            ->first();

        $value = $setting ? $setting->value : $default;

        // Update the cache with fresh data for this type
        $this->refreshCacheForType($type);

        return $value;
    }

    /**
     * Get all utilities of a type directly from database and update cache
     */
    public function freshByType(string $type = 'general')
    {
        // Load directly from database
        $utilities = Utility::where('type', $type)
            ->where('flag', 1)
            ->pluck('value', 'key')
            ->toArray();

        // Update cache with fresh data
        $cacheKey = "{$this->cacheKey}_{$type}";
        Cache::put($cacheKey, $utilities, $this->cacheTtl);

        // Update in-memory cache
        $this->utilities[$type] = $utilities;

        return $utilities;
    }

    /**
     * Refresh cache for a specific type by loading from database
     */
    public function refreshCacheForType(string $type = 'general')
    {
        // Load fresh data from database
        $utilities = Utility::where('type', $type)
            ->where('flag', 1)
            ->pluck('value', 'key')
            ->toArray();

        // Update cache
        $cacheKey = "{$this->cacheKey}_{$type}";
        Cache::put($cacheKey, $utilities, $this->cacheTtl);

        // Update in-memory cache
        $this->utilities[$type] = $utilities;

        return $utilities;
    }

    /**
     * Force refresh all cached utilities from database
     */
    public function refreshAllCache()
    {
        // Get all distinct types from database
        $types = Utility::where('flag', 1)
            ->distinct()
            ->pluck('type')
            ->toArray();

        foreach ($types as $type) {
            $this->refreshCacheForType($type);
        }

        return $this;
    }

    /**
     * Get multiple utilities directly from database
     */
    public function freshMultiple(array $keys, string $type = 'general', $default = null)
    {
        // Get multiple utilities from database
        $utilities = Utility::where('type', $type)
            ->whereIn('key', $keys)
            ->where('flag', 1)
            ->pluck('value', 'key')
            ->toArray();

        // Fill in defaults for missing keys
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $utilities[$key] ?? $default;
        }

        // Refresh cache for this type
        $this->refreshCacheForType($type);

        return $result;
    }

    /**
     * Check if a setting exists in database (bypasses cache)
     */
    public function exists(string $key, string $type = 'general'): bool
    {
        return Utility::where('type', $type)
            ->where('key', $key)
            ->where('flag', 1)
            ->exists();
    }

    /**
     * Get all utilities of a specific type
     */
    public function getByType(string $type = 'general')
    {
        $this->loadUtilitiesIfNeeded($type);

        return $this->utilities[$type] ?? [];
    }

    /**
     * Load utilities from cache or database
     */
    protected function loadUtilitiesIfNeeded(string $type)
    {
        // If this type is already loaded, return early
        if (isset($this->utilities[$type])) {
            return;
        }

        // Try to get from cache first
        $cacheKey = "{$this->cacheKey}_{$type}";
        $utilities = Cache::get($cacheKey);

        if ($utilities === null) {
            // Not in cache, load from database
            $utilities = Utility::where('type', $type)
                ->where('flag', 1)
                ->pluck('value', 'key')
                ->toArray();

            // Store in cache for future requests
            Cache::put($cacheKey, $utilities, $this->cacheTtl);
        }

        $this->utilities[$type] = $utilities;
    }

    /**
     * Clear the utilities cache for a type
     */
    public function clearCache(?string $type = null)
    {
        if ($type) {
            Cache::forget("{$this->cacheKey}_{$type}");
            unset($this->utilities[$type]);
        } else {
            // Clear all utilities cache
            Cache::forget($this->cacheKey);
            $this->utilities = [];
            $this->loaded = false;
        }
    }

    /**
     * Set a setting value
     */
    public function set(string $key, $value, string $type = 'general', ?string $title = null)
    {
        Utility::updateOrCreate(
            ['type' => $type, 'key' => $key],
            [
                'value' => $value,
                'title' => $title ?? ucwords(str_replace('_', ' ', $key))
            ]
        );

        // Clear cache for this type
        $this->clearCache($type);
    }

    /**
     * Update multiple utilities at once and refresh cache
     */
    public function setMultiple(array $utilities, string $type = 'general')
    {
        foreach ($utilities as $key => $value) {
            Utility::updateOrCreate(
                ['type' => $type, 'key' => $key],
                [
                    'value' => $value,
                    'title' => ucwords(str_replace('_', ' ', $key))
                ]
            );
        }

        // Refresh cache once after all updates
        $this->refreshCacheForType($type);

        return $this;
    }

    /**
     * Get a utility value by key with optional default
     */
    public function type(string $type)
    {
        return new class($this, $type) {
            private $service;
            private $type;

            public function __construct($service, $type)
            {
                $this->service = $service;
                $this->type = $type;
            }

            public function get(string $key, $default = null)
            {
                return $this->service->get($key, $default, $this->type);
            }
        };
    }
}
