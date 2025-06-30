<?php

namespace VormiaPHP\Vormia\Providers;

use Illuminate\Support\ServiceProvider;
use VormiaPHP\Vormia\Console\InstallCommand;
use VormiaPHP\Vormia\Console\UninstallCommand;

class VormiaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/vrm.php',
            'vrm'
        );

        $this->registerCommands();
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $this->publishes([
            __DIR__ . '/../../config/vrm.php' => config_path('vrm.php'),
        ], 'vormia-config');

        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/vormia'),
        ], 'vormia-views');

        $this->publishes([
            __DIR__ . '/../../public' => public_path('vendor/vormia'),
        ], 'vormia-assets');
    }

    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                UninstallCommand::class,
            ]);
        }
    }
}
