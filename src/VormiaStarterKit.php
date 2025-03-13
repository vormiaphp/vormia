<?php

namespace VormiaCms\StarterKit;

use Illuminate\Filesystem\Filesystem;

class VormiaStarterKit
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
        $this->copyDirectory($filesystem, 'migrations', $this->databasePath('migrations'));

        // Copy models
        $this->copyDirectory($filesystem, 'models', $this->appPath('Models'));

        // Copy controllers
        $this->copyDirectory($filesystem, 'controllers', $this->appPath('Http/Controllers'));

        // Copy livewire components
        $this->copyDirectory($filesystem, 'livewire', $this->appPath('Livewire'));

        // Copy middleware
        $this->copyDirectory($filesystem, 'middleware', $this->appPath('Http/Middleware'));

        // Copy rules
        $this->copyDirectory($filesystem, 'rules', $this->appPath('Rules'));

        // Copy services
        $this->copyDirectory($filesystem, 'services', $this->appPath('Services'));

        // Copy views
        $this->copyDirectory($filesystem, 'views', $this->resourcePath('views'));

        // Copy seeders
        $this->copyDirectory($filesystem, 'seeders', $this->databasePath('seeders'));

        // Copy routes
        $this->copyDirectory($filesystem, 'routes', $this->basePath('routes'));

        // Copy public assets
        $this->copyDirectory($filesystem, 'public', $this->publicPath());
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

    /**
     * Get the application path.
     */
    protected function appPath($path = ''): string
    {
        return app_path($path);
    }

    /**
     * Get the database path.
     */
    protected function databasePath($path = ''): string
    {
        return database_path($path);
    }

    /**
     * Get the resource path.
     */
    protected function resourcePath($path = ''): string
    {
        return resource_path($path);
    }

    /**
     * Get the base path.
     */
    protected function basePath($path = ''): string
    {
        return base_path($path);
    }

    /**
     * Get the config path.
     */
    protected function configPath($path = ''): string
    {
        return config_path($path);
    }

    /**
     * Get the public path.
     */
    protected function publicPath($path = ''): string
    {
        return public_path($path);
    }
}
