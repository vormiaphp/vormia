<?php

namespace VormiaPHP\Vormia;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Vormia\Console\Commands\InstallCommand;
use Vormia\Console\Commands\HelpCommand;
use Vormia\Console\Commands\UpdateCommand;
use Vormia\Console\Commands\UninstallCommand;

class VormiaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/vormia.php', 'vormia');

        // Register facades
        $this->app->bind('vormia', function () {
            return new \VormiaPHP\Vormia();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                HelpCommand::class,
                UpdateCommand::class,
                UninstallCommand::class,
            ]);
        }

        // Publish config
        $this->publishes([
            __DIR__ . '/config/vormia.php' => config_path('vormia.php'),
        ], 'vormia-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/database/migrations' => database_path('migrations'),
        ], 'vormia-migrations');

        // Publish all files
        $this->publishes([
            __DIR__ . '/app/Facades/Vrm' => app_path('Facades/Vrm'),
            // __DIR__ . '/app/Helpers/Vrm' => app_path('Helpers/Vrm'),
            __DIR__ . '/app/Jobs/Vrm' => app_path('Jobs/Vrm'),
            __DIR__ . '/app/Http/Middleware/Vrm' => app_path('Http/Middleware/Vrm'),
            __DIR__ . '/app/Models/Vrm' => app_path('Models/Vrm'),
            __DIR__ . '/app/Providers/Vrm' => app_path('Providers/Vrm'),
            __DIR__ . '/app/Services/Vrm' => app_path('Services/Vrm'),
            __DIR__ . '/app/Traits/Vrm' => app_path('Traits/Vrm'),
        ], 'vormia-files');

        // Publish stubs
        $this->publishes([
            __DIR__ . '/stubs' => resource_path('stubs/vormia'),
        ], 'vormia-stubs');
    }
}
