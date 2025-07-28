<?php

namespace App\Facades\Vrm;

use Illuminate\Support\Facades\Facade;

/**
 * MediaForge Facade
 *
 * Provides image processing functionality including resizing, compression,
 * format conversion, watermarking, and avatar generation.
 *
 * Supports both GD and Imagick drivers with automatic detection and switching.
 *
 * Driver Compatibility:
 * - Imagick: Full feature support including text watermarks, WebP, progressive JPEG, and rounded avatars
 * - GD: Basic operations supported, limited text watermark and WebP support, no perfect circle clipping
 *
 * @requires intervention/image package
 * @see \App\Services\Vrm\MediaForgeService
 *
 * @method static \App\Services\Vrm\MediaForgeService setDriver(string $driver) Set driver preference ('auto', 'gd', 'imagick')
 * @method static string getCurrentDriver() Get current driver being used
 * @method static array getAvailableDrivers() Get list of available drivers
 * @method static bool isOperationSupported(string $operation, array $options = []) Check if operation is supported by current driver
 * @method static array getOperationWarnings(string $operation, array $options = []) Get compatibility warnings for operation
 * @method static array delete(string $filePath, string|array $type = 'all') Delete files based on type(s) ('only', 'all', 'thumb', 'resized', 'compressed', 'converted', 'watermark', 'avatar', 'processed')
 * @method static array getDeletePreview(string $filePath, string|array $type = 'all') Preview files that would be deleted
 */
class MediaForge extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mediaforge';
    }
}
