<?php

namespace Vormia\Vormia\Services\MediaForge;

final readonly class MediaForgeResult
{
    public function __construct(
        public string $disk,
        public string $path,
        public ?string $url,
        public string $urlOrPath,
    ) {
    }
}

