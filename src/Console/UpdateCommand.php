<?php

namespace VormiaPHP\Vormia\Console;

use Illuminate\Console\Command;
use VormiaPHP\Vormia\VormiaVormia;

class UpdateCommand extends Command
{
    protected $signature = 'vormia:update';

    protected $description = 'Update Vormia Starter Kit to the latest version';

    public function handle()
    {
        $this->info('Updating Vormia Starter Kit...');

        $starter = new VormiaVormia();
        $starter->update();

        // You might want to run migrations for new database changes
        if (!$this->option('no-interaction') && $this->confirm('Would you like to run migrations to update the database?', true)) {
            $this->call('migrate');
        }

        $this->info('Vormia Starter Kit has been updated successfully!');
    }
}
