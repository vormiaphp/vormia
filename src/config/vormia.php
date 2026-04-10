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
    ],

];
