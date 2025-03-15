<?php

namespace VormiaPHP\Vormia\Console;

use Illuminate\Console\Command;
use VormiaPHP\Vormia\VormiaVormia;

class RefreshCommand extends Command
{
    protected $signature = 'vormia:refresh';

    protected $description = 'Reset Vormia Starter Kit to default configuration';

    public function handle()
    {
        if (!$this->option('no-interaction') && !$this->confirm('This will reset Vormia Starter Kit to default. All customizations will be lost. Are you sure?', false)) {
            $this->info('Operation cancelled.');
            return;
        }

        $this->info('Refreshing Vormia Starter Kit...');

        // First uninstall current files
        $starter = new VormiaVormia();
        $starter->uninstall();

        // Then reinstall fresh files
        $starter->install();

        // Check if we should reset the database
        if (!$this->option('no-interaction') && $this->confirm('Would you like to refresh the database tables as well? This will delete all your data!', false)) {
            $this->call('migrate:fresh', [
                '--path' => 'database/migrations/vormia'
            ]);
            $this->call('db:seed', [
                '--class' => 'VormiaSeeder'
            ]);
        }

        $this->info('Vormia Starter Kit has been refreshed to default settings!');
    }
}
