<?php

namespace App\Facades\Vrm;

use Illuminate\Support\Facades\Facade;

/**
 * MediaForge Facade
 * 
 * Provides image processing functionality including resizing, compression, 
 * format conversion, watermarking, and avatar generation.
 * 
 * @requires intervention/image package
 * @see \App\Services\Vrm\MediaForgeService
 */
class MediaForge extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mediaforge';
    }
}
