<?php

namespace Vormia\Vormia\Services\MediaForge;

use Illuminate\Filesystem\Filesystem;

class VormiaWebrootStore implements MediaStore
{
    public function __construct(
        private readonly Filesystem $fs,
        private readonly string $publicRoot,
        private readonly ?string $appUrl,
    ) {
    }

    public function put(string $path, string $bytes): void
    {
        $relative = ltrim($path, '/');
        $absolute = rtrim($this->publicRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);

        $this->fs->ensureDirectoryExists(dirname($absolute));
        $this->fs->put($absolute, $bytes);
    }

    public function exists(string $path): bool
    {
        $relative = ltrim($path, '/');
        $absolute = rtrim($this->publicRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);

        return $this->fs->exists($absolute);
    }

    public function urlOrPath(string $path): string
    {
        $relative = ltrim($path, '/');

        if (! $this->appUrl || trim($this->appUrl) === '') {
            return $relative;
        }

        return rtrim($this->appUrl, '/') . '/' . $relative;
    }
}

