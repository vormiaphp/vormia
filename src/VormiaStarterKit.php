<?php

namespace VormiaCms\StarterKit;

use Illuminate\Contracts\Support\Starter;
use Illuminate\Filesystem\Filesystem;

class VormiaStarterKit implements Starter
{
    /**
     * Get the name of the starter kit.
     */
    public function name(): string
    {
        return 'VormiaStarterKit';
    }

    /**
     * Get the description of the starter kit.
     */
    public function description(): string
    {
        return 'Vormia CMS - A complete CMS starter kit for Laravel 12';
    }

    /**
     * Install the starter kit.
     */
    public function install(): void
    {
        $filesystem = new Filesystem();

        // Copy migrations
        $this->copyDirectory($filesystem, 'migrations', database_path('migrations'));

        // Copy models
        $this->copyDirectory($filesystem, 'models', app_path('Models'));

        // Copy controllers
        $this->copyDirectory($filesystem, 'controllers', app_path('Http/Controllers'));

        // Copy views
        $this->copyDirectory($filesystem, 'views', resource_path('views'));

        // Copy seeders
        $this->copyDirectory($filesystem, 'seeders', database_path('seeders'));

        // Copy routes if available
        $this->copyDirectory($filesystem, 'routes', base_path('routes'));

        // Copy configs if available
        $this->copyDirectory($filesystem, 'config', config_path());

        // Copy assets if available
        $this->copyDirectory($filesystem, 'assets', public_path('assets'));

        // Add any additional setup logic here
    }

    /**
     * Helper to copy directories.
     */
    protected function copyDirectory(Filesystem $filesystem, $source, $destination)
    {
        $source = __DIR__ . '/stubs/' . $source;

        if ($filesystem->isDirectory($source)) {
            $filesystem->ensureDirectoryExists($destination);
            $filesystem->copyDirectory($source, $destination);
        }
    }
}
