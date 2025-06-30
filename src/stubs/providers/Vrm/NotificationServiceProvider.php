<?php

namespace App\Providers\Vrm;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('notification', function ($app) {
            return new \App\Services\Vrm\NotificationService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Laravel 12's new way to ensure Blade is available
        if ($this->app->resolved('blade.compiler') || $this->app->bound('blade.compiler')) {
            $this->registerBladeDirectives();
        } else {
            $this->app->afterResolving('blade.compiler', function () {
                $this->registerBladeDirectives();
            });
        }
    }

    /**
     * Register blade directives.
     */
    protected function registerBladeDirectives(): void
    {
        Blade::directive('notifications', function () {
            return "<?php echo \App\Facades\Notification::render(session('notification')); ?>";
        });
    }
}
