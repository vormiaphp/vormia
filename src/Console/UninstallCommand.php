<?php

namespace VormiaPHP\Vormia\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UninstallCommand extends Command
{
    protected $signature = 'vormia:uninstall {--force : Force uninstallation without confirmation} {--keep-files : Keep files but remove routes} {--keep-tables : Keep database tables}';

    protected $description = 'Uninstall Vormia Starter Kit';

    protected $tables = ['user_meta', 'activation_tokens', 'roles', 'role_user', 'terms', 'settings', 'hierarchy', 'hierarchy_meta', 'user_tokens', 'personal_access_tokens'];

    /**
     * Directories and files to be removed during uninstallation
     */
    protected $paths = [
        'views' => [
            'resources/views/admin',
            'resources/views/content',
            'resources/views/livewire/setting',
        ],
        'controllers' => [
            'app/Http/Controllers/Vrm',
            'app/Http/Controllers/Front',
            'app/Http/Controllers/Api',
        ],
        'models' => [
            'app/Models/Api',
            'app/Models/Vrm',
            'app/Models/Auto.php',
            'app/Models/Role.php',
            'app/Models/UserToken.php',
        ],
        'assets' => [
            'public/admin',
            'public/content',
        ],
        'migrations' => [
            'database/migrations/0002_02_02_000000_update_users_table_add_phone_fields.php',
            'database/migrations/0002_02_02_000001_update_users_table_email_nullable_add_username.php',
            'database/migrations/0002_02_02_000002_create_user_meta_table.php',
            'database/migrations/0002_02_02_000003_create_activation_tokens_table.php',
            'database/migrations/0002_02_02_000004_create_roles_table.php',
            'database/migrations/0002_02_02_000005_create_role_user_table.php',
            'database/migrations/0002_02_02_000006_create_terms_table.php',
            'database/migrations/0002_02_02_000007_create_settings_table.php',
            'database/migrations/0002_02_02_000008_create_hierarchy_table.php',
            'database/migrations/0002_02_02_000009_create_hierarchy_meta_table.php',
            'database/migrations/0002_02_02_000010_create_user_tokens_table.php',
        ],
        'middleware' => [
            'app/Http/Middleware/CheckRolePermission.php',
        ],
        'livewire' => [
            'app/Livewire/LiveSetting.php',
            'resources/views/livewire/Setting',
        ],
        'storage' => [],
        'rules' => [
            'app/Rules/CommaSeparatedNumbers.php',
            'app/Rules/EmailOrId.php',
            'app/Rules/EmailOrPhone.php',
            'app/Rules/MinWords.php',
            'app/Rules/NumericFormat.php',
        ],
        'services' => [
            'app/Services/TokenService.php',
            'app/Services/Verification.php',
        ],
    ];

    /**
     * Vormia database tables
     */
    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('Are you sure you want to uninstall Vormia Starter Kit? This action cannot be undone.', false)) {
            $this->info('Uninstallation cancelled.');
            return;
        }

        $this->info('Uninstalling Vormia Starter Kit...');

        // Remove routes from web.php and api.php
        $this->removeVormiaRoutes();

        // Remove files and directories unless --keep-files option is set
        if (!$this->option('keep-files')) {
            $this->removeVormiaFiles();
        }

        // Remove database tables unless --keep-tables option is set
        if (!$this->option('keep-tables')) {
            if ($this->option('force') || $this->confirm('Would you like to remove the database tables? This will delete all your data!', false)) {
                $this->removeDatabaseTables();
            }
        }

        $this->info('Vormia Starter Kit has been uninstalled successfully.');
        $this->line('Make sure to run "composer update" to update your autoloader.');
        $this->line('Run "php artisan cache:clear" and "php artisan config:clear" to clear any cached data.');
    }

    /**
     * Remove Vormia routes from web.php and api.php
     */
    protected function removeVormiaRoutes()
    {
        // Web routes
        $webRoutesPath = base_path('routes/web.php');
        if (file_exists($webRoutesPath)) {
            $content = file_get_contents($webRoutesPath);

            // Look specifically for the vrm route group
            $pattern = '/\/\/\s*TODO:\s*VORMIA\s*ROUTES\s*\n\s*Route::group\(\[\'prefix\'\s*=>\s*\'vrm\'\].*?\}\);/s';
            $content = preg_replace($pattern, '', $content);

            // Remove the Livewire route specifically (with comment)
            $livewirePattern = '/\/\/\s*TODO:\s*VORMIA\s*LIVEWIRE\s*\n\s*Route::get\(\'\/\',\s*App\\\\Livewire\\\\LiveSetting::class\).*?;/s';
            $content = preg_replace($livewirePattern, '', $content);

            // Clean up any extra blank lines
            $content = preg_replace('/\n{3,}/', "\n\n", $content);

            file_put_contents($webRoutesPath, $content);
            $this->info('✓ Vormia routes removed from web.php');
        }

        // API routes
        $apiRoutesPath = base_path('routes/api.php');
        if (file_exists($apiRoutesPath)) {
            $content = file_get_contents($apiRoutesPath);

            // Look for the Todo: API VERSION 1 comment and route group
            $pattern = '/\/\/\s*Todo:\s*API\s*VERSION\s*1\s*\n\s*Route::group\(\[\'prefix\'\s*=>\s*\'v1\'\].*?\}\);/s';
            $content = preg_replace($pattern, '', $content);

            // Alternative pattern for API
            $altPattern = '/\/\/\s*Todo:\s*VORMIA\s*API\s*\n\s*Route::group\(\[\'prefix\'\s*=>\s*\'vrm\'\].*?\}\);/s';
            $content = preg_replace($altPattern, '', $content);

            // Clean up any extra blank lines
            $content = preg_replace('/\n{3,}/', "\n\n", $content);

            file_put_contents($apiRoutesPath, $content);
            $this->info('✓ Vormia routes removed from api.php');
        }
    }

    /**
     * Remove Vormia files and directories
     */
    protected function removeVormiaFiles()
    {
        foreach ($this->paths as $type => $paths) {
            foreach ($paths as $path) {
                $fullPath = base_path($path);

                if (File::exists($fullPath)) {
                    try {
                        if (File::isDirectory($fullPath)) {
                            File::deleteDirectory($fullPath);
                            $this->info("✓ Removed directory: {$path}");
                        } else {
                            File::delete($fullPath);
                            $this->info("✓ Removed file: {$path}");
                        }
                    } catch (\Exception $e) {
                        $this->error("Failed to remove {$path}: {$e->getMessage()}");
                    }
                } else {
                    $this->line("Path not found (skipping): {$path}");
                }
            }
        }
    }

    /**
     * Remove Vormia database tables by checking for prefix
     */
    protected function removeDatabaseTables()
    {
        $this->info('Removing database tables...');

        try {
            // Get all tables from the database
            $tables = $this->tables;
            $removed = 0;

            foreach ($tables as $tableName) {
                try {
                    Schema::dropIfExists($tableName);
                    $this->info("✓ Dropped table: {$tableName}");
                    $removed++;
                } catch (\Exception $e) {
                    $this->error("Failed to drop table {$tableName}: {$e->getMessage()}");
                }
            }

            if ($removed > 0) {
                $this->info("✓ Successfully removed {$removed} database tables");
            } else {
                $this->warn("No Vormia tables found");

                // Ask user if they want to provide additional table prefixes to check
                if (!$this->option('force') && $this->confirm('Would you like to specify additional table prefixes to check?', false)) {
                    $additionalPrefix = $this->ask('Enter additional table prefix (without underscore):');
                    if (!empty($additionalPrefix)) {
                        $this->dropTablesByPrefix($additionalPrefix . '_', $tables);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error("Failed to access database tables: {$e->getMessage()}");
            $this->line("You may need to manually remove Vormia tables from your database.");
        }
    }

    /**
     * Get all tables from the current database
     * 
     * @return array
     */
    protected function getAllDatabaseTables()
    {
        $tables = [];

        try {
            // This works for MySQL
            $db = DB::connection()->getDatabaseName();
            $results = DB::select("SHOW TABLES");

            foreach ($results as $result) {
                // Extract table name from the object (different drivers return different formats)
                $tableName = is_object($result) ? array_values((array)$result)[0] : $result;
                $tables[] = $tableName;
            }
        } catch (\Exception $e) {
            // Try another approach for other database systems
            try {
                // SQLite approach
                $results = DB::select("SELECT name FROM sqlite_master WHERE type='table'");
                foreach ($results as $result) {
                    $tables[] = $result->name;
                }
            } catch (\Exception $e2) {
                $this->warn("Could not retrieve database tables: {$e2->getMessage()}");
            }
        }

        return $tables;
    }

    /**
     * Drop tables by a specific prefix
     * 
     * @param string $prefix
     * @param array $tables
     */
    protected function dropTablesByPrefix($prefix, $tables)
    {
        $removed = 0;

        foreach ($tables as $tableName) {
            if (strpos($tableName, $prefix) === 0) {
                try {
                    Schema::dropIfExists($tableName);
                    $this->info("✓ Dropped table: {$tableName}");
                    $removed++;
                } catch (\Exception $e) {
                    $this->error("Failed to drop table {$tableName}: {$e->getMessage()}");
                }
            }
        }

        if ($removed > 0) {
            $this->info("✓ Successfully removed {$removed} tables with prefix '{$prefix}'");
        } else {
            $this->warn("No tables found with prefix '{$prefix}'");
        }
    }
}
