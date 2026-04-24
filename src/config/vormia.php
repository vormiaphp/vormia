<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services - VORMIA
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for vormia utilities.
    |
    */

    'table_prefix' => env('VORMIA_TABLE_PREFIX', 'vrm_'),

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The fully qualified class name of the User model. Uses auth config if not set.
    |
    */

    'user_model' => env('VORMIA_USER_MODEL') ?? null,

    /*
    |--------------------------------------------------------------------------
    | Route registration
    |--------------------------------------------------------------------------
    |
    | Default is false: routes/api.php is not loaded unless you enable it.
    | Set VORMIA_REGISTER_API_ROUTES=true (or vormia:install) to register API routes.
    |
    */

    'register_routes' => [
        'api' => filter_var(env('VORMIA_REGISTER_API_ROUTES', false), FILTER_VALIDATE_BOOLEAN),
    ],

    /*
    |--------------------------------------------------------------------------
    | Slug Management Settings
    |--------------------------------------------------------------------------
    |
    | Control automatic slug updates and approval workflows.
    |
    */

    'auto_update_slugs' => env('VORMIA_AUTO_UPDATE_SLUGS', false),
    'slug_approval_required' => env('VORMIA_SLUG_APPROVAL_REQUIRED', true),
    'slug_history_enabled' => env('VORMIA_SLUG_HISTORY_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | MediaForge Settings
    |--------------------------------------------------------------------------
    |
    | Configure MediaForge image processing driver and default settings.
    |
    */

    'mediaforge' => [
        // Storage rule:
        // - laravel (default): store via Laravel filesystem disks
        // - vormia: legacy behavior (write directly to webroot)
        'storage_rule' => env('VORMIA_MEDIAFORGE_STORAGE_RULE', 'laravel'),
        'driver' => env('VORMIA_MEDIAFORGE_DRIVER', 'auto'), // 'auto', 'imagick', 'gd'
        'disk' => env('VORMIA_MEDIAFORGE_DISK', 'public'),
        // If true, MediaForge::url() returns http(s) / data: inputs unchanged.
        // If false, MediaForge::url() will try to extract S3-style keys and rebuild via the disk.
        'url_passthrough' => env('VORMIA_MEDIAFORGE_URL_PASSTHROUGH', false),
        'base_dir' => env('VORMIA_MEDIAFORGE_BASE_DIR', 'uploads'),
        // Legacy (vormia) mode directories under public path.
        'public_dir' => env('VORMIA_MEDIAFORGE_PUBLIC_DIR', 'media'),
        'private_dir' => env('VORMIA_MEDIAFORGE_PRIVATE_DIR', 'media-private'),
        'default_quality' => env('VORMIA_MEDIAFORGE_DEFAULT_QUALITY', 85),
        'default_format' => env('VORMIA_MEDIAFORGE_DEFAULT_FORMAT', 'webp'),
        'auto_override' => env('VORMIA_MEDIAFORGE_AUTO_OVERRIDE', false),
        'preserve_originals' => env('VORMIA_MEDIAFORGE_PRESERVE_ORIGINALS', true),
        'thumbnail_keep_aspect_ratio' => env('VORMIA_MEDIAFORGE_THUMBNAIL_KEEP_ASPECT_RATIO', true),
        'thumbnail_from_original' => env('VORMIA_MEDIAFORGE_THUMBNAIL_FROM_ORIGINAL', false),

        // Preview URLs:
        // - auto: use `url()` (public) unless caller requests private
        // - public: always prefer `url()`
        // - private: prefer `temporaryUrl()` (signed) when supported
        'preview_mode' => env('VORMIA_MEDIAFORGE_PREVIEW_MODE', 'auto'),
        // Default signed URL lifetime in seconds (when using MediaForge::url(...)->private() with no explicit expiry).
        // - If missing: defaults to 86400 (24 hours)
        // - If present but empty (VORMIA_MEDIAFORGE_PREVIEW_PERIOD=): defaults to 3600 (1 hour)
        'preview_period_seconds' => env('VORMIA_MEDIAFORGE_PREVIEW_PERIOD'),
        // Legacy / compatibility default (used by previewUrl() when no other period is provided).
        'preview_expires_minutes' => env('VORMIA_MEDIAFORGE_PREVIEW_EXPIRES_MINUTES', 10),
    ],

];
