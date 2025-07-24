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

    public function __construct(string $driver = 'auto')
    {
        // Check if intervention/image is installed
        if (!class_exists('Intervention\Image\ImageManager')) {
            throw new \RuntimeException(
                'The intervention/image package is required for MediaForgeService. ' .
                    'Please install it by running: composer require intervention/image'
            );
        }

        $this->driver = $driver;
        $this->imageManager = $this->createImageManager();
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

        $this->driver = $driver;
        $this->imageManager = $this->createImageManager();

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
     * @return self
     */
    public function resize(int $width, int $height, bool $keepAspectRatio = true, ?string $fillColor = null): self
    {
        $this->operations[] = [
            'type' => 'resize',
            'width' => $width,
            'height' => $height,
            'keepAspectRatio' => $keepAspectRatio,
            'fillColor' => $fillColor
        ];
        return $this;
    }

    /**
     * Add compress operation to the queue
     *
     * @param int $quality (1-100)
     * @return self
     */
    public function compress(int $quality = 80): self
    {
        if ($quality < 1 || $quality > 100) {
            throw new \InvalidArgumentException('Quality must be between 1 and 100');
        }

        $this->operations[] = [
            'type' => 'compress',
            'quality' => $quality
        ];
        return $this;
    }

    /**
     * Add convert format operation to the queue
     *
     * @param string $format (jpg, png, webp, gif)
     * @param integer $quality (default 90)
     * @param bool $progressive (default false)
     * @return self
     */
    public function convert(string $format, $quality = 90, $progressive = false): self
    {
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
     * @return self
     */
    public function watermark(string $watermark, string $type = 'image', string $position = 'bottom-right', array $options = []): self
    {
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
            'options' => array_merge(['opacity' => 50, 'size' => 20], $options)
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

        // Copy uploaded file (don't move since it's already in temp location)
        File::copy($file->getRealPath(), $fullPath . '/' . $fileName);

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

            foreach ($this->operations as $operation) {
                switch ($operation['type']) {
                    case 'resize':
                        if ($operation['keepAspectRatio']) {
                            $image->scale($operation['width'], $operation['height']);
                        } else {
                            $image->resize($operation['width'], $operation['height']);
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
                        break;

                    case 'convert':
                        $filePath = $this->convertFormat($image, $filePath, $operation['format'], $operation['quality'], $operation['progressive']);
                        $image = $this->imageManager->read($filePath); // reload the image with new format
                        break;

                    case 'thumbnail':
                        $this->generateThumbnails($image, $filePath, $operation['sizes']);
                        break;

                    case 'watermark':
                        $this->applyWatermark($image, $operation);
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
     * @return string
     */
    protected function convertFormat(\Intervention\Image\Image $image, string $filePath, string $format, int $quality = 90, bool $progressive = true): string
    {
        $newPath = preg_replace('/\.\w+$/', '.' . $format, $filePath);

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

        // Determine output path
        $outputPath = $override
            ? preg_replace('/\.\w+$/', '.' . $format, $filePath)
            : preg_replace('/\.\w+$/', '_compressed.' . $format, $filePath);

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
     * @return void
     */
    protected function applyWatermark(ImageInterface $image, array $operation): void
    {
        $type = $operation['watermarkType'] ?? 'image';
        $position = $operation['position'] ?? 'bottom-right';
        $options = $operation['options'] ?? [];

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
}
