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
        'driver' => env('VORMIA_MEDIAFORGE_DRIVER', 'auto'), // 'auto', 'imagick', 'gd'
        'default_quality' => env('VORMIA_MEDIAFORGE_DEFAULT_QUALITY', 85),
        'default_format' => env('VORMIA_MEDIAFORGE_DEFAULT_FORMAT', 'webp'),
        'auto_override' => env('VORMIA_MEDIAFORGE_AUTO_OVERRIDE', false),
        'preserve_originals' => env('VORMIA_MEDIAFORGE_PRESERVE_ORIGINALS', true),
    ],

];
