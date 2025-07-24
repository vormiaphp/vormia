<?php

namespace App\Providers\Vrm;

use Illuminate\Support\ServiceProvider;
use App\Services\Vrm\MediaForgeService;

class MediaForgeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Check if intervention/image is installed
        if (!class_exists('Intervention\Image\ImageManager')) {
            throw new \RuntimeException(
                'The intervention/image package is required for MediaForgeService. ' .
                    'Please install it by running: composer require intervention/image'
            );
        }

        $this->app->singleton('mediaforge', function () {
            return new MediaForgeService();
        });
    }

    /**
     * Register facades.
     */
    public function boot(): void
    {
        // Register facade alias
        $this->app->alias('mediaforge', \App\Facades\Vrm\MediaForge::class);
    }
}
