<?php

namespace App\Providers\Vrm;

use Illuminate\Support\ServiceProvider;
use App\Services\Vrm\TokenService;

class TokenServiceProvider extends ServiceProvider
{

    // Todo: Register services
    public function register(): void
    {
        $this->app->singleton(TokenService::class, function ($app) {
            return new TokenService();
        });
    }
}
