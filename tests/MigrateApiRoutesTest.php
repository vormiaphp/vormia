<?php

namespace VormiaPHP\Vormia\Tests;

use Illuminate\Support\Facades\File;
use Vormia\Console\Commands\MigrateApiRoutesCommand;
use VormiaPHP\Vormia\VormiaVormia;

class MigrateApiRoutesTest extends IntegrationTestCase
{
    private ?string $apiRoutesPath = null;

    private ?string $apiRoutesBackup = null;

    private ?string $envPath = null;

    private ?string $envBackup = null;

    protected function tearDown(): void
    {
        if ($this->apiRoutesPath !== null && $this->apiRoutesBackup !== null) {
            File::put($this->apiRoutesPath, $this->apiRoutesBackup);
        } elseif ($this->apiRoutesPath !== null && File::exists($this->apiRoutesPath)) {
            File::delete($this->apiRoutesPath);
        }

        if ($this->envPath !== null && $this->envBackup !== null) {
            File::put($this->envPath, $this->envBackup);
        }

        parent::tearDown();
    }

    public function test_migrate_command_signature(): void
    {
        $command = new MigrateApiRoutesCommand();
        $reflection = new \ReflectionClass($command);
        $signatureProperty = $reflection->getProperty('signature');
        $signatureProperty->setAccessible(true);

        $this->assertSame(
            'vormia:migrate-api-routes {--dry-run : Preview changes without writing files or clearing caches}',
            $signatureProperty->getValue($command),
        );
    }

    public function test_remove_v1_auth_route_block(): void
    {
        $sample = <<<'PHP'
<?php

use Vormia\Vormia\Http\Controllers\Api\AuthLoginController;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthLoginController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthLoginController::class, 'logout']);
    });
});

Route::get('/health', fn () => 'ok');
PHP;

        $vormia = new VormiaVormia();
        $removeBlock = new \ReflectionMethod($vormia, 'removeV1AuthRouteBlock');
        $removeBlock->setAccessible(true);
        $withoutBlock = $removeBlock->invoke($vormia, $sample);

        $removeImport = new \ReflectionMethod($vormia, 'removeUnusedAuthLoginControllerImport');
        $removeImport->setAccessible(true);
        $result = $removeImport->invoke($vormia, $withoutBlock);

        $this->assertIsString($result);
        $this->assertStringNotContainsString("prefix('v1')", $result);
        $this->assertStringNotContainsString('AuthLoginController', $result);
        $this->assertStringContainsString("/health", $result);
    }

    public function test_migrate_command_scrubs_host_v1_routes_and_appends_env_flag(): void
    {
        $routesDir = base_path('routes');
        File::ensureDirectoryExists($routesDir);

        $this->apiRoutesPath = $routesDir . '/api.php';
        $this->apiRoutesBackup = File::exists($this->apiRoutesPath)
            ? File::get($this->apiRoutesPath)
            : null;

        File::put($this->apiRoutesPath, <<<'PHP'
<?php

use Vormia\Vormia\Http\Controllers\Api\AuthLoginController;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthLoginController::class, 'login']);
});
PHP);

        $this->envPath = base_path('.env');
        $this->envBackup = File::exists($this->envPath) ? File::get($this->envPath) : '';

        File::put($this->envPath, "APP_NAME=Workbench\n");

        $this->artisan('vormia:migrate-api-routes')
            ->assertSuccessful();

        $updatedRoutes = File::get($this->apiRoutesPath);
        $this->assertStringNotContainsString("prefix('v1')", $updatedRoutes);
        $this->assertStringNotContainsString('AuthLoginController', $updatedRoutes);

        $updatedEnv = File::get($this->envPath);
        $this->assertStringContainsString('VORMIA_REGISTER_API_ROUTES=true', $updatedEnv);
    }

    public function test_migrate_command_dry_run_does_not_modify_files(): void
    {
        $routesDir = base_path('routes');
        File::ensureDirectoryExists($routesDir);

        $this->apiRoutesPath = $routesDir . '/api.php';
        $this->apiRoutesBackup = File::exists($this->apiRoutesPath)
            ? File::get($this->apiRoutesPath)
            : null;

        $originalRoutes = <<<'PHP'
<?php

use Vormia\Vormia\Http\Controllers\Api\AuthLoginController;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthLoginController::class, 'login']);
});
PHP;

        File::put($this->apiRoutesPath, $originalRoutes);

        $this->envPath = base_path('.env');
        $this->envBackup = File::exists($this->envPath) ? File::get($this->envPath) : '';
        File::put($this->envPath, "APP_NAME=Workbench\n");

        $this->artisan('vormia:migrate-api-routes', ['--dry-run' => true])
            ->assertSuccessful();

        $this->assertSame($originalRoutes, File::get($this->apiRoutesPath));
        $this->assertStringNotContainsString('VORMIA_REGISTER_API_ROUTES', File::get($this->envPath));
    }
}
