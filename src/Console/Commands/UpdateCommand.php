<?php

namespace Vormia\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class UpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vormia:update {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Vormia package files (removes old files and copies fresh ones)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Updating Vormia Package...');
        $this->newLine();

        $force = $this->option('force');

        // Warning message
        $this->warn('⚠️  WARNING: This will replace existing Vormia files with fresh copies.');
        $this->warn('   Make sure you have backed up any custom modifications.');
        $this->newLine();

        if (!$force && !$this->confirm('Do you want to continue with the update?', false)) {
            $this->info('❌ Update cancelled.');
            return;
        }

        // Check for required dependencies
        $this->checkRequiredDependencies();

        // Step 1: Create backup
        $this->step('Creating backup of existing files...');
        $this->createBackup();

        // Step 2: Remove old files
        $this->step('Removing old Vormia files...');
        $this->removeOldFiles();

        // Step 3: Publish fresh files
        $this->step('Publishing fresh package files...');
        $this->publishFreshFiles();

        // Step 4: Update config if needed
        $this->step('Updating configuration...');
        $this->updateConfig();

        // Step 5: Clear caches
        $this->step('Clearing application caches...');
        $this->clearCaches();

        $this->displayCompletionMessage();
    }

    /**
     * Check for required dependencies
     */
    private function checkRequiredDependencies(): void
    {
        $this->step('Checking required dependencies...');

        // Check for intervention/image
        if (!class_exists('Intervention\Image\ImageManager')) {
            $this->warn('⚠️  The intervention/image package is required for MediaForge functionality.');
            $this->line('   Please install it by running: composer require intervention/image');
            $this->line('   This package is needed for image processing features like resizing, compression, and watermarking.');
            $this->newLine();
        } else {
            $this->info('✅ intervention/image package is installed.');
        }
    }

    /**
     * Display a step message
     */
    private function step($message)
    {
        $this->info("📦 {$message}");
    }

    /**
     * Create backup of existing files
     */
    private function createBackup()
    {
        $backupDir = storage_path('app/vormia-backups/' . date('Y-m-d-H-i-s'));

        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $filesToBackup = [
            app_path('Facades/Vrm') => $backupDir . '/Facades/Vrm',
            // app_path('Helpers/Vrm') => $backupDir . '/Helpers/Vrm',
            app_path('Jobs/Vrm') => $backupDir . '/Jobs/Vrm',
            app_path('Http/Middleware/Vrm') => $backupDir . '/Http/Middleware/Vrm',
            app_path('Models/Vrm') => $backupDir . '/Models/Vrm',
            app_path('Providers/Vrm') => $backupDir . '/Providers/Vrm',
            app_path('Services/Vrm') => $backupDir . '/Services/Vrm',
            app_path('Traits/Vrm') => $backupDir . '/Traits/Vrm',
            config_path('vormia.php') => $backupDir . '/config/vormia.php',
            app_path('Models/User.php') => $backupDir . '/Models/User.php',
            base_path('bootstrap/app.php') => $backupDir . '/bootstrap/app.php',
        ];

        foreach ($filesToBackup as $source => $destination) {
            if (File::exists($source)) {
                if (File::isDirectory($source)) {
                    File::copyDirectory($source, $destination);
                } else {
                    File::ensureDirectoryExists(dirname($destination));
                    File::copy($source, $destination);
                }
            }
        }

        $this->info("✅ Backup created in: {$backupDir}");
    }

    /**
     * Remove old Vormia files
     */
    private function removeOldFiles()
    {
        $directoriesToRemove = [
            // app_path('Helpers/Vrm'),
            app_path('Facades/Vrm'),
            app_path('Jobs/Vrm'),
            app_path('Http/Middleware/Vrm'),
            app_path('Models/Vrm'),
            app_path('Providers/Vrm'),
            app_path('Services/Vrm'),
            app_path('Traits/Vrm'),
            public_path('vendor/vormia'),
        ];

        foreach ($directoriesToRemove as $directory) {
            if (File::exists($directory)) {
                File::deleteDirectory($directory);
                $this->line("  Removed: {$directory}");
            }
        }

        // Remove old migration files (those with vormia prefix)
        $migrationPath = database_path('migrations');
        if (File::isDirectory($migrationPath)) {
            foreach (File::files($migrationPath) as $file) {
                if (strpos($file->getFilename(), 'vrm_') !== false) {
                    File::delete($file->getPathname());
                    $this->line("  Removed migration: " . $file->getFilename());
                }
            }
        }

        $this->info('✅ Old files removed successfully.');
    }

    /**
     * Publish fresh files from package
     */
    private function publishFreshFiles()
    {
        // Publish config
        Artisan::call('vendor:publish', [
            '--provider' => 'VormiaPHP\Vormia\VormiaServiceProvider',
            '--tag' => 'vormia-config',
            '--force' => true
        ]);

        // Publish all package files
        Artisan::call('vendor:publish', [
            '--provider' => 'VormiaPHP\Vormia\VormiaServiceProvider',
            '--tag' => 'vormia-files',
            '--force' => true
        ]);

        // Publish migrations if any new ones exist
        Artisan::call('vendor:publish', [
            '--provider' => 'VormiaPHP\Vormia\VormiaServiceProvider',
            '--tag' => 'vormia-migrations',
            '--force' => true
        ]);

        $this->info('✅ Fresh files published successfully.');
    }

    /**
     * Update configuration if needed
     */
    private function updateConfig()
    {
        // Check if we need to update bootstrap/app.php
        $this->updateBootstrapApp();

        // Check if we need to update User model
        $this->checkUserModel();

        $this->info('✅ Configuration updated successfully.');
    }

    /**
     * Update bootstrap/app.php with any missing configurations
     */
    private function updateBootstrapApp()
    {
        $bootstrapPath = base_path('bootstrap/app.php');

        if (!File::exists($bootstrapPath)) {
            $this->warn('⚠️  bootstrap/app.php not found. Skipping bootstrap update.');
            return;
        }

        $content = File::get($bootstrapPath);
        $updated = false;

        // Check for middleware aliases
        $middlewareAliases = [
            'role' => '\\App\\Http\\Middleware\\Vrm\\CheckRole::class',
            'module' => '\\App\\Http\\Middleware\\Vrm\\CheckModule::class',
            'permission' => '\\App\\Http\\Middleware\\Vrm\\CheckPermission::class',
        ];

        foreach ($middlewareAliases as $alias => $class) {
            if (strpos($content, "'$alias' =>") === false) {
                $this->line("  Adding missing middleware alias: $alias");
                $updated = true;
            }
        }

        // Check for providers
        $providers = [
            'App\\Providers\\Vrm\\NotificationServiceProvider::class',
            'App\\Providers\\Vrm\\TokenServiceProvider::class',
            'App\\Providers\\Vrm\\MediaForgeServiceProvider::class',
            'App\\Providers\\Vrm\\UtilitiesServiceProvider::class',
            'App\\Providers\\Vrm\\GlobalDataServiceProvider::class',
        ];

        foreach ($providers as $provider) {
            if (strpos($content, $provider) === false) {
                $this->line("  Adding missing provider: " . basename($provider));
                $updated = true;
            }
        }

        if ($updated) {
            // Create backup
            File::copy($bootstrapPath, $bootstrapPath . '.backup.' . date('Y-m-d-H-i-s'));

            // Add missing configurations
            $this->addMissingBootstrapConfigurations($bootstrapPath);
            $this->line("  ✅ bootstrap/app.php updated (backup created)");
        } else {
            $this->line("  ✅ bootstrap/app.php is up to date");
        }

        // --- Manual fallback instructions ---
        $finalContent = File::get($bootstrapPath);
        $missing = [];
        if (strpos($finalContent, "'role' => \\App\\Http\\Middleware\\Vrm\\CheckRole::class") === false) $missing[] = "'role' => \\App\\Http\\Middleware\\Vrm\\CheckRole::class";
        if (strpos($finalContent, "'module' => \\App\\Http\\Middleware\\Vrm\\CheckModule::class") === false) $missing[] = "'module' => \\App\\Http\\Middleware\\Vrm\\CheckModule::class";
        if (strpos($finalContent, "'permission' => \\App\\Http\\Middleware\\Vrm\\CheckPermission::class") === false) $missing[] = "'permission' => \\App\\Http\\Middleware\\Vrm\\CheckPermission::class";
        $providersList = [
            'App\\Providers\\Vrm\\NotificationServiceProvider::class',
            'App\\Providers\\Vrm\\TokenServiceProvider::class',
            'App\\Providers\\Vrm\\MediaForgeServiceProvider::class',
            'App\\Providers\\Vrm\\UtilitiesServiceProvider::class',
            'App\\Providers\\Vrm\\GlobalDataServiceProvider::class',
        ];
        $missingProviders = array_filter($providersList, fn($p) => strpos($finalContent, $p) === false);
        if ($missing || $missingProviders) {
            $this->warn('Some middleware aliases or providers could not be added automatically. Please add them manually to bootstrap/app.php:');
            if ($missing) {
                $this->line("\nAdd these to your middleware aliases array:");
                foreach ($missing as $m) $this->line('    ' . $m);
            }
            if ($missingProviders) {
                $this->line("\nAdd these to your providers array:");
                foreach ($missingProviders as $p) $this->line('    ' . $p . ',');
            }
        }
    }

    /**
     * Add missing configurations to bootstrap/app.php
     */
    private function addMissingBootstrapConfigurations($bootstrapPath)
    {
        $content = File::get($bootstrapPath);

        // Add middleware aliases
        $middlewareAliases = "
            'role' => \\App\\Http\\Middleware\\Vrm\\CheckRole::class,
            'module' => \\App\\Http\\Middleware\\Vrm\\CheckModule::class,
            'permission' => \\App\\Http\\Middleware\\Vrm\\CheckPermission::class,";

        // Add providers
        $providers = "
        App\\Providers\\Vrm\\NotificationServiceProvider::class,
        App\\Providers\\Vrm\\TokenServiceProvider::class,
        App\\Providers\\Vrm\\MediaForgeServiceProvider::class,
        App\\Providers\\Vrm\\UtilitiesServiceProvider::class,
        App\\Providers\\Vrm\\GlobalDataServiceProvider::class,";

        // Pattern to find middleware aliases section
        if (preg_match('/(->withMiddleware\(function\s*\([^)]*\)\s*\{[^}]*alias\(\s*\[[^]]*)/s', $content, $matches)) {
            if (strpos($matches[1], 'role') === false) {
                $content = str_replace($matches[1], $matches[1] . $middlewareAliases, $content);
            }
        }

        // Pattern to find providers section  
        if (preg_match('/(Application::configure[^}]*providers:\s*\[[^]]*)/s', $content, $matches)) {
            if (strpos($matches[1], 'NotificationServiceProvider') === false) {
                $content = str_replace($matches[1], $matches[1] . $providers, $content);
            }
        }

        File::put($bootstrapPath, $content);
    }

    /**
     * Check if User model needs updates
     */
    private function checkUserModel()
    {
        $userModelPath = app_path('Models/User.php');

        if (!File::exists($userModelPath)) {
            $this->warn('⚠️  User model not found. Skipping User model check.');
            return;
        }

        $content = File::get($userModelPath);

        // Check if Vormia methods exist
        if (strpos($content, 'User meta') === false) {
            $this->warn('⚠️  User model appears to be missing Vormia methods.');

            if ($this->confirm('Would you like to update the User model with Vormia methods?', true)) {
                $this->updateUserModel();
            }
        } else {
            $this->line('  ✅ User model is up to date');
        }
    }

    /**
     * Update User model with Vormia methods
     */
    private function updateUserModel()
    {
        $userModelPath = app_path('Models/User.php');

        // Create backup
        File::copy($userModelPath, $userModelPath . '.backup.' . date('Y-m-d-H-i-s'));

        $content = File::get($userModelPath);

        // Update fillable
        $fillablePattern = '/protected\s+\$fillable\s*=\s*\[(.*?)\];/s';
        $newFillable = "protected \$fillable = [
        'name',
        'email',
        'password',
        'username',
        'phone',
        'is_active',
    ];";

        $content = preg_replace($fillablePattern, $newFillable, $content);

        // Update casts
        $castsPattern = '/protected\s+function\s+casts\(\)\s*:\s*array\s*\{(.*?)\}/s';
        $newCasts = "protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }";

        if (preg_match($castsPattern, $content)) {
            $content = preg_replace($castsPattern, $newCasts, $content);
        } else {
            // Add casts method if it doesn't exist
            $content = str_replace(
                $newFillable,
                $newFillable . "\n\n    " . $newCasts,
                $content
            );
        }

        // Add Vormia methods
        $vormiaMethodsPath = __DIR__ . '/../../stubs/user-methods.stub';
        if (File::exists($vormiaMethodsPath)) {
            $vormiaMethods = File::get($vormiaMethodsPath);

            // Add before the closing class brace
            $content = preg_replace('/(\n\s*})(\s*)$/', "\n" . $vormiaMethods . "$1$2", $content);
        }

        File::put($userModelPath, $content);
        $this->line('  ✅ User model updated successfully (backup created)');
    }

    /**
     * Clear application caches
     */
    private function clearCaches()
    {
        $cacheCommands = [
            'config:clear' => 'Configuration cache',
            'route:clear' => 'Route cache',
            'view:clear' => 'View cache',
            'cache:clear' => 'Application cache',
        ];

        foreach ($cacheCommands as $command => $description) {
            try {
                Artisan::call($command);
                $this->line("  Cleared: {$description}");
            } catch (\Exception $e) {
                $this->line("  Skipped: {$description} (not available)");
            }
        }

        $this->info('✅ Caches cleared successfully.');
    }

    /**
     * Display completion message
     */
    private function displayCompletionMessage()
    {
        $this->newLine();
        $this->info('🎉 Vormia package updated successfully!');
        $this->newLine();

        $this->comment('📋 What was updated:');
        $this->line('   ✅ All package files replaced with fresh copies');
        $this->line('   ✅ Configuration files updated');
        $this->line('   ✅ Backups created in storage/app/vormia-backups/');
        $this->line('   ✅ Application caches cleared');
        $this->newLine();

        $this->comment('📖 Next steps:');
        $this->line('   1. Review any custom modifications in your backup files');
        $this->line('   2. Test your application to ensure everything works correctly');
        $this->line('   3. Run migrations if there are any new ones: php artisan migrate');
        $this->newLine();

        $this->info('✨ Your Vormia package is now up to date!');
    }
}
