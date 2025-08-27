<?php

namespace App\Providers\Vrm;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        // Only register utilities if database connection is available and migrations have been run
        try {
            if (app()->runningInConsole()) {
                // Skip in console to avoid issues during migrations
                return;
            }
            
            // Check if we can connect to the database
            DB::connection()->getPdo();
            
            // Check if the utilities table exists (basic check for migrations)
            if (Schema::hasTable(config('vormia.table_prefix', 'vrm_') . 'utilities')) {
                // Utilities are available, service is already registered
            }
        } catch (\Exception $e) {
            // Database not available or migrations not run, skip utilities
            // This prevents errors when cloning a project before running migrations
        }
    }
}
