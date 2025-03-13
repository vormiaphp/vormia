<?php

namespace VormiaCms\StarterKit;

use Illuminate\Support\ServiceProvider;
use Laravel\Foundation\Starters;

class StarterServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\InstallCommand::class,
            ]);
        }

        // Register the starter kit
        if (class_exists(Starters::class)) {
            Starters::register(VormiaStarterKit::class);
        }
    }
}
