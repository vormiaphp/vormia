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

    'auto_update_slugs' => env('AUTO_UPDATE_SLUGS', false),
    'slug_approval_required' => env('SLUG_APPROVAL_REQUIRED', true),
    'slug_history_enabled' => env('SLUG_HISTORY_ENABLED', true),

];
