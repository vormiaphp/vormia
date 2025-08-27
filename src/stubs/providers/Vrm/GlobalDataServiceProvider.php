<?php

namespace App\Providers\Vrm;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        // Only share global data if database connection is available and migrations have been run
        try {
            if (app()->runningInConsole()) {
                // Skip in console to avoid issues during migrations
                return;
            }

            // Check if we can connect to the database
            DB::connection()->getPdo();

            // Check if the utilities table exists (basic check for migrations)
            if (Schema::hasTable(config('vormia.table_prefix', 'vrm_') . 'utilities')) {
                // Share global data with all views
                View::share('global_data', app(GlobalDataService::class)());
            }
        } catch (\Exception $e) {
            // Database not available or migrations not run, skip global data
            // This prevents errors when cloning a project before running migrations
        }
    }
}
