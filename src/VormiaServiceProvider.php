<?php

namespace VormiaPHP\Vormia;

use Illuminate\Support\ServiceProvider;

class VormiaServiceProvider extends ServiceProvider
{
    public function boot()
    {

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\InstallCommand::class,
                Console\UpdateCommand::class,
                Console\RefreshCommand::class,
                Console\UninstallCommand::class,
            ]);
        }

        // The Laravel 12 Starter interface registration approach is still in development
        // For now, we'll just provide a command-based installation method
    }
}
