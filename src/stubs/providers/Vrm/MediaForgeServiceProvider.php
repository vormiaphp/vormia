<?php

namespace App\Providers\Vrm;

use Illuminate\Support\ServiceProvider;
use App\Helpers\Vrm\MediaForgeUpload;
use App\Services\Vrm\MediaForgeService;

class MediaForgeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('mediaforge', function () {
            return new MediaForgeUpload(new MediaForgeService());
        });
    }

    /**
     * Register facades.
     */
    public function boot(): void
    {
        // No alias needed here
    }
}
