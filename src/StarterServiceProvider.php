<?php

namespace VormiaCms\StarterKit;

use Illuminate\Support\ServiceProvider;

class StarterServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\InstallCommand::class,
            ]);
        }

        // The Laravel 12 Starter interface registration approach is still in development
        // For now, we'll just provide a command-based installation method
    }
}
