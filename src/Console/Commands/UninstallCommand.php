<?php

namespace Vormia\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;

class UninstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vormia:uninstall {--force : Skip confirmation prompts} {--keep-data : Keep database tables and data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all Vormia package files and configurations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🗑️  Uninstalling Vormia Package...');
        $this->newLine();

        $force = $this->option('force');
        $keepData = $this->option('keep-data');

        // Warning message
        $this->error('⚠️  DANGER: This will completely remove Vormia from your application!');
        $this->warn('   This action will:');
        $this->warn('   • Remove all Vormia files and directories');
        $this->warn('   • Clean up bootstrap/app.php configurations');
        $this->warn('   • Remove environment variables');
        $this->warn('   • Remove npm packages (jquery, flatpickr, select2, sweetalert2)');
        $this->warn('   • Remove intervention/image package');
        $this->warn('   • Remove CSS and JS plugin files');
        $this->warn('   • Optionally drop database tables and data');
        $this->newLine();

        if (!$force && !$this->confirm('Are you absolutely sure you want to uninstall Vormia?', false)) {
            $this->info('❌ Uninstall cancelled.');
            return;
        }

        if (!$keepData && !$force) {
            $this->newLine();
            $this->warn('💾 DATABASE WARNING: This will also remove all Vormia database tables and data!');
            if (!$this->confirm('Do you want to keep the database tables and data?', true)) {
                $keepData = false;
            } else {
                $keepData = true;
            }
        }

        // Final confirmation
        if (!$force) {
            $this->newLine();
            $this->error('🚨 FINAL WARNING: This action cannot be undone!');
            if (!$this->confirm('Type "yes" to proceed with uninstallation', false)) {
                $this->info('❌ Uninstall cancelled.');
                return;
            }
        }

        // Step 1: Create final backup
        $this->step('Creating final backup...');
        $this->createFinalBackup();

        // Step 2: Remove files and directories
        $this->step('Removing Vormia files and directories...');
        $this->removeFiles();

        // Step 3: Clean up configurations
        $this->step('Cleaning up configurations...');
        $this->cleanupConfigurations();

        // Step 4: Remove environment variables
        $this->step('Removing environment variables...');
        $this->removeEnvironmentVariables();

        // Step 5: Remove database tables (if requested)
        if (!$keepData) {
            $this->step('Removing database tables...');
            $this->removeDatabaseTables();
        }

        // Step 6: Remove npm packages
        $this->step('Removing npm packages...');
        $this->removeNpmPackages();

        // Step 7: Remove intervention/image
        $this->step('Removing intervention/image package...');
        $this->removeInterventionImage();

        // Step 8: Clear caches
        $this->step('Clearing application caches...');
        $this->clearCaches();

        // Attempt to rollback Vormia (vrm_) migrations BEFORE deleting migration files
        $this->info('Attempting to rollback Vormia (vrm_) migrations...');
        $prefix = 'vrm_';
        $migrationPath = database_path('migrations');
        $rolledBack = false;
        if (File::isDirectory($migrationPath)) {
            foreach (File::files($migrationPath) as $file) {
                if (strpos($file->getFilename(), $prefix) !== false) {
                    // Try to rollback this migration
                    $migrationName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                    try {
                        Artisan::call('migrate:rollback', ['--path' => 'database/migrations/' . $file->getFilename(), '--force' => true]);
                        $this->line('  Rolled back migration: ' . $file->getFilename());
                        $rolledBack = true;
                    } catch (\Exception $e) {
                        $this->warn('  Could not rollback migration: ' . $file->getFilename() . ' (' . $e->getMessage() . ')');
                    }
                }
            }
        }
        if (!$rolledBack) {
            $this->warn('No Vormia (vrm_) migrations were rolled back automatically. You may need to manually drop Vormia tables from your database.');
        }

        // Remove Vormia migration files
        $migrationPath = database_path('migrations');
        if (File::isDirectory($migrationPath)) {
            foreach (File::files($migrationPath) as $file) {
                if (strpos($file->getFilename(), 'vrm_') !== false) {
                    File::delete($file->getPathname());
                    $this->line("  Removed migration: " . $file->getFilename());
                }
            }
        }

        $this->displayCompletionMessage($keepData);
    }

    /**
     * Display a step message
     */
    private function step($message)
    {
        $this->info("🗂️  {$message}");
    }

    /**
     * Create final backup before uninstallation
     */
    private function createFinalBackup()
    {
        $backupDir = storage_path('app/vormia-final-backup-' . date('Y-m-d-H-i-s'));

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
            base_path('.env') => $backupDir . '/.env',
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

        $this->info("✅ Final backup created in: {$backupDir}");
    }

    /**
     * Remove all Vormia files and directories
     */
    private function removeFiles()
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
            resource_path('css/plugins'),
            resource_path('js/plugins'),
            resource_path('js/helpers'),
        ];

        $filesToRemove = [
            config_path('vormia.php'),
        ];

        // Remove directories
        foreach ($directoriesToRemove as $directory) {
            if (File::exists($directory)) {
                File::deleteDirectory($directory);
                $this->line("  Removed directory: {$directory}");
            }
        }

        // Remove files
        foreach ($filesToRemove as $file) {
            if (File::exists($file)) {
                File::delete($file);
                $this->line("  Removed file: {$file}");
            }
        }

        // Automatically restore User.php from backup if available
        $userModelPath = app_path('Models/User.php');
        $backupFiles = glob($userModelPath . '.backup.*');
        if (!empty($backupFiles)) {
            $latestBackup = end($backupFiles);
            File::copy($latestBackup, $userModelPath);
            $this->info('✅ User model restored from backup: ' . basename($latestBackup));
        } else {
            $this->warn('⚠️  No User model backup found. You may need to manually clean up User model changes.');
            $this->line('   Vormia-specific methods and properties should be removed manually.');
        }

        $this->info('✅ Vormia files removed successfully.');
    }

    /**
     * Clean up configurations in bootstrap/app.php
     */
    private function cleanupConfigurations()
    {
        $bootstrapPath = base_path('bootstrap/app.php');

        if (!File::exists($bootstrapPath)) {
            $this->warn('⚠️  bootstrap/app.php not found. Skipping configuration cleanup.');
            return;
        }

        // Create backup
        File::copy($bootstrapPath, $bootstrapPath . '.backup.' . date('Y-m-d-H-i-s'));

        $content = File::get($bootstrapPath);

        // Remove middleware aliases
        $middlewareToRemove = [
            "'role' => \\App\\Http\\Middleware\\Vrm\\CheckRole::class,",
            "'module' => \\App\\Http\\Middleware\\Vrm\\CheckModule::class,",
            "'permission' => \\App\\Http\\Middleware\\Vrm\\CheckPermission::class,",
        ];

        // Remove providers
        $providersToRemove = [
            "App\\Providers\\Vrm\\NotificationServiceProvider::class,",
            "App\\Providers\\Vrm\\TokenServiceProvider::class,",
            "App\\Providers\\Vrm\\MediaForgeServiceProvider::class,",
            "App\\Providers\\Vrm\\UtilitiesServiceProvider::class,",
            "App\\Providers\\Vrm\\GlobalDataServiceProvider::class,",
        ];

        // Remove middleware
        foreach ($middlewareToRemove as $middleware) {
            $content = str_replace($middleware, '', $content);
            $content = str_replace(str_replace("'", '"', $middleware), '', $content);
        }

        // Remove providers
        foreach ($providersToRemove as $provider) {
            $content = str_replace($provider, '', $content);
        }

        // Clean up extra whitespace and empty lines
        $content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content);

        File::put($bootstrapPath, $content);

        $this->info('✅ bootstrap/app.php cleaned up successfully (backup created).');
    }

    /**
     * Remove environment variables
     */
    private function removeEnvironmentVariables()
    {
        $envPath = base_path('.env');
        $envExamplePath = base_path('.env.example');

        $filesToClean = [$envPath, $envExamplePath];

        foreach ($filesToClean as $filePath) {
            if (File::exists($filePath)) {
                $content = File::get($filePath);

                // Remove Vormia section
                $content = preg_replace('/\n*# VORMIA CONFIG\n.*?VORMIA_TABLE_PREFIX=.*?\n/s', '', $content);

                // Clean up extra whitespace
                $content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content);

                File::put($filePath, $content);
                $this->line("  Cleaned: " . basename($filePath));
            }
        }

        $this->info('✅ Environment variables removed successfully.');
    }

    /**
     * Remove database tables
     */
    private function removeDatabaseTables()
    {
        try {
            $prefix = config('vormia.table_prefix', 'vrm_');

            // Get all tables with Vormia prefix
            $tables = DB::select("SHOW TABLES LIKE '{$prefix}%'");

            if (empty($tables)) {
                $this->line('  No Vormia tables found.');
                return;
            }

            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0];
                DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
                $this->line("  Dropped table: {$tableName}");
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info('✅ Database tables removed successfully.');
        } catch (\Exception $e) {
            $this->error("❌ Error removing database tables: " . $e->getMessage());
            $this->warn('   You may need to manually remove the tables.');
        }
    }

    /**
     * Revert User model changes
     */
    private function revertUserModel()
    {
        $userModelPath = app_path('Models/User.php');

        if (!File::exists($userModelPath)) {
            return;
        }

        // Look for backup files
        $backupFiles = glob($userModelPath . '.backup.*');

        if (!empty($backupFiles)) {
            // Get the most recent backup
            $latestBackup = end($backupFiles);

            if ($this->confirm("Found User model backup. Do you want to restore it from: " . basename($latestBackup) . "?", true)) {
                File::copy($latestBackup, $userModelPath);
                $this->info('✅ User model restored from backup.');
                return;
            }
        }

        $this->warn('⚠️  No User model backup found. You may need to manually clean up User model changes.');
        $this->line('   Vormia-specific methods and properties should be removed manually.');
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
                \Illuminate\Support\Facades\Artisan::call($command);
                $this->line("  Cleared: {$description}");
            } catch (\Exception $e) {
                $this->line("  Skipped: {$description} (not available)");
            }
        }

        $this->info('✅ Caches cleared successfully.');
    }

    /**
     * Remove npm packages installed by Vormia
     */
    private function removeNpmPackages(): void
    {
        $packageJsonPath = base_path('package.json');

        if (!File::exists($packageJsonPath)) {
            $this->warn('⚠️  package.json not found. Skipping npm package removal.');
            return;
        }

        // Check if npm is available
        $npmCheck = Process::run('npm --version');
        if (!$npmCheck->successful()) {
            $this->warn('⚠️  npm is not available. Skipping npm package removal.');
            $this->line('   You can manually remove the packages: npm uninstall jquery flatpickr select2 sweetalert2');
            return;
        }

        // Packages to remove
        $packages = ['jquery', 'flatpickr', 'select2', 'sweetalert2'];
        $failed = [];

        foreach ($packages as $package) {
            $this->line("   Removing {$package}...");
            $result = Process::path(base_path())->run("npm uninstall {$package}");

            if ($result->successful()) {
                $this->info("   ✅ {$package} removed successfully.");
            } else {
                $this->warn("   ⚠️  Failed to remove {$package}.");
                $failed[] = $package;
            }
        }

        if (empty($failed)) {
            $this->info('✅ All npm packages removed successfully.');
        } else {
            $this->warn('⚠️  Some npm packages failed to remove: ' . implode(', ', $failed));
            $this->line('   You can manually remove them: npm uninstall ' . implode(' ', $failed));
        }
    }

    /**
     * Remove intervention/image package
     */
    private function removeInterventionImage(): void
    {
        // Check if installed
        if (!class_exists('Intervention\Image\ImageManager')) {
            $this->info('✅ intervention/image is not installed.');
            return;
        }

        $this->line('   Removing intervention/image...');
        $result = Process::path(base_path())->run('composer remove intervention/image');

        if ($result->successful()) {
            $this->info('✅ intervention/image removed successfully.');
        } else {
            $this->warn('⚠️  Failed to remove intervention/image automatically.');
            $this->line('   Please remove it manually: composer remove intervention/image');
            if ($result->errorOutput()) {
                $this->line('   Error: ' . $result->errorOutput());
            }
        }
    }

    /**
     * Display completion message
     */
    private function displayCompletionMessage($keepData)
    {
        $this->newLine();
        $this->info('🎉 Vormia package uninstalled successfully!');
        $this->newLine();

        $this->comment('📋 What was removed:');
        $this->line('   ✅ All Vormia files and directories');
        $this->line('   ✅ Configuration files');
        $this->line('   ✅ bootstrap/app.php middleware and providers');
        $this->line('   ✅ Environment variables');
        $this->line('   ✅ CSS and JS plugin files');
        $this->line('   ✅ npm packages (jquery, flatpickr, select2, sweetalert2)');
        $this->line('   ✅ intervention/image package');

        if ($keepData) {
            $this->line('   ⚠️  Database tables preserved (--keep-data)');
        } else {
            $this->line('   ✅ Database tables removed');
        }

        $this->line('   ✅ Application caches cleared');
        $this->line('   ✅ Final backup created in storage/app/');
        $this->newLine();

        $this->comment('📖 Final steps:');
        $this->line('   1. Remove "vormiaphp/vormia" from your composer.json');
        $this->line('   2. Run: composer remove vormiaphp/vormia');
        $this->line('   3. Review your User model for any remaining Vormia code');
        $this->line('   4. Clean up app.css and app.js - remove Vormia imports and initialization code');

        if ($keepData) {
            $this->line('   5. Manually remove database tables if needed');
        }

        $this->newLine();
        $this->comment('⚠️  Manual cleanup required:');
        $this->warn('   • Laravel Sanctum: If you want to remove Sanctum, run: composer remove laravel/sanctum');
        $this->warn('   • CORS Config: If you want to remove CORS config, delete: config/cors.php');
        $this->newLine();

        $this->info('✨ Thank you for using Vormia!');
    }
}
