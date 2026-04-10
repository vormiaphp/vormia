<?php

namespace Vormia\Vormia\Services\MediaForge;

use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;

class LaravelDiskStore implements MediaStore
{
    private Filesystem|FilesystemAdapter $disk;

    public function __construct(
        private readonly FilesystemFactory $filesystems,
        private readonly string $diskName,
    ) {
        $this->disk = $this->filesystems->disk($this->diskName);
    }

    public function put(string $path, string $bytes): void
    {
        $this->disk->put($path, $bytes);
    }

    public function exists(string $path): bool
    {
        return $this->disk->exists($path);
    }

    public function urlOrPath(string $path): string
    {
        try {
            return $this->disk->url($path);
        } catch (\Throwable) {
            return $path;
        }
    }
}

