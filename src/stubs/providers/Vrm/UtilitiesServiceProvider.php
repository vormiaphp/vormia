<?php

namespace App\Providers\Vrm;

use Illuminate\Support\ServiceProvider;
use App\Services\Vrm\UtilityService;

class UtilitiesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton(UtilityService::class, function ($app) {
            return new UtilityService();
        });

        // Add a convenient alias
        $this->app->alias(UtilityService::class, 'vrm.utilities');
    }

    /**
     * Register facades.
     */
    public function boot(): void
    {
        // No alias needed here
    }
}
