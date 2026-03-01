<?php

namespace App\Providers\Vrm;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Vormia\Vormia\Services\TokenService;

class TokenServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TokenService::class, fn () => new TokenService());
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only register token services if database connection is available and migrations have been run
        try {
            if (app()->runningInConsole()) {
                // Skip in console to avoid issues during migrations
                return;
            }
            
            // Check if we can connect to the database
            DB::connection()->getPdo();
            
            // Check if the auth_tokens table exists (basic check for migrations)
            if (Schema::hasTable(config('vormia.table_prefix', 'vrm_') . 'auth_tokens')) {
                // Token services are available, service is already registered
            }
        } catch (\Exception $e) {
            // Database not available or migrations not run, skip token services
            // This prevents errors when cloning a project before running migrations
        }
    }
}
