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
