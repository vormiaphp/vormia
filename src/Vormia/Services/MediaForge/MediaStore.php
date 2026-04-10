<?php

namespace Vormia\Vormia\Services\MediaForge;

interface MediaStore
{
    public function put(string $path, string $bytes): void;

    public function exists(string $path): bool;

    /**
     * Return a public URL when possible, otherwise a storage path.
     */
    public function urlOrPath(string $path): string;
}

