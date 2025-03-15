<?php

namespace VormiaPHP\Vormia\Console;

use Illuminate\Console\Command;
use VormiaPHP\Vormia\VormiaVormia;

class UninstallCommand extends Command
{
    protected $signature = 'vormia:uninstall';

    protected $description = 'Uninstall Vormia Starter Kit';

    public function handle()
    {
        if (!$this->option('no-interaction') && !$this->confirm('Are you sure you want to uninstall Vormia Starter Kit? This action cannot be undone.', false)) {
            $this->info('Uninstallation cancelled.');
            return;
        }

        $this->info('Uninstalling Vormia Starter Kit...');

        $starter = new VormiaVormia();
        $starter->uninstall();

        // Remove Vormia routes from web.php and api.php
        $this->removeVormiaRoutes();

        // Ask if they want to remove database tables
        if (!$this->option('no-interaction') && $this->confirm('Would you like to remove the database tables? This will delete all your data!', false)) {
            // Roll back Vormia migrations
            $this->call('migrate:rollback', [
                '--path' => 'database/migrations/vormia'
            ]);
        }

        $this->info('Vormia Starter Kit has been uninstalled successfully.');
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

            // Look for the Vormia routes section using a more robust pattern
            // This pattern looks for the route group with prefix 'vrm'
            $pattern = '/\s*Route::group\(\[\'prefix\'\s*=>\s*\'vrm\'\].*?\}\);/s';
            $content = preg_replace($pattern, '', $content);

            // Remove the Livewire route if it exists
            $livewirePattern = '/\s*Route::get\(\'\/\',\s*App\\\\Livewire\\\\LiveSetting::class\).*?;/s';
            $content = preg_replace($livewirePattern, '', $content);

            // Clean up any extra blank lines
            $content = preg_replace('/\n{3,}/', "\n\n", $content);

            file_put_contents($webRoutesPath, $content);
            $this->info('Vormia routes removed from web.php');
        }

        // API routes
        $apiRoutesPath = base_path('routes/api.php');
        if (file_exists($apiRoutesPath)) {
            $content = file_get_contents($apiRoutesPath);

            // Look for the API v1 routes section
            $pattern = '/\s*Route::group\(\[\'prefix\'\s*=>\s*\'v1\'\].*?\}\);/s';
            $content = preg_replace($pattern, '', $content);

            // Clean up any extra blank lines
            $content = preg_replace('/\n{3,}/', "\n\n", $content);

            file_put_contents($apiRoutesPath, $content);
            $this->info('Vormia routes removed from api.php');
        }
    }
}
