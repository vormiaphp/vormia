<?php

namespace VormiaPHP\Vormia;

use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;
use ZipArchive;
use RuntimeException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class VormiaVormia
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * Create a new VormiaVormia instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * Get the name of the vormia kit.
     */
    public function name(): string
    {
        return 'VormiaVormia';
    }

    /**
     * Get the description of the vormia kit.
     */
    public function description(): string
    {
        return 'Vormia - A complete kit for Laravel';
    }

    /**
     * Install the vormia kit.
     */
    public function install(bool $apiOnly = false): bool
    {
        try {
            $this->copyStubs($apiOnly);
            $this->runMigrations();
            return true;
        } catch (\Exception $e) {
            $this->handleError($e);
            return false;
        }
    }

    /**
     * Update the vormia kit.
     */
    public function update(): bool
    {
        try {
            $this->updateStubs();
            return true;
        } catch (\Exception $e) {
            $this->handleError($e);
            return false;
        }
    }

    /**
     * Uninstall the vormia kit.
     */
    public function uninstall(): bool
    {
        try {
            $this->removeInstalledFiles();
            return true;
        } catch (\Exception $e) {
            $this->handleError($e);
            return false;
        }
    }

    /**
     * Copy all stubs to their respective directories.
     */
    protected function copyStubs(bool $apiOnly = false): void
    {
        $stubs = [
            // Migrations handled separately below
            'models' => $this->appPath('Models'),
            'jobs' => $this->appPath('Jobs'),
            'helpers' => $this->appPath('Helpers'),
            'facades' => $this->appPath('Facades'),
            'config' => $this->configPath(),
            'providers' => $this->appPath('Providers'),
            'traits' => $this->appPath('Traits'),
            'services' => $this->appPath('Services'),
            'middleware' => $this->appPath('Http/Middleware'),
        ];

        // Copy all non-migration stubs
        foreach ($stubs as $source => $destination) {
            $this->copyDirectory($source, $destination);
        }

        // Copy migration files directly into database/migrations
        $migrationSource = __DIR__ . '/stubs/migrations/Vrm';
        $migrationDest = $this->databasePath('migrations');
        if ($this->filesystem->isDirectory($migrationSource)) {
            foreach ($this->filesystem->files($migrationSource) as $file) {
                $this->filesystem->copy($file->getPathname(), $migrationDest . '/' . $file->getFilename());
            }
        }
    }

    /**
     * Run database migrations.
     */
    protected function runMigrations(): void
    {
        Artisan::call('migrate', ['--force' => true]);
    }

    /**
     * Copy only API controllers and related files.
     */
    protected function copyApiControllers(): void
    {
        // No API controllers or views to copy, so this method is now empty.
    }

    /**
     * Update Stubs in directories.
     */
    protected function updateStubs(): void
    {
        $stubs = [
            // Migrations handled separately below
            'models' => $this->appPath('Models'),
            'jobs' => $this->appPath('Jobs'),
            'helpers' => $this->appPath('Helpers'),
            'facades' => $this->appPath('Facades'),
            'config' => $this->configPath(),
            'providers' => $this->appPath('Providers'),
            'traits' => $this->appPath('Traits'),
            'services' => $this->appPath('Services'),
            'middleware' => $this->appPath('Http/Middleware'),
        ];

        // Update all non-migration stubs
        foreach ($stubs as $source => $destination) {
            $sourcePath = __DIR__ . "/stubs/{$source}";
            if ($this->filesystem->isDirectory($sourcePath)) {
                $this->updateDirectory($sourcePath, $destination);
            }
        }

        // Update migration files directly into database/migrations
        $migrationSource = __DIR__ . '/stubs/migrations/Vrm';
        $migrationDest = $this->databasePath('migrations');
        if ($this->filesystem->isDirectory($migrationSource)) {
            foreach ($this->filesystem->files($migrationSource) as $file) {
                $this->filesystem->copy($file->getPathname(), $migrationDest . '/' . $file->getFilename());
            }
        }
    }

    /**
     * Handle errors during installation/update.
     *
     * @param \Exception $e
     * @throws \Exception
     */
    protected function handleError(\Exception $e): void
    {
        Log::error('Vormia installation error: ' . $e->getMessage());
        throw $e;
    }

    /**
     * Copy a directory from source to destination.
     */
    protected function copyDirectory(string $source, string $destination): void
    {
        $source = __DIR__ . '/stubs/' . $source;

        if (!$this->filesystem->exists($source)) {
            throw new RuntimeException("Source directory does not exist: {$source}");
        }

        $this->filesystem->ensureDirectoryExists(dirname($destination));
        $this->filesystem->copyDirectory($source, $destination);
    }

    /**
     * Get the application path.
     */
    protected function appPath(string $path = ''): string
    {
        return app_path($path);
    }

    /**
     * Get the database path.
     */
    protected function databasePath(string $path = ''): string
    {
        return database_path($path);
    }

    /**
     * Get the resources path.
     */
    protected function resourcePath(string $path = ''): string
    {
        return resource_path($path);
    }

    /**
     * Get the public path.
     */
    protected function publicPath(string $path = ''): string
    {
        return public_path($path);
    }

    /**
     * Get the base path.
     */
    protected function basePath(string $path = ''): string
    {
        return base_path($path);
    }

    /**
     * Get the config path.
     */
    protected function configPath(string $path = ''): string
    {
        return config_path($path);
    }

    /**
     * Remove installed files during uninstallation.
     */
    protected function removeInstalledFiles(): void
    {
        $directoriesToRemove = [
            // Helpers
            $this->appPath('Helpers/Vrm'),
            // Facades
            $this->appPath('Facades/Vrm'),
            // Jobs
            $this->appPath('Jobs/Vrm'),
            // Middleware
            $this->appPath('Http/Middleware/Vrm'),
            // Models
            $this->appPath('Models/Vrm'),
            // Providers
            $this->appPath('Providers/Vrm'),
            // Services
            $this->appPath('Services/Vrm'),
            // Traits
            $this->appPath('Traits/Vrm'),
            // Config
            $this->configPath('vormia.php'),
            // Public assets (if any were ever published)
            $this->publicPath('vendor/vormia'),
        ];

        foreach ($directoriesToRemove as $directory) {
            if ($this->filesystem->exists($directory)) {
                $this->filesystem->deleteDirectory($directory);
            }
        }
    }

    /**
     * Update a directory with new files.
     */
    protected function updateDirectory(string $source, string $destination): void
    {
        if (!$this->filesystem->isDirectory($source)) {
            return;
        }

        // Create destination directory if it doesn't exist
        $this->filesystem->ensureDirectoryExists($destination);

        // Copy files
        $files = $this->filesystem->files($source);
        foreach ($files as $file) {
            $destPath = $destination . '/' . $file->getFilename();

            // Only copy if file doesn't exist or is newer
            if (
                !$this->filesystem->exists($destPath) ||
                $this->filesystem->lastModified($file->getPathname()) > $this->filesystem->lastModified($destPath)
            ) {
                $this->filesystem->copy($file->getPathname(), $destPath);
            }
        }

        // Process subdirectories
        $directories = $this->filesystem->directories($source);
        foreach ($directories as $directory) {
            $dirName = basename($directory);
            $this->updateDirectory(
                $directory,
                $destination . '/' . $dirName
            );
        }
    }

    /**
     * Remove a directory and its contents.
     */
    protected function removeDirectory(string $path): void
    {
        if ($this->filesystem->exists($path)) {
            $this->filesystem->deleteDirectory($path);
        }
    }

    /**
     * Extract the zipped files
     */
    protected function extractCompressedDirectory($archiveName, $destinationPath)
    {
        $possiblePaths = [
            __DIR__ . '/stubs/' . $archiveName,
            __DIR__ . '/stubs/public/' . $archiveName,
            __DIR__ . '/../stubs/' . $archiveName,
            __DIR__ . '/../stubs/public/' . $archiveName
        ];

        $archivePath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $archivePath = $path;
                break;
            }
        }

        if (!$archivePath) {
            error_log("ZIP file not found: tried paths: " . implode(', ', $possiblePaths));
            return false;
        }

        // Create destination directory if it doesn't exist
        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        // Extract using ZipArchive
        $zip = new \ZipArchive;
        $result = $zip->open($archivePath);

        if ($result === TRUE) {
            // Get the base folder name from the destination path
            $baseFolder = basename($destinationPath);

            // First, extract to a temporary directory
            $tempDir = sys_get_temp_dir() . '/vormia_extract_' . time();
            mkdir($tempDir, 0755, true);

            $zip->extractTo($tempDir);
            $zip->close();

            // Now copy the contents from the extracted directory to the destination
            $extractedDir = $tempDir . '/' . $baseFolder;

            // If the extracted directory exists (with the duplicated structure)
            if (is_dir($extractedDir)) {
                $filesystem = new Filesystem();
                $filesystem->copyDirectory($extractedDir, $destinationPath);
            } else {
                // If there's no duplicated structure, just copy everything
                $filesystem = new Filesystem();
                $filesystem->copyDirectory($tempDir, $destinationPath);
            }

            // Clean up the temporary directory
            $filesystem->deleteDirectory($tempDir);

            return true;
        } else {
            error_log("Failed to open zip file $archiveName: Error code $result");
            return false;
        }
    }
}
