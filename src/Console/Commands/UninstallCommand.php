<?php

namespace Vormia\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;
use VormiaPHP\Vormia\VormiaVormia;

class UninstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vormia:uninstall {--force : Run without confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uninstall Vormia and remove related files, configuration, assets, and database tables';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧹 Uninstalling Vormia Package...');
        $this->line('');
        $this->comment('This will attempt to remove:');
        $this->line('  - Vormia config, stubs, and public assets');
        $this->line('  - Vormia entries in bootstrap/app.php (middleware/providers)');
        $this->line('  - VORMIA_* entries from .env and .env.example');
        $this->line('  - Vormia CSS/JS imports in resources/css/app.css and resources/js/app.js');
        $this->line('  - Vormia database tables (vrm_*)');
        $this->line('  - Vormia-related npm packages (jquery, flatpickr, select2, sweetalert2)');
        $this->line('  - Application caches (optimize:clear)');
        $this->line('');

        if (! $this->option('force')) {
            if (! $this->confirm('Do you really want to uninstall Vormia and remove these items?', false)) {
                $this->warn('Uninstall aborted.');
                return self::SUCCESS;
            }
        }

        $this->step('Running core Vormia uninstall (files & config)...');
        $this->runCoreUninstall();

        $this->step('Cleaning bootstrap/app.php and providers.php...');
        $this->cleanupBootstrapFiles();

        $this->step('Cleaning environment files (.env, .env.example)...');
        $this->cleanupEnvFiles();

        $this->step('Reverting CSS imports in resources/css/app.css...');
        $this->cleanupAppCss();

        $this->step('Reverting JS imports in resources/js/app.js...');
        $this->cleanupAppJs();

        $this->step('Removing Vormia asset directories (plugins/helpers under resources/js and css)...');
        $this->cleanupVormiaAssetDirectories();

        $this->step('Removing Vormia stubs from resources/stubs/vormia (if present)...');
        $this->cleanupStubs();

        $this->step('Dropping Vormia database tables...');
        $this->dropVormiaTables();

        $this->step('Uninstalling npm packages (best effort)...');
        $this->uninstallNpmPackages();

        $this->step('Clearing application caches...');
        $this->clearCaches();

        $this->newLine();
        $this->info('✅ Vormia has been uninstalled. You can now remove the composer package with:');
        $this->line('   composer remove vormiaphp/vormia');

        return self::SUCCESS;
    }

    private function step(string $message): void
    {
        $this->info("📦 {$message}");
    }

    private function runCoreUninstall(): void
    {
        $vormia = new VormiaVormia();

        if (! $vormia->uninstall()) {
            $this->warn('⚠️ Core Vormia uninstall reported a failure. Some files may remain.');
        } else {
            $this->info('   ✅ Core Vormia uninstall completed.');
        }
    }

    private function cleanupBootstrapFiles(): void
    {
        $bootstrapApp = base_path('bootstrap/app.php');
        $providersFile = base_path('bootstrap/providers.php');

        $middlewareMarkers = [];
        $providerMarkers = [];

        if (File::exists($bootstrapApp)) {
            $original = File::get($bootstrapApp);
            $lines = preg_split('/\\R/', $original);

            $lines = array_filter($lines, function ($line) use ($middlewareMarkers, $providerMarkers) {
                foreach (array_merge($middlewareMarkers, $providerMarkers) as $marker) {
                    if (strpos($line, $marker) !== false) {
                        return false;
                    }
                }
                return true;
            });

            File::put($bootstrapApp, implode(PHP_EOL, $lines));
            $this->info('   ✅ bootstrap/app.php cleaned.');
        } else {
            $this->warn('   ⚠️ bootstrap/app.php not found, skipping.');
        }

        if (File::exists($providersFile)) {
            $original = File::get($providersFile);
            $lines = preg_split('/\\R/', $original);

            $lines = array_filter($lines, function ($line) use ($providerMarkers) {
                foreach ($providerMarkers as $marker) {
                    if (strpos($line, $marker) !== false) {
                        return false;
                    }
                }
                return true;
            });

            File::put($providersFile, implode(PHP_EOL, $lines));
            $this->info('   ✅ bootstrap/providers.php cleaned.');
        }
    }

    private function cleanupEnvFiles(): void
    {
        $paths = [
            base_path('.env'),
            base_path('.env.example'),
        ];

        $patterns = [
            '# VORMIA CONFIG',
            'VORMIA_TABLE_PREFIX=',
            '# VORMIA SLUG CONFIG',
            'VORMIA_AUTO_UPDATE_SLUGS=',
            'VORMIA_SLUG_APPROVAL_REQUIRED=',
            'VORMIA_SLUG_HISTORY_ENABLED=',
            '# VORMIA MEDIAFORGE CONFIG',
            'VORMIA_MEDIAFORGE_DRIVER=',
            'VORMIA_MEDIAFORGE_DISK=',
            'VORMIA_MEDIAFORGE_URL_PASSTHROUGH=',
            'VORMIA_MEDIAFORGE_BASE_DIR=',
            'VORMIA_MEDIAFORGE_DEFAULT_QUALITY=',
            'VORMIA_MEDIAFORGE_DEFAULT_FORMAT=',
            'VORMIA_MEDIAFORGE_AUTO_OVERRIDE=',
            'VORMIA_MEDIAFORGE_PRESERVE_ORIGINALS=',
            'VORMIA_MEDIAFORGE_THUMBNAIL_KEEP_ASPECT_RATIO=',
            'VORMIA_MEDIAFORGE_THUMBNAIL_FROM_ORIGINAL=',
        ];

        foreach ($paths as $path) {
            if (! File::exists($path)) {
                continue;
            }

            $original = File::get($path);
            $lines = preg_split('/\\R/', $original);

            $filtered = array_filter($lines, function ($line) use ($patterns) {
                foreach ($patterns as $pattern) {
                    if (strpos($line, $pattern) === 0) {
                        return false;
                    }
                }
                return true;
            });

            File::put($path, implode(PHP_EOL, $filtered));
            $this->info("   ✅ Cleaned {$path}.");
        }
    }

    private function cleanupAppCss(): void
    {
        $appCssPath = resource_path('css/app.css');

        if (! File::exists($appCssPath)) {
            $this->warn('   ⚠️ resources/css/app.css not found, skipping.');
            return;
        }

        $content = File::get($appCssPath);
        $lines = preg_split('/\\R/', $content);

        $filtered = array_filter($lines, function ($line) {
            if (strpos($line, '/* Include Style */') !== false) {
                return false;
            }
            if (strpos($line, "@import '../../vendor/livewire/flux/dist/flux.css';") !== false) {
                return false;
            }
            if (strpos($line, "@import './plugins/style.min.css';") !== false) {
                return false;
            }
            return true;
        });

        File::put($appCssPath, implode(PHP_EOL, $filtered));
        $this->info('   ✅ resources/css/app.css cleaned.');
    }

    private function cleanupAppJs(): void
    {
        $appJsPath = resource_path('js/app.js');

        if (! File::exists($appJsPath)) {
            $this->warn('   ⚠️ resources/js/app.js not found, skipping.');
            return;
        }

        $content = File::get($appJsPath);
        $startMarker = '// Vormia imports and initialization';
        $startPos = strpos($content, $startMarker);

        if ($startPos === false) {
            $this->info('   ℹ️ No Vormia JS block found in app.js.');
            return;
        }

        $cleaned = substr($content, 0, $startPos);
        $cleaned = rtrim($cleaned) . PHP_EOL;

        File::put($appJsPath, $cleaned);
        $this->info('   ✅ resources/js/app.js cleaned.');
    }

    /**
     * Remove directories created by VormiaVormia::copyResourceFiles (pkg/js, pkg/css).
     */
    private function cleanupVormiaAssetDirectories(): void
    {
        $paths = [
            resource_path('js/plugins'),
            resource_path('js/helpers'),
            resource_path('css/plugins'),
        ];

        foreach ($paths as $dir) {
            if (! File::isDirectory($dir)) {
                continue;
            }

            File::deleteDirectory($dir);
            $this->info("   ✅ Removed {$dir}");
        }
    }

    private function cleanupStubs(): void
    {
        $stubsPath = resource_path('stubs/vormia');

        if (File::exists($stubsPath)) {
            File::deleteDirectory($stubsPath);
            $this->info('   ✅ resources/stubs/vormia removed.');
        } else {
            $this->info('   ℹ️ No resources/stubs/vormia directory found.');
        }
    }

    private function dropVormiaTables(): void
    {
        $prefix = config('vormia.table_prefix', 'vrm_');

        $tables = [
            'utilities',
            'roles',
            'permissions',
            'role_user',
            'permission_role',
            'user_meta',
            'taxonomies',
            'auth_tokens',
            'slug_registry',
        ];

        foreach ($tables as $table) {
            $name = $prefix . $table;

            if (Schema::hasTable($name)) {
                Schema::dropIfExists($name);
                $this->info("   ✅ Dropped table {$name}.");
            }
        }
    }

    private function uninstallNpmPackages(): void
    {
        $packageJsonPath = base_path('package.json');

        if (! File::exists($packageJsonPath)) {
            $this->info('   ℹ️ package.json not found. Skipping npm uninstall.');
            return;
        }

        $npmCheck = Process::run('npm --version');
        if (! $npmCheck->successful()) {
            $this->info('   ℹ️ npm is not available. Skipping npm uninstall.');
            return;
        }

        $packages = ['jquery', 'flatpickr', 'select2', 'sweetalert2'];

        foreach ($packages as $package) {
            $command = "npm uninstall {$package}";
            $this->line("   Uninstalling {$package}...");
            $result = Process::path(base_path())->run($command);

            if ($result->successful()) {
                $this->info("   ✅ {$package} uninstalled.");
            } else {
                $this->warn("   ⚠️ Failed to uninstall {$package}.");
            }
        }
    }

    private function clearCaches(): void
    {
        try {
            Artisan::call('optimize:clear');
            $this->info('   ✅ optimize:clear executed.');
        } catch (\Throwable $e) {
            $this->warn('   ⚠️ Failed to clear caches via artisan optimize:clear.');
        }
    }
}

