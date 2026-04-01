<?php

namespace Vormia\Vormia\Services\MediaForge;

use Intervention\Image\ImageManager;

class ImageEngine
{
    public function manager(?string $driver): ImageManager
    {
        $driver = strtolower(trim((string) $driver));

        return match ($driver) {
            'imagick' => ImageManager::imagick(),
            'gd' => ImageManager::gd(),
            'auto', '' => $this->autoManager(),
            default => $this->autoManager(),
        };
    }

    private function autoManager(): ImageManager
    {
        if (extension_loaded('imagick')) {
            try {
                return ImageManager::imagick();
            } catch (\Throwable) {
                // fall back to GD
            }
        }

        return ImageManager::gd();
    }
}

