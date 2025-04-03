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
            'resources/views/emails/general',
            'resources/views/emails/partials',
            'resources/views/livewire/setting',
        ],
        'controllers' => [
            'app/Http/Controllers/Admin',
            'app/Http/Controllers/Front',
            'app/Http/Controllers/Setup',
            'app/Http/Controllers/Api',
            'app/Http/Controllers/Livewire/Setting',
        ],
        'models' => [
            'app/Models/Api',
            'app/Models/Vrm',
            'app/Models/Auto.php',
            'app/Models/Role.php',
            'app/Models/UserToken.php',
        ],
        'jobs' => [
            'app/Jobs/Vrm',
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
        'seeders' => [
            'database/seeders/RolesTableSeeder.php',
            'database/seeders/SettingSeeder.php',
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
            'app/Services/AfricasTalkingService.php',
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
        $this->warn('Remove the vormia routes in api.php and web.php files');
        $this->warn('Remove the vormia middleware import in web.php file');
        $this->warn('Remove the config/services keys related to africastalking and other used by vormia also in .env file');
        $this->comment('Update your DatabaseSeeder.php" remove anything related to `SettingSeeder`, `RolesTableSeeder` and `$admin->roles()->attach(1);`.');
        $this->comment('Make sure to run "composer update" to update your autoloader.');
        $this->warn('FAILURE TO DO SO WILL CAUSE AN ERROR IN THE NEXT COMMAND.');
        $this->line('Run "php artisan cache:clear" and "php artisan config:clear" to clear any cached data.');
        $this->comment('To completely remove the package, run: composer remove vormiaphp/vormia');
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
                    // Temporarily disable foreign key checks
                    DB::statement('SET FOREIGN_KEY_CHECKS=0');

                    Schema::dropIfExists($tableName);
                    $this->info("✓ Dropped table: {$tableName}");
                    $removed++;

                    // Re-enable foreign key checks
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                } catch (\Exception $e) {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                    $this->error("Failed to drop table {$tableName}: {$e->getMessage()}");
                }
            }

            if ($removed > 0) {
                $this->info("✓ Successfully removed {$removed} database tables");

                // Extract migration file names without the path and extension
                $migrationRecords = collect($this->paths['migrations'])
                    ->map(fn($file) => pathinfo($file, PATHINFO_FILENAME))
                    ->toArray();

                // Delete migration records from database
                $deleted = DB::table('migrations')
                    ->whereIn('migration', $migrationRecords)
                    ->delete();

                if ($deleted) {
                    $this->info('Migration records removed successfully.');
                } else {
                    $this->info('No matching migration records found.');
                }
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
                    // Temporarily disable foreign key checks
                    DB::statement('SET FOREIGN_KEY_CHECKS=0');

                    Schema::dropIfExists($tableName);
                    $this->info("✓ Dropped table: {$tableName}");
                    $removed++;

                    // Re-enable foreign key checks
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                } catch (\Exception $e) {
                    // Make sure to re-enable foreign key checks even if an error occurs
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');

                    $this->error("Failed to drop table {$tableName}: {$e->getMessage()}");
                }
            }
        }

        if ($removed > 0) {
            $this->info("✓ Successfully removed {$removed} tables with prefix '{$prefix}'");

            // Extract migration file names without the path and extension
            $migrationRecords = collect($this->paths['migrations'])
                ->map(fn($file) => pathinfo($file, PATHINFO_FILENAME))
                ->toArray();

            // Delete migration records from database
            $deleted = DB::table('migrations')
                ->whereIn('migration', $migrationRecords)
                ->delete();

            if ($deleted) {
                $this->info('Migration records removed successfully.');
            } else {
                $this->info('No matching migration records found.');
            }
        } else {
            $this->warn("No tables found with prefix '{$prefix}'");
        }
    }
}
