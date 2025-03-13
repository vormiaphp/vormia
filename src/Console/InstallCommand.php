<?php

namespace VormiaCms\StarterKit\Console;

use Illuminate\Console\Command;
use VormiaCms\StarterKit\VormiaStarterKit;

class InstallCommand extends Command
{
    protected $signature = 'vormia:install';

    protected $description = 'Install Vormia CMS Starter Kit';

    public function handle()
    {
        $this->info('Installing Vormia CMS Starter Kit...');

        $starter = new VormiaStarterKit();
        $starter->install();

        $this->info('Vormia CMS Starter Kit has been installed successfully!');
    }
}
