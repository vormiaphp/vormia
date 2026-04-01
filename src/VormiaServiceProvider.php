<?php

namespace VormiaPHP\Vormia;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Vormia\Console\Commands\HelpCommand;
use Vormia\Console\Commands\InstallCommand;
use Vormia\Console\Commands\UninstallCommand;
use Vormia\Console\Commands\UpdateCommand;
use Illuminate\Support\Facades\Blade;
use Vormia\Vormia\Http\Middleware\ApiAuthenticate;
use Vormia\Vormia\Models\SlugRegistry;
use Vormia\Vormia\Services\MediaForge\MediaForgeManager;
use Vormia\Vormia\Services\NotificationService;
use Vormia\Vormia\Services\TokenService;

class VormiaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/vormia.php', 'vormia');

        $this->app->bind('vormia', function () {
            return new VormiaVormia();
        });

        $this->app->singleton(TokenService::class, fn () => new TokenService());

        $this->app->singleton('notification', fn () => new NotificationService());

        $this->app->singleton(MediaForgeManager::class);
        $this->app->alias(MediaForgeManager::class, 'vrm.mediaforge');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->registerBladeDirectives();

        if ($this->app->runningInConsole()) {
            $this->commands([
                HelpCommand::class,
                InstallCommand::class,
                UpdateCommand::class,
                UninstallCommand::class,
            ]);
        }

        $this->registerMiddleware();
        $this->registerRouteBindings();
        $this->registerRoutes();
        $this->registerPublishing();
    }

    protected function registerMiddleware(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('api-auth', ApiAuthenticate::class);
        $router->aliasMiddleware('role', \Vormia\Vormia\Http\Middleware\CheckRole::class);
        $router->aliasMiddleware('permission', \Vormia\Vormia\Http\Middleware\CheckPermission::class);
        $router->aliasMiddleware('authority', \Vormia\Vormia\Http\Middleware\CheckAuthority::class);
        $router->aliasMiddleware('module', \Vormia\Vormia\Http\Middleware\CheckModule::class);
    }

    protected function registerRouteBindings(): void
    {
        Route::bind('anySlug', function ($value) {
            return SlugRegistry::findBySlug($value);
        });
    }

    protected function registerRoutes(): void
    {
        $this->app->booted(function () {
            Route::prefix('api')
                ->middleware('api')
                ->group(__DIR__ . '/../routes/api.php');
        });
    }

    protected function registerBladeDirectives(): void
    {
        Blade::directive('notifications', function () {
            return "<?php echo \Vormia\Vormia\Services\NotificationService::render(session('notification')); ?>";
        });
    }

    protected function registerPublishing(): void
    {
        $this->publishes([
            __DIR__ . '/config/vormia.php' => config_path('vormia.php'),
        ], 'vormia-config');

        $this->publishes([
            __DIR__ . '/database/migrations' => database_path('migrations'),
        ], 'vormia-migrations');

        $this->publishes([
            __DIR__ . '/stubs' => resource_path('stubs/vormia'),
        ], 'vormia-stubs');
    }
}

