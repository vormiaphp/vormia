<?php

namespace VormiaPHP\Vormia\Console;

use Illuminate\Console\Command;

class HelpCommand extends Command
{
    protected $signature = 'vormia:help {topic? : Specific topic to get help on (install, update, refresh, uninstall)}';

    protected $description = 'Display help information for Vormia Starter Kit';

    public function handle()
    {
        $topic = $this->argument('topic');

        if ($topic) {
            $method = 'help' . ucfirst(strtolower($topic));
            if (method_exists($this, $method)) {
                $this->$method();
                return;
            } else {
                $this->error("Topic '{$topic}' not found.");
                $this->line("Available topics: install, update, refresh, uninstall");
                return;
            }
        }

        $this->info('Vormia Starter Kit - Help Guide');
        $this->line('===============================');
        $this->newLine();

        $this->comment('Available Commands:');
        $this->line('  php artisan vormia:install   - Install Vormia Starter Kit');
        $this->line('  php artisan vormia:update    - Update Vormia components');
        $this->line('  php artisan vormia:refresh   - Refresh Vormia configuration');
        $this->line('  php artisan vormia:uninstall - Remove Vormia from your project');

        $this->newLine();
        $this->comment('For more information on a specific command:');
        $this->line('  php artisan vormia:help install');
        $this->line('  php artisan vormia:help update');
        $this->line('  php artisan vormia:help refresh');
        $this->line('  php artisan vormia:help uninstall');
    }

    protected function helpInstall()
    {
        $this->info('Vormia Starter Kit - Installation Help');
        $this->line('===================================');
        $this->newLine();

        $this->comment('Basic Installation:');
        $this->line('  php artisan vormia:install');
        $this->line('  This will install all Vormia files, routes, and configurations.');

        $this->newLine();
        $this->comment('What Gets Installed:');
        $this->line('  1. Controllers in app/Http/Controllers/Vrm');
        $this->line('  2. Views in resources/views/admin and resources/views/content');
        $this->line('  3. Routes in web.php and api.php (prefixed with "vrm")');
        $this->line('  4. Models in app/Models/Vrm');
        $this->line('  5. Migrations in database/migrations');
        $this->line('  6. Assets in public/admin and public/content');

        $this->newLine();
        $this->comment('After Installation:');
        $this->line('  1. Run migrations: php artisan migrate');
        $this->line('  2. Run "php artisan serve" to start your application');
        $this->line('  3. Access the admin panel at: your-app-url/vrm/admin');
    }

    protected function helpUpdate()
    {
        $this->info('Vormia Starter Kit - Update Help');
        $this->line('==============================');
        $this->newLine();

        $this->comment('Update Vormia Components:');
        $this->line('  php artisan vormia:update');
        $this->line('  This will update all Vormia components to the latest version.');

        $this->newLine();
        $this->comment('What Gets Updated:');
        $this->line('  1. Core controllers and models');
        $this->line('  2. View templates');
        $this->line('  3. Admin assets');
        $this->line('  4. Routes configuration');

        $this->newLine();
        $this->comment('After Update:');
        $this->line('  1. Run "php artisan migrate" to apply any database changes');
        $this->line('  2. Clear caches: php artisan cache:clear && php artisan config:clear');
    }

    protected function helpRefresh()
    {
        $this->info('Vormia Starter Kit - Refresh Help');
        $this->line('===============================');
        $this->newLine();

        $this->comment('Refresh Vormia Configuration:');
        $this->line('  php artisan vormia:refresh');
        $this->line('  This will refresh Vormia configuration and repair any issues.');

        $this->newLine();
        $this->comment('What Gets Refreshed:');
        $this->line('  1. Routes configuration');
        $this->line('  2. Permission settings');
        $this->line('  3. Asset links');
        $this->line('  4. Database connections');

        $this->newLine();
        $this->comment('When to Use Refresh:');
        $this->line('  - If you encounter permission issues');
        $this->line('  - If routes are not working properly');
        $this->line('  - After manual changes to configuration files');
    }

    protected function helpUninstall()
    {
        $this->info('Vormia Starter Kit - Uninstallation Help');
        $this->line('=====================================');
        $this->newLine();

        $this->comment('Basic Uninstallation:');
        $this->line('  php artisan vormia:uninstall');
        $this->line('  This will remove all Vormia files, routes, and prompt for database removal.');

        $this->newLine();
        $this->comment('Uninstallation Options:');
        $this->line('  --force       : Skip confirmation prompts');
        $this->line('  --keep-files  : Keep files but remove routes');
        $this->line('  --keep-tables : Don\'t remove database tables');

        $this->newLine();
        $this->comment('Examples:');
        $this->line('  1. Remove everything without prompts:');
        $this->line('     php artisan vormia:uninstall --force');
        $this->line('  2. Remove only routes, keep files and database:');
        $this->line('     php artisan vormia:uninstall --keep-files --keep-tables');

        $this->newLine();
        $this->comment('After Uninstallation:');
        $this->line('  1. Run "composer update" to update your autoloader');
        $this->line('  2. Clear caches: php artisan cache:clear && php artisan config:clear');
    }
}
