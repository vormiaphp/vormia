<?php

namespace App\Providers\Vrm;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\Vrm\GlobalDataService;

class GlobalDataServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     */
    public function register()
    {
        // Merge package config
        $this->mergeConfigFrom(__DIR__ . '../../../../config/vormia.php', 'vormia');

        // You can bind your service here if you want it injectable
        $this->app->singleton(GlobalDataService::class, function ($app) {
            return new GlobalDataService();
        });
    }

    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__ . '../../../../config/vormia.php' => config_path('vormia.php'),
        ], 'vormia-config');

        /*
        // Load views (optional, if your package has views)
        $this->loadViewsFrom(__DIR__ . '../../../../resources/views', 'vormia');

        // Load routes (optional, if needed)
        $this->loadRoutesFrom(__DIR__ . '../../../../routes/web.php');

        // Load migrations (optional)
        $this->loadMigrationsFrom(__DIR__ . '../../../../database/migrations');
        */

        // Share global data with all views
        View::share('global_data', app(GlobalDataService::class)());
    }
}
