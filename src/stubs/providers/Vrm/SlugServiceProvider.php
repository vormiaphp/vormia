<?php

namespace App\Providers\Vrm;

use App\Models\Vrm\SlugRegistry;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class SlugServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Global route model binding for any slug
        Route::bind('anySlug', function ($value) {
            return SlugRegistry::findBySlug($value);
        });
    }
}
