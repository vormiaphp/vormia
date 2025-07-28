<?php

namespace App\Services\Vrm;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Intervention\Image\ImageManager as Image;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Typography\FontFactory;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Geometry\Factories\CircleFactory;

class MediaForgeService
{
    protected $files = [];
    protected $urls = [];
    protected $uploadPath = null;
    protected $yearFolder = true;
    protected $randomizeFileName = true;
    protected $privateUpload = false;
    protected $imageManager;
    protected string $disk = 'public';
    protected string $visibility = 'public';
    protected string $folder = '';
    protected array $operations = [];
    protected string $driver = 'auto'; // 'auto', 'gd', 'imagick'
    protected $originalFiles = [];

    public function __construct(string $driver = null)
    {
        // Check if intervention/image is installed
        if (!class_exists('Intervention\Image\ImageManager')) {
            throw new \RuntimeException(
                'The intervention/image package is required for MediaForgeService. ' .
                    'Please install it by running: composer require intervention/image'
            );
        }

        // Use provided driver or fall back to config
        $this->driver = $driver ?? config('vormia.mediaforge.driver', 'auto');
        $this->imageManager = $this->createImageManager();
        $this->originalFiles = [];
    }

    /**
     * Create ImageManager with appropriate driver
     *
     * @return Image
     */
    protected function createImageManager(): Image
    {
        $driver = $this->getBestAvailableDriver();

        if ($driver === 'imagick') {
            return new Image(new ImagickDriver());
        } else {
            return new Image(new GdDriver());
        }
    }

    /**
     * Get the best available driver
     *
     * @return string
     */
    protected function getBestAvailableDriver(): string
    {
        if ($this->driver === 'auto') {
            // Prefer Imagick if available, fallback to GD
            if ($this->isImagickAvailable()) {
                return 'imagick';
            } elseif ($this->isGdAvailable()) {
                return 'gd';
            } else {
                throw new \RuntimeException('Neither Imagick nor GD are available. Please install one of them.');
            }
        }

        if ($this->driver === 'imagick' && !$this->isImagickAvailable()) {
            throw new \RuntimeException('Imagick is not available. Please install the Imagick extension.');
        }

        if ($this->driver === 'gd' && !$this->isGdAvailable()) {
            throw new \RuntimeException('GD is not available. Please install the GD extension.');
        }

        return $this->driver;
    }

    /**
     * Check if Imagick is available
     *
     * @return bool
     */
    public static function isImagickAvailable(): bool
    {
        return extension_loaded('imagick') && class_exists('Imagick');
    }

    /**
     * Check if GD is available
     *
     * @return bool
     */
    public static function isGdAvailable(): bool
    {
        return extension_loaded('gd');
    }

    /**
     * Get current driver
     *
     * @return string
     */
    public function getCurrentDriver(): string
    {
        return $this->getBestAvailableDriver();
    }

    /**
     * Get available drivers
     *
     * @return array
     */
    public static function getAvailableDrivers(): array
    {
        $drivers = [];

        if (self::isImagickAvailable()) {
            $drivers[] = 'imagick';
        }

        if (self::isGdAvailable()) {
            $drivers[] = 'gd';
        }

        return $drivers;
    }

    /**
     * Check if a specific operation is supported by current driver
     *
     * @param string $operation
     * @param array $options
     * @return bool
     */
    public function isOperationSupported(string $operation, array $options = []): bool
    {
        $driver = $this->getCurrentDriver();

        switch ($operation) {
            case 'watermark':
                if (isset($options['type']) && $options['type'] === 'text') {
                    // Text watermarks work better with Imagick
                    return $driver === 'imagick';
                }
                return true;

            case 'convert':
                $format = $options['format'] ?? 'jpg';
                if ($format === 'webp') {
                    // WebP support is better in Imagick
                    return $driver === 'imagick' || (self::isGdAvailable() && function_exists('imagewebp'));
                }
                return true;

            case 'avatar':
                if (isset($options['rounded']) && $options['rounded']) {
                    // Rounded avatars work better with Imagick
                    return $driver === 'imagick';
                }
                return true;

            case 'progressive':
                // Progressive JPEG is better supported in Imagick
                return $driver === 'imagick';

            default:
                return true;
        }
    }

    /**
     * Get operation compatibility warnings
     *
     * @param string $operation
     * @param array $options
     * @return array
     */
    public function getOperationWarnings(string $operation, array $options = []): array
    {
        $warnings = [];
        $driver = $this->getCurrentDriver();

        switch ($operation) {
            case 'watermark':
                if (isset($options['type']) && $options['type'] === 'text' && $driver === 'gd') {
                    $warnings[] = 'Text watermarks may have limited font support in GD. Consider using Imagick for better text rendering.';
                }
                break;

            case 'convert':
                $format = $options['format'] ?? 'jpg';
                if ($format === 'webp' && $driver === 'gd') {
                    $warnings[] = 'WebP support in GD may be limited. Consider using Imagick for better WebP support.';
                }
                break;

            case 'avatar':
                if (isset($options['rounded']) && $options['rounded'] && $driver === 'gd') {
                    $warnings[] = 'Rounded avatars may not render perfectly in GD. Consider using Imagick for better circle clipping.';
                }
                break;
        }

        return $warnings;
    }

    /**
     * Set driver preference
     *
     * @param string $driver 'auto', 'gd', or 'imagick'
     * @return self
     */
    public function setDriver(string $driver): self
    {
        $allowedDrivers = ['auto', 'gd', 'imagick'];

        if (!in_array($driver, $allowedDrivers)) {
            throw new \InvalidArgumentException('Driver must be one of: ' . implode(', ', $allowedDrivers));
        }

        $previousDriver = $this->driver;
        $this->driver = $driver;
        $this->imageManager = $this->createImageManager();

        // Log if driver is being overridden from config
        if ($previousDriver !== $driver) {
            $configuredDriver = self::getConfiguredDriver();
            if ($driver !== $configuredDriver) {
                Log::info("MediaForge: Driver overridden from config '{$configuredDriver}' to '{$driver}'");
            }
        }

        return $this;
    }

    /**
     * Check if intervention/image dependency is available
     *
     * @return bool
     */
    public static function isImageProcessingAvailable(): bool
    {
        return class_exists('Intervention\Image\ImageManager');
    }

    /**
     * Get installation instructions for intervention/image
     *
     * @return string
     */
    public static function getInstallationInstructions(): string
    {
        return 'Please install the intervention/image package by running: composer require intervention/image';
    }

    /**
     * Get current operations array (for testing/debugging)
     *
     * @return array
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * Get the configured driver from environment/config
     *
     * @return string
     */
    public static function getConfiguredDriver(): string
    {
        return config('vormia.mediaforge.driver', 'auto');
    }

    /**
     * Check if current driver is overridden from config
     *
     * @return bool
     */
    public function isDriverOverridden(): bool
    {
        $configuredDriver = self::getConfiguredDriver();
        return $this->driver !== $configuredDriver;
    }

    /**
     * Get driver information including configuration status
     *
     * @return array
     */
    public function getDriverInfo(): array
    {
        return [
            'current' => $this->getCurrentDriver(),
            'configured' => self::getConfiguredDriver(),
            'overridden' => $this->isDriverOverridden(),
            'available' => self::getAvailableDrivers(),
        ];
    }

    /**
     * Get default quality from config
     *
     * @return int
     */
    public static function getDefaultQuality(): int
    {
        return config('vormia.mediaforge.default_quality', 85);
    }

    /**
     * Get default format from config
     *
     * @return string
     */
    public static function getDefaultFormat(): string
    {
        return config('vormia.mediaforge.default_format', 'webp');
    }

    /**
     * Get auto override setting from config
     *
     * @return bool
     */
    public static function getAutoOverride(): bool
    {
        return config('vormia.mediaforge.auto_override', false);
    }

    /**
     * Get preserve originals setting from config
     *
     * @return bool
     */
    public static function getPreserveOriginals(): bool
    {
        return config('vormia.mediaforge.preserve_originals', true);
    }


    /**
     * Upload files or images
     *
     * @param array|UploadedFile $files
     * @return self
     */
    public function upload($files): self
    {
        // Convert single file to array for consistent handling
        if ($files instanceof UploadedFile) {
            $files = [$files];
        }

        // Validate that all items are UploadedFile instances
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                throw new \InvalidArgumentException('All items must be UploadedFile instances');
            }

            if (!$file->isValid()) {
                throw new \InvalidArgumentException('Invalid file upload: ' . $file->getErrorMessage());
            }
        }

        $this->files = array_merge($this->files, $files);
        return $this;
    }

    /**
     * Upload files from URLs
     *
     * @param array|string $urls
     * @return self
     */
    public function uploadFromUrl($urls): self
    {
        // Convert single URL to array for consistent handling
        if (is_string($urls)) {
            $urls = [$urls];
        }

        // Validate URLs
        foreach ($urls as $url) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new \InvalidArgumentException("Invalid URL: $url");
            }
        }

        $this->urls = array_merge($this->urls, $urls);
        return $this;
    }

    /**
     * Set upload path
     *
     * @param string|null $path
     * @return self
     */
    public function to(?string $path): self
    {
        $this->uploadPath = $path;
        return $this;
    }

    /**
     * Set whether to use year folders (Y/m/d structure)
     *
     * @param bool $use
     * @return self
     */
    public function useYearFolder(bool $use = true): self
    {
        $this->yearFolder = $use;
        return $this;
    }

    /**
     * Set whether to randomize file name
     *
     * @param bool $randomize
     * @return self
     */
    public function randomizeFileName(bool $randomize = true): self
    {
        $this->randomizeFileName = $randomize;
        return $this;
    }

    /**
     * Set whether to upload to private directory
     *
     * @param bool $private
     * @return self
     */
    public function privateUpload(bool $private = true): self
    {
        $this->privateUpload = $private;
        return $this;
    }

    /**
     * Add resize operation to the queue
     *
     * @param int $width
     * @param int $height
     * @param bool $keepAspectRatio
     * @param string|null $fillColor
     * @param bool|null $override Whether to override the original file - null to use config default
     * @return self
     */
    public function resize(int $width, int $height, bool $keepAspectRatio = true, ?string $fillColor = null, ?bool $override = null): self
    {
        $override = $override ?? self::getAutoOverride();

        $this->operations[] = [
            'type' => 'resize',
            'width' => $width,
            'height' => $height,
            'keepAspectRatio' => $keepAspectRatio,
            'fillColor' => $fillColor,
            'override' => $override
        ];
        return $this;
    }

    /**
     * Add compress operation to the queue
     *
     * @param int|null $quality (1-100) - null to use config default
     * @param bool|null $override Whether to override the original file - null to use config default
     * @return self
     */
    public function compress(?int $quality = null, ?bool $override = null): self
    {
        $quality = $quality ?? self::getDefaultQuality();
        $override = $override ?? self::getAutoOverride();

        if ($quality < 1 || $quality > 100) {
            throw new \InvalidArgumentException('Quality must be between 1 and 100');
        }

        $this->operations[] = [
            'type' => 'compress',
            'quality' => $quality,
            'override' => $override
        ];
        return $this;
    }

    /**
     * Add convert format operation to the queue
     *
     * @param string|null $format (jpg, png, webp, gif) - null to use config default
     * @param integer|null $quality (default from config) - null to use config default
     * @param bool|null $progressive (default false) - null to use false
     * @param bool|null $override Whether to override the original file - null to use config default
     * @return self
     */
    public function convert(?string $format = null, ?int $quality = null, ?bool $progressive = false, ?bool $override = null): self
    {
        $format = $format ?? self::getDefaultFormat();
        $quality = $quality ?? self::getDefaultQuality();
        $override = $override ?? self::getAutoOverride();

        $allowedFormats = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $format = strtolower($format);

        if (!in_array($format, $allowedFormats)) {
            throw new \InvalidArgumentException('Format must be one of: ' . implode(', ', $allowedFormats));
        }

        // Check if operation is supported by current driver
        if (!$this->isOperationSupported('convert', ['format' => $format])) {
            $warnings = $this->getOperationWarnings('convert', ['format' => $format]);
            if (!empty($warnings)) {
                Log::warning('MediaForge: ' . implode(' ', $warnings));
            }
        }

        // Check progressive JPEG support
        if ($progressive && !$this->isOperationSupported('progressive')) {
            Log::warning('MediaForge: Progressive JPEG is not well supported in GD. Consider using Imagick for better progressive JPEG support.');
        }

        $this->operations[] = [
            'type' => 'convert',
            'format' => $format,
            'quality' => $quality,
            'progressive' => $progressive,
            'override' => $override
        ];
        return $this;
    }

    /**
     * Add thumbnail generation operation to the queue
     *
     * @param array $sizes Array of [width, height, name] arrays
     * @return self
     */
    public function thumbnail(array $sizes): self
    {
        foreach ($sizes as $size) {
            if (!is_array($size) || count($size) < 2) {
                throw new \InvalidArgumentException('Each size must be array with [width, height, optional_name]');
            }
        }

        $this->operations[] = [
            'type' => 'thumbnail',
            'sizes' => $sizes
        ];
        return $this;
    }

    /**
     * Add watermark operation to the queue
     *
     * @param string $watermark Path to watermark image or text string
     * @param string $type 'image' or 'text'
     * @param string $position 'top-left', 'top-right', 'bottom-left', 'bottom-right', 'center'
     * @param array $options Additional options (opacity, size, color, etc.)
     * @param bool|null $override Whether to override the original file - null to use config default
     * @return self
     */
    public function watermark(string $watermark, string $type = 'image', string $position = 'bottom-right', array $options = [], ?bool $override = null): self
    {
        $override = $override ?? self::getAutoOverride();

        $allowedTypes = ['image', 'text'];
        $allowedPositions = ['top-left', 'top-right', 'bottom-left', 'bottom-right', 'center'];

        if (!in_array($type, $allowedTypes)) {
            throw new \InvalidArgumentException('Type must be: image or text');
        }

        if (!in_array($position, $allowedPositions)) {
            throw new \InvalidArgumentException('Position must be one of: ' . implode(', ', $allowedPositions));
        }

        // Check if operation is supported by current driver
        if (!$this->isOperationSupported('watermark', ['type' => $type])) {
            $warnings = $this->getOperationWarnings('watermark', ['type' => $type]);
            if (!empty($warnings)) {
                Log::warning('MediaForge: ' . implode(' ', $warnings));
            }
        }

        $this->operations[] = [
            'type' => 'watermark',
            'watermark' => $watermark,
            'watermarkType' => $type,
            'position' => $position,
            'options' => array_merge(['opacity' => 50, 'size' => 20], $options),
            'override' => $override
        ];
        return $this;
    }

    /**
     * Add avatar creation operation to the queue
     *
     * @param int $size
     * @param bool $rounded
     * @return self
     */
    public function makeAvatar(int $size = 200, bool $rounded = true): self
    {
        // Check if operation is supported by current driver
        if (!$this->isOperationSupported('avatar', ['rounded' => $rounded])) {
            $warnings = $this->getOperationWarnings('avatar', ['rounded' => $rounded]);
            if (!empty($warnings)) {
                Log::warning('MediaForge: ' . implode(' ', $warnings));
            }
        }

        $this->operations[] = [
            'type' => 'avatar',
            'size' => $size,
            'rounded' => $rounded
        ];
        return $this;
    }

    /**
     * Set file to delete after successful upload
     *
     * @param string|array $filePaths
     * @return self
     */
    public function deleteOldFile($filePaths): self
    {
        if (is_string($filePaths)) {
            $filePaths = [$filePaths];
        }

        $this->operations[] = [
            'type' => 'deleteOld',
            'files' => $filePaths
        ];
        return $this;
    }

    /**
     * Set disk
     *
     * @param string $disk
     * @return self
     */
    public function setDisk(string $disk): self
    {
        $this->disk = $disk;
        return $this;
    }

    /**
     * Set visibility
     *
     * @param string $visibility
     * @return self
     */
    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;
        return $this;
    }

    /**
     * Set folder
     *
     * @param string $folder
     * @return self
     */
    public function setFolder(string $folder): self
    {
        $this->folder = $folder;
        return $this;
    }

    /**
     * Set operations
     *
     * @param array $operations
     * @return self
     */
    public function setOperations(array $operations): self
    {
        $this->operations = $operations;
        return $this;
    }

    /**
     * Execute all queued operations
     *
     * @return string|array
     * @throws \Exception
     */
    public function run()
    {
        if (empty($this->files) && empty($this->urls)) {
            throw new \Exception('No files or URLs provided for upload');
        }

        $results = [];

        // Process uploaded files
        foreach ($this->files as $file) {
            $results[] = $this->processFile($file);
        }

        // Process URLs
        foreach ($this->urls as $url) {
            $results[] = $this->processUrl($url);
        }

        // Execute delete operations after successful uploads
        $this->executeDeleteOperations();

        // Reset state for next operation
        $this->resetState();

        return count($results) === 1 ? $results[0] : $results;
    }

    /**
     * Handle file upload
     *
     * @param array $files
     * @return array
     */
    public function handle(array $files): array
    {
        $results = [];

        foreach ($files as $file) {
            $results[] = $this->processFile($file, $this->folder, $this->operations);
        }

        return $results;
    }

    /**
     * Delete files based on type(s)
     *
     * @param string $filePath Relative path to the file (e.g., 'media/food-categories/2025/07/28/donut_1.png')
     * @param string|array $type Type(s) of files to delete: 'only', 'all', 'thumb', 'resized', 'compressed', 'converted', 'watermark', 'avatar', 'processed'
     * @return array Array containing deletion results and information
     */
    public function delete(string $filePath, string|array $type = 'all'): array
    {
        $result = [
            'success' => false,
            'deleted_files' => [],
            'errors' => [],
            'total_deleted' => 0,
            'message' => ''
        ];

        try {
            // Normalize type parameter to array
            $types = is_array($type) ? $type : [$type];

            // Validate type parameters
            $validTypes = ['only', 'all', 'thumb', 'resized', 'compressed', 'converted', 'watermark', 'avatar', 'processed'];
            foreach ($types as $singleType) {
                if (!in_array($singleType, $validTypes)) {
                    $result['errors'][] = "Invalid type '$singleType'. Must be one of: " . implode(', ', $validTypes);
                    $result['message'] = 'Invalid type parameter provided';
                    return $result;
                }
            }

            // Validate and normalize the file path
            $filePath = $this->normalizeFilePath($filePath);

            if (!$this->isValidFilePath($filePath)) {
                $result['errors'][] = "Invalid file path: $filePath";
                $result['message'] = 'Invalid file path provided';
                return $result;
            }

            // Find files to delete based on types
            $filesToDelete = [];
            foreach ($types as $singleType) {
                $filesForType = $this->findFilesByType($filePath, $singleType);
                $filesToDelete = array_merge($filesToDelete, $filesForType);
            }

            // Remove duplicates
            $filesToDelete = array_unique($filesToDelete);

            if (empty($filesToDelete)) {
                $typeString = is_array($type) ? implode(', ', $type) : $type;
                $result['message'] = "No files found to delete for type(s): $typeString";
                return $result;
            }

            // Delete the files
            foreach ($filesToDelete as $fileToDelete) {
                $fullDeletePath = $this->getFullPath($fileToDelete);
                if ($this->deleteFile($fullDeletePath)) {
                    $result['deleted_files'][] = $fileToDelete;
                    $result['total_deleted']++;
                } else {
                    $result['errors'][] = "Failed to delete file: $fileToDelete";
                }
            }

            $result['success'] = $result['total_deleted'] > 0;
            $typeString = is_array($type) ? implode(', ', $type) : $type;
            $result['message'] = $result['success']
                ? "Successfully deleted {$result['total_deleted']} files (type(s): $typeString)"
                : 'No files were deleted';

            // Log the deletion operation
            Log::info("MediaForge: Delete operation completed", [
                'file_path' => $filePath,
                'types' => $types,
                'deleted_count' => $result['total_deleted'],
                'errors' => $result['errors']
            ]);
        } catch (\Exception $e) {
            $result['errors'][] = "Exception during deletion: " . $e->getMessage();
            $result['message'] = 'An error occurred during deletion';
            Log::error("MediaForge: Delete operation failed", [
                'file_path' => $filePath,
                'types' => $types,
                'error' => $e->getMessage()
            ]);
        }

        return $result;
    }

    /**
     * Find files to delete based on type
     *
     * @param string $filePath
     * @param string $type
     * @return array
     */
    protected function findFilesByType(string $filePath, string $type): array
    {
        $pathInfo = pathinfo($filePath);
        $directory = $pathInfo['dirname'];
        $baseName = $pathInfo['filename'];
        $extension = $pathInfo['extension'];

        $fullDirectoryPath = $this->getFullPath($directory);

        if (!is_dir($fullDirectoryPath)) {
            return [];
        }

        // Get all files in the directory
        $files = File::files($fullDirectoryPath);
        $filesToDelete = [];

        foreach ($files as $file) {
            $fileName = basename($file);
            $relativePath = $directory . '/' . $fileName;

            switch ($type) {
                case 'only':
                    // Delete only the original file
                    if ($fileName === basename($filePath)) {
                        $filesToDelete[] = $filePath;
                    }
                    break;

                case 'all':
                    // Delete original and all related files
                    if ($fileName === basename($filePath)) {
                        $filesToDelete[] = $filePath;
                    } elseif ($this->isRelatedFile($fileName, $baseName, $extension)) {
                        $filesToDelete[] = $relativePath;
                    }
                    break;

                case 'thumb':
                    // Delete only thumbnail files
                    if ($this->isThumbnailFile($fileName, $baseName, $extension)) {
                        $filesToDelete[] = $relativePath;
                    }
                    break;

                case 'resized':
                    // Delete only resized files
                    if ($this->isResizedFile($fileName, $baseName, $extension)) {
                        $filesToDelete[] = $relativePath;
                    }
                    break;

                case 'compressed':
                    // Delete only compressed files
                    if ($this->isCompressedFile($fileName, $baseName)) {
                        $filesToDelete[] = $relativePath;
                    }
                    break;

                case 'converted':
                    // Delete only converted files
                    if ($this->isConvertedFile($fileName, $baseName)) {
                        $filesToDelete[] = $relativePath;
                    }
                    break;

                case 'watermark':
                    // Delete only watermarked files
                    if ($this->isWatermarkedFile($fileName, $baseName, $extension)) {
                        $filesToDelete[] = $relativePath;
                    }
                    break;

                case 'avatar':
                    // Delete only avatar files
                    if ($this->isAvatarFile($fileName, $baseName, $extension)) {
                        $filesToDelete[] = $relativePath;
                    }
                    break;

                case 'processed':
                    // Delete all processed files (everything except original)
                    if ($fileName !== basename($filePath) && $this->isRelatedFile($fileName, $baseName, $extension)) {
                        $filesToDelete[] = $relativePath;
                    }
                    break;
            }
        }

        return $filesToDelete;
    }

    /**
     * Check if a file is a related file
     *
     * @param string $fileName
     * @param string $baseName
     * @param string $extension
     * @return bool
     */
    protected function isRelatedFile(string $fileName, string $baseName, string $extension): bool
    {
        $patterns = $this->getFilePatterns($baseName, $extension);

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $fileName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a file is a thumbnail
     *
     * @param string $fileName
     * @param string $baseName
     * @param string $extension
     * @return bool
     */
    protected function isThumbnailFile(string $fileName, string $baseName, string $extension): bool
    {
        $escapedBaseName = preg_quote($baseName, '/');
        $escapedExtension = preg_quote($extension, '/');

        $thumbnailPatterns = [
            "/^{$escapedBaseName}_[a-zA-Z0-9_]+\.{$escapedExtension}$/",
            "/^{$escapedBaseName}_\d+x\d+\.{$escapedExtension}$/"
        ];

        foreach ($thumbnailPatterns as $pattern) {
            if (preg_match($pattern, $fileName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a file is a resized version
     *
     * @param string $fileName
     * @param string $baseName
     * @param string $extension
     * @return bool
     */
    protected function isResizedFile(string $fileName, string $baseName, string $extension): bool
    {
        $escapedBaseName = preg_quote($baseName, '/');
        $escapedExtension = preg_quote($extension, '/');
        $pattern = "/^{$escapedBaseName}-\d+-\d+\.{$escapedExtension}$/";

        return preg_match($pattern, $fileName);
    }

    /**
     * Check if a file is a compressed version
     *
     * @param string $fileName
     * @param string $baseName
     * @return bool
     */
    protected function isCompressedFile(string $fileName, string $baseName): bool
    {
        $escapedBaseName = preg_quote($baseName, '/');
        $pattern = "/^{$escapedBaseName}-compressed\.(webp|jpg|jpeg|png|gif)$/";

        return preg_match($pattern, $fileName);
    }

    /**
     * Check if a file is a converted version
     *
     * @param string $fileName
     * @param string $baseName
     * @return bool
     */
    protected function isConvertedFile(string $fileName, string $baseName): bool
    {
        $escapedBaseName = preg_quote($baseName, '/');
        $pattern = "/^{$escapedBaseName}-(webp|jpg|jpeg|png|gif)\.(webp|jpg|jpeg|png|gif)$/";

        return preg_match($pattern, $fileName);
    }

    /**
     * Check if a file is a watermarked version
     *
     * @param string $fileName
     * @param string $baseName
     * @param string $extension
     * @return bool
     */
    protected function isWatermarkedFile(string $fileName, string $baseName, string $extension): bool
    {
        $escapedBaseName = preg_quote($baseName, '/');
        $escapedExtension = preg_quote($extension, '/');
        $pattern = "/^{$escapedBaseName}-watermark\.{$escapedExtension}$/";

        return preg_match($pattern, $fileName);
    }

    /**
     * Check if a file is an avatar version
     *
     * @param string $fileName
     * @param string $baseName
     * @param string $extension
     * @return bool
     */
    protected function isAvatarFile(string $fileName, string $baseName, string $extension): bool
    {
        $escapedBaseName = preg_quote($baseName, '/');
        $escapedExtension = preg_quote($extension, '/');
        $pattern = "/^{$escapedBaseName}_avatar\.{$escapedExtension}$/";

        return preg_match($pattern, $fileName);
    }

    /**
     * Get regex patterns for finding related files
     *
     * @param string $baseName
     * @param string $extension
     * @return array
     */
    public function getFilePatterns(string $baseName, string $extension): array
    {
        $escapedBaseName = preg_quote($baseName, '/');
        $escapedExtension = preg_quote($extension, '/');

        return [
            // Resized files: {baseName}-{width}-{height}.{extension}
            "/^{$escapedBaseName}-\d+-\d+\.{$escapedExtension}$/",

            // Compressed files: {baseName}-compressed.{format}
            "/^{$escapedBaseName}-compressed\.(webp|jpg|jpeg|png|gif)$/",

            // Converted files: {baseName}-{format}.{format}
            "/^{$escapedBaseName}-(webp|jpg|jpeg|png|gif)\.(webp|jpg|jpeg|png|gif)$/",

            // Watermarked files: {baseName}-watermark.{extension}
            "/^{$escapedBaseName}-watermark\.{$escapedExtension}$/",

            // Avatar files: {baseName}_avatar.{extension}
            "/^{$escapedBaseName}_avatar\.{$escapedExtension}$/",

            // Thumbnail files: {baseName}_{suffix}.{extension}
            "/^{$escapedBaseName}_[a-zA-Z0-9_]+\.{$escapedExtension}$/",

            // Thumbnail files with dimensions: {baseName}_{width}x{height}.{extension}
            "/^{$escapedBaseName}_\d+x\d+\.{$escapedExtension}$/"
        ];
    }

    /**
     * Safely delete a single file
     *
     * @param string $fullPath
     * @return bool
     */
    protected function deleteFile(string $fullPath): bool
    {
        if (!file_exists($fullPath)) {
            return false;
        }

        try {
            return File::delete($fullPath);
        } catch (\Exception $e) {
            Log::warning("MediaForge: Failed to delete file", [
                'file_path' => $fullPath,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get full system path from relative path
     *
     * @param string $relativePath
     * @return string
     */
    protected function getFullPath(string $relativePath): string
    {
        if ($this->privateUpload) {
            return storage_path("app/public/$relativePath");
        } else {
            return public_path($relativePath);
        }
    }

    /**
     * Normalize file path (remove leading/trailing slashes, ensure proper format)
     *
     * @param string $filePath
     * @return string
     */
    public function normalizeFilePath(string $filePath): string
    {
        // Remove leading and trailing slashes
        $filePath = trim($filePath, '/');

        // Ensure it starts with 'media' or 'media-private'
        if (!str_starts_with($filePath, 'media') && !str_starts_with($filePath, 'media-private')) {
            $filePath = 'media/' . $filePath;
        }

        return $filePath;
    }

    /**
     * Validate if the file path is safe and within allowed directories
     *
     * @param string $filePath
     * @return bool
     */
    public function isValidFilePath(string $filePath): bool
    {
        // Check for directory traversal attempts
        if (str_contains($filePath, '..') || str_contains($filePath, '//')) {
            return false;
        }

        // Ensure path starts with allowed prefixes
        $allowedPrefixes = ['media/', 'media-private/'];
        $isValidPrefix = false;

        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($filePath, $prefix)) {
                $isValidPrefix = true;
                break;
            }
        }

        if (!$isValidPrefix) {
            return false;
        }

        // Check if file has a valid extension
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        return in_array($extension, $allowedExtensions);
    }

    /**
     * Get information about what files would be deleted without actually deleting them
     *
     * @param string $filePath
     * @param string|array $type
     * @return array
     */
    public function getDeletePreview(string $filePath, string|array $type = 'all'): array
    {
        $result = [
            'files_to_delete' => [],
            'total_files' => 0,
            'is_safe' => true,
            'errors' => []
        ];

        try {
            // Normalize type parameter to array
            $types = is_array($type) ? $type : [$type];

            // Validate type parameters
            $validTypes = ['only', 'all', 'thumb', 'resized', 'compressed', 'converted', 'watermark', 'avatar', 'processed'];
            foreach ($types as $singleType) {
                if (!in_array($singleType, $validTypes)) {
                    $result['errors'][] = "Invalid type '$singleType'. Must be one of: " . implode(', ', $validTypes);
                    $result['is_safe'] = false;
                    return $result;
                }
            }

            $filePath = $this->normalizeFilePath($filePath);

            if (!$this->isValidFilePath($filePath)) {
                $result['errors'][] = "Invalid file path: $filePath";
                $result['is_safe'] = false;
                return $result;
            }

            // Find files to delete based on types
            $filesToDelete = [];
            foreach ($types as $singleType) {
                $filesForType = $this->findFilesByType($filePath, $singleType);
                $filesToDelete = array_merge($filesToDelete, $filesForType);
            }

            // Remove duplicates
            $result['files_to_delete'] = array_unique($filesToDelete);
            $result['total_files'] = count($result['files_to_delete']);
        } catch (\Exception $e) {
            $result['errors'][] = "Exception during preview: " . $e->getMessage();
            $result['is_safe'] = false;
        }

        return $result;
    }

    /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

    /**
     * Process a single uploaded file
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function processFile(UploadedFile $file): string
    {
        $uploadDir = $this->prepareFolderPath();
        $fileName = $this->generateFileName($file->getClientOriginalName(), $uploadDir);

        // Create directory if it doesn't exist
        $fullPath = $this->privateUpload
            ? storage_path("app/public/$uploadDir")
            : public_path($uploadDir);

        if (!File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }

        // Move uploaded file
        $file->move($fullPath, $fileName);

        // Apply image operations if it's an image
        if ($this->isImage($fileName)) {
            $this->applyImageOperations($fullPath . '/' . $fileName);
        }

        return $this->privateUpload
            ? "storage/$uploadDir/$fileName"
            : "$uploadDir/$fileName";
    }

    /**
     * Check if a file is an image
     *
     * @param string $fileName
     * @return bool
     */
    protected function isImage(string $fileName): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        return in_array(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)), $imageExtensions);
    }

    /**
     * Process a single URL download
     *
     * @param string $url
     * @return string
     */
    protected function processUrl(string $url): string
    {
        $response = Http::timeout(10)->get($url);

        if (!$response->successful()) {
            throw new \Exception("Failed to download file from URL: $url");
        }

        $fileData = $response->body();

        $uploadDir = $this->prepareFolderPath();
        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $fileName = $this->generateFileName("download.$extension", $uploadDir);

        $fullPath = $this->privateUpload
            ? storage_path("app/private/$uploadDir")
            : public_path($uploadDir);

        if (!File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }

        file_put_contents($fullPath . '/' . $fileName, $fileData);

        if ($this->isImage($fileName)) {
            $this->applyImageOperations($fullPath . '/' . $fileName);
        }

        return $this->privateUpload
            ? "storage/private/$uploadDir/$fileName"
            : "$uploadDir/$fileName";
    }

    /**
     * Execute delete operations for old files
     */
    protected function executeDeleteOperations(): void
    {
        foreach ($this->operations as $operation) {
            if ($operation['type'] === 'deleteOld') {
                foreach ($operation['files'] as $filePath) {
                    $fullPath = $this->privateUpload
                        ? storage_path("app/public/$filePath")
                        : public_path($filePath);

                    if (File::exists($fullPath)) {
                        File::delete($fullPath);
                    }
                }
            }
        }
    }

    /**
     * Reset state for next operation
     */
    protected function resetState(): void
    {
        $this->files = [];
        $this->urls = [];
        $this->operations = [];
        $this->uploadPath = null;
        $this->yearFolder = true;
        $this->randomizeFileName = true;
        $this->privateUpload = false;
        $this->originalFiles = [];
    }

    /**
     * Prepare folder path with year structure if enabled
     *
     * @return string
     */
    protected function prepareFolderPath(): string
    {
        $directory = $this->privateUpload ? 'media-private' : 'media';

        if ($this->uploadPath) {
            $directory .= '/' . trim($this->uploadPath, '/');
        }

        if ($this->yearFolder) {
            $autoFolder = date('Y') . '/' . date('m') . '/' . date('d');
            $directory .= '/' . $autoFolder;
        }

        return $directory;
    }

    /**
     * Ensure a unique filename by appending a counter if necessary
     *
     * @param string $directory
     * @param string $fileName
     * @return string
     */
    protected function ensureUniqueFileName(string $directory, string $fileName): string
    {
        $pathInfo = pathinfo($fileName);
        $baseName = $pathInfo['filename'];
        $extension = $pathInfo['extension'];
        $counter = 1;

        $newFileName = $fileName;

        while (file_exists($directory . DIRECTORY_SEPARATOR . $newFileName)) {
            $newFileName = $baseName . '_' . $counter . '.' . $extension;
            $counter++;
        }

        return $newFileName;
    }

    /**
     * Generate unique filename
     *
     * @param string $originalName
     * @param string $uploadDir
     * @return string
     */
    protected function generateFileName(string $originalName, string $uploadDir): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);

        if ($this->randomizeFileName) {
            $name = uniqid() . '.' . $extension;
        } else {
            $name = $originalName;
        }

        // Ensure unique filename
        $fullPath = $this->privateUpload
            ? storage_path("app/public/$uploadDir")
            : public_path($uploadDir);

        return $this->ensureUniqueFileName($fullPath, $name);
    }

    /**
     * Apply image operations to the image
     *
     * @param string $filePath
     * @return void
     */
    protected function applyImageOperations(string $filePath): void
    {
        try {
            $image = $this->imageManager->read($filePath);
            $currentDriver = $this->getCurrentDriver();
            $originalFilePath = $filePath; // Track original file for potential deletion

            foreach ($this->operations as $operation) {
                switch ($operation['type']) {
                    case 'resize':
                        $newWidth = $operation['width'];
                        $newHeight = $operation['height'];
                        $keepAspectRatio = $operation['keepAspectRatio'];
                        $fillColor = $operation['fillColor'];
                        $override = $operation['override'];

                        if ($keepAspectRatio) {
                            $image->scale($newWidth, $newHeight);
                        } else {
                            $image->resize($newWidth, $newHeight);
                        }

                        if ($fillColor) {
                            $image->fill($fillColor);
                        }

                        if ($override) {
                            // Generate new filename with width-height format
                            $pathInfo = pathinfo($filePath);
                            $baseName = $pathInfo['filename'];
                            $extension = $pathInfo['extension'];
                            $directory = $pathInfo['dirname'];

                            $newFileName = $baseName . '-' . $newWidth . '-' . $newHeight . '.' . $extension;
                            $newFilePath = $directory . '/' . $newFileName;

                            // Ensure unique filename
                            $newFilePath = $this->ensureUniqueFileName($directory, $newFileName);

                            $image->save($newFilePath);
                            $filePath = $newFilePath; // Update filePath for subsequent operations

                            // Track original file for deletion
                            if ($originalFilePath !== $filePath) {
                                $this->originalFiles[] = $originalFilePath;
                                $originalFilePath = $filePath;
                            }
                        }
                        break;

                    case 'compress':
                        $filePath = $this->compressImage(
                            $filePath,
                            $operation['format'] ?? 'webp',
                            $operation['quality'] ?? 75,
                            $operation['override'] ?? false
                        );
                        $image = $this->imageManager->read($filePath); // Reload updated version

                        // Track original file for deletion if override is true
                        if ($operation['override'] ?? false) {
                            $this->originalFiles[] = $originalFilePath;
                            $originalFilePath = $filePath;
                        }
                        break;

                    case 'convert':
                        $filePath = $this->convertFormat($image, $filePath, $operation['format'], $operation['quality'], $operation['progressive'], $operation['override']);
                        $image = $this->imageManager->read($filePath); // reload the image with new format

                        // Track original file for deletion if override is true
                        if ($operation['override'] ?? false) {
                            $this->originalFiles[] = $originalFilePath;
                            $originalFilePath = $filePath;
                        }
                        break;

                    case 'thumbnail':
                        $this->generateThumbnails($image, $filePath, $operation['sizes']);
                        break;

                    case 'watermark':
                        $filePath = $this->applyWatermark($image, $operation, $filePath);
                        $image = $this->imageManager->read($filePath); // Reload updated version

                        // Track original file for deletion if override is true
                        if ($operation['override'] ?? false) {
                            $this->originalFiles[] = $originalFilePath;
                            $originalFilePath = $filePath;
                        }
                        break;

                    case 'avatar':
                        $this->makeAvatarImage($image, $filePath, $operation);
                        break;

                    case 'deleteOld':
                        $this->deleteFiles($operation['files']);
                        break;

                        // Add other operations as needed
                }
            }

            // Save final image with driver-specific options
            $saveOptions = [];

            // Handle progressive JPEG for Imagick
            if ($currentDriver === 'imagick' && pathinfo($filePath, PATHINFO_EXTENSION) === 'jpg') {
                $saveOptions['progressive'] = true;
            }

            $image->save($filePath, ...$saveOptions);

            // Delete original files after successful processing
            $this->deleteOriginalFiles();
        } catch (\Exception $e) {
            // Log error and optionally delete corrupted file
            Log::error("Image processing failed for file $filePath: " . $e->getMessage());

            if (File::exists($filePath)) {
                File::delete($filePath);
            }

            throw new \Exception("Failed to process image file.");
        }
    }

    /**
     * Convert image format
     *
     * @param \Intervention\Image\Image $image
     * @param string $filePath
     * @param string $format
     * @param int $quality
     * @param bool $progressive
     * @param bool $override
     * @return string
     */
    protected function convertFormat(\Intervention\Image\Image $image, string $filePath, string $format, int $quality = 90, bool $progressive = true, bool $override = false): string
    {
        $pathInfo = pathinfo($filePath);
        $baseName = $pathInfo['filename'];
        $extension = $pathInfo['extension'];
        $directory = $pathInfo['dirname'];

        if ($override) {
            // Override original file with new format
            $newPath = $directory . '/' . $baseName . '.' . $format;
        } else {
            // Create new file with format suffix
            $newPath = $directory . '/' . $baseName . '-' . $format . '.' . $format;
        }

        // Ensure unique filename
        $newPath = $this->ensureUniqueFileName($directory, basename($newPath));

        try {
            $image->save($newPath, quality: $quality);
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to convert image format: " . $e->getMessage());
        }

        return $newPath;
    }

    /**
     * Compress image
     *
     * @param string $filePath
     * @param string $format
     * @param int $quality
     * @param bool $override
     * @return string
     */
    protected function compressImage(string $filePath, string $format = 'webp', int $quality = 75, bool $override = false): string
    {
        // Load image using ImageManager
        $image = $this->imageManager->read($filePath);

        $pathInfo = pathinfo($filePath);
        $baseName = $pathInfo['filename'];
        $extension = $pathInfo['extension'];
        $directory = $pathInfo['dirname'];

        if ($override) {
            // Override original file
            $outputPath = $directory . '/' . $baseName . '.' . $format;
        } else {
            // Create new file with compressed suffix
            $outputPath = $directory . '/' . $baseName . '-compressed.' . $format;
        }

        // Ensure unique filename
        $outputPath = $this->ensureUniqueFileName($directory, basename($outputPath));

        // Save with desired format, quality
        $image->save($outputPath, quality: $quality);

        return $outputPath;
    }

    /**
     * Generate thumbnails for an image
     *
     * @param \Intervention\Image\Image $image
     * @param string $filePath
     * @param array $sizes Array of [width, height, name] arrays
     * @return void
     */
    protected function generateThumbnails(\Intervention\Image\Image $image, string $filePath, array $sizes): void
    {
        foreach ($sizes as $size) {
            [$width, $height, $nameSuffix] = array_pad($size, 3, null);

            $thumbnail = $this->imageManager->read($filePath);

            // Use scaleDown() to prevent upscaling, or scale() to allow
            $thumbnail->scale($width, $height); // or ->scaleDown($width, $height)

            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
            $base = pathinfo($filePath, PATHINFO_FILENAME);
            $thumbName = $base . ($nameSuffix ? "_$nameSuffix" : "_{$width}x{$height}") . '.' . $ext;

            $thumbnail->save(dirname($filePath) . '/' . $thumbName);
        }
    }

    /**
     * Apply watermark to an image
     *
     * @param ImageInterface $image
     * @param array $operation
     * @param string $filePath
     * @return string Updated file path
     */
    protected function applyWatermark(ImageInterface $image, array $operation, string $filePath): string
    {
        $type = $operation['watermarkType'] ?? 'image';
        $position = $operation['position'] ?? 'bottom-right';
        $options = $operation['options'] ?? [];
        $override = $operation['override'] ?? false;

        $offsetX = $options['offset_x'] ?? 10;
        $offsetY = $options['offset_y'] ?? 10;
        $opacity = $options['opacity'] ?? 50;

        if ($type === 'image') {
            $image->place(
                $operation['watermark'],
                $position,
                $offsetX,
                $offsetY,
                $opacity
            );
        } elseif ($type === 'text') {
            $text = $operation['watermark'];
            $fontSize = $options['size'] ?? 20;
            $color = $options['color'] ?? 'rgba(255, 255, 255, 0.5)';
            $fontFile = $options['font'] ?? null; // path to .ttf

            $image->text(
                $text,
                $position,
                $fontSize,
                function (FontFactory $font) use ($fontFile, $fontSize, $color) {
                    $font->size($fontSize);
                    $font->color($color);
                    if ($fontFile) {
                        $font->filename($fontFile);
                    }
                },
                $offsetX,
                $offsetY
            );
        }

        if ($override) {
            // Generate new filename with watermark suffix
            $pathInfo = pathinfo($filePath);
            $baseName = $pathInfo['filename'];
            $extension = $pathInfo['extension'];
            $directory = $pathInfo['dirname'];

            $newFileName = $baseName . '-watermark.' . $extension;
            $newFilePath = $directory . '/' . $newFileName;

            // Ensure unique filename
            $newFilePath = $this->ensureUniqueFileName($directory, $newFileName);

            $image->save($newFilePath);
            return $newFilePath; // Return updated file path
        }

        return $filePath; // Return original file path if not overriding
    }

    /**
     * Make an avatar image
     *
     * @param ImageInterface $image
     * @param string $filePath
     * @param array $options
     * @return void
     */
    protected function makeAvatarImage(ImageInterface $image, string $filePath, array $options = []): void
    {
        $size = $options['size'] ?? 200;
        $rounded = $options['rounded'] ?? true;
        $currentDriver = $this->getCurrentDriver();

        // Resize the original image to fit in a square
        $avatar = $image->resize($size, $size);

        if ($rounded) {
            if ($currentDriver === 'imagick') {
                // Imagick has better circle clipping support
                $canvas = $this->imageManager->create($size, $size)->fill('rgba(0,0,0,0)');

                // Draw circular area as a background
                $canvas->drawCircle($size / 2, $size / 2, function (CircleFactory $circle) use ($size) {
                    $circle->radius($size / 2);
                    $circle->background('white');
                });

                // Paste avatar on top using the drawn circle as a clipping guide
                $canvas->place($avatar, 'center');
                $avatar = $canvas;
            } else {
                // GD has limited circle clipping - create a square avatar with rounded corners approximation
                Log::info('MediaForge: Using GD driver for rounded avatar. Circle clipping may not be perfect.');

                // For GD, we'll create a square avatar without perfect circle clipping
                // The user can implement custom circle clipping if needed
                $avatar = $image->resize($size, $size);
            }
        }

        $avatarPath = str_replace('.', '_avatar.', $filePath);
        $avatar->save($avatarPath);
    }

    /**
     * Delete files
     *
     * @param array $files
     * @return void
     */
    protected function deleteFiles(array $files): void
    {
        foreach ($files as $file) {
            $fullPath = $this->privateUpload
                ? storage_path("app/private/{$file}")
                : public_path($file);

            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
    }

    /**
     * Delete original files that were overridden by operations
     */
    protected function deleteOriginalFiles(): void
    {
        foreach ($this->originalFiles as $filePath) {
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }
        $this->originalFiles = []; // Clear the list after deletion
    }
}
