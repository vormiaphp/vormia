<?php

namespace VormiaPHP\Vormia\Facades;

use DateTimeInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Vormia\Vormia\Services\MediaForge\MediaForgeJob upload(mixed $input)
 * @method static \Vormia\Vormia\Services\MediaForge\MediaFileForgeJob uploadFile(mixed $input)
 * @method static string url(string $urlOrPath, ?string $disk = null)
 * @method static string previewUrl(string $urlOrPath, ?string $disk = null, ?DateTimeInterface $expiresAt = null, array $options = [])
 */
class MediaForge extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'vrm.mediaforge';
    }
}

