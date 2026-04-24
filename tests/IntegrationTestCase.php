<?php

namespace VormiaPHP\Vormia\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;
use VormiaPHP\Vormia\VormiaServiceProvider;
use Workbench\App\Models\User;

abstract class IntegrationTestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            \Laravel\Sanctum\SanctumServiceProvider::class,
            VormiaServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.env', 'local');  // Avoid strict domain checks in AuthLoginController
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('cache.default', 'array');
        $app['config']->set('cache.stores.array', ['driver' => 'array']);
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('vormia.user_model', User::class);
        $app['config']->set('vormia.register_routes.api', true);
        $app['config']->set('vormia.register_routes.web', true);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../workbench/database/migrations');
    }
}
