<?php

namespace Vormia\Vormia\Services\MediaForge;

use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\EncodedImage;
use Intervention\Image\Interfaces\ImageInterface;

class MediaForgeJob
{
    private string $targetSubdir = '';
    private bool $useYearFolder = false;
    private bool $useDateFolders = false;
    private bool $randomizeFileName = false;

    private ?array $resize = null;
    private ?array $convert = null;
    private ?array $thumbnails = null;

    public function __construct(
        private readonly mixed $input,
        private readonly array $config,
        private readonly FilesystemFactory $filesystems,
        private readonly ImageEngine $engine,
    ) {
    }

    public function to(string $directory): self
    {
        $this->targetSubdir = trim($directory, " \t\n\r\0\x0B/");
        return $this;
    }

    public function useYearFolder(bool $on = true): self
    {
        $this->useYearFolder = $on;
        return $this;
    }

    public function useDateFolders(bool $on = true): self
    {
        $this->useDateFolders = $on;
        return $this;
    }

    public function randomizeFileName(bool $on = true): self
    {
        $this->randomizeFileName = $on;
        return $this;
    }

    public function resize(int $width, int $height, bool $keepAspectRatio = false, ?string $fillColor = null): self
    {
        $this->resize = [
            'width' => $width,
            'height' => $height,
            'keep_aspect' => $keepAspectRatio,
            'fill' => $fillColor,
        ];

        return $this;
    }

    public function convert(?string $format = null, ?int $quality = null, ?bool $override = null, ?bool $preserveOriginals = null): self
    {
        $this->convert = [
            'format' => $format ?? ($this->config['default_format'] ?? 'webp'),
            'quality' => $quality ?? (int) ($this->config['default_quality'] ?? 85),
            'override' => $override ?? (bool) ($this->config['auto_override'] ?? false),
            'preserve_originals' => $preserveOriginals ?? (bool) ($this->config['preserve_originals'] ?? true),
        ];

        return $this;
    }

    /**
     * @param array<int, array{0:int,1:int,2:string}> $specs
     */
    public function thumbnail(array $specs, ?bool $keepAspectRatio = null, ?bool $fromOriginal = null, ?string $fillColor = null): self
    {
        $this->thumbnails = [
            'specs' => $specs,
            'keep_aspect' => $keepAspectRatio ?? (bool) ($this->config['thumbnail_keep_aspect_ratio'] ?? true),
            'from_original' => $fromOriginal ?? (bool) ($this->config['thumbnail_from_original'] ?? false),
            'fill' => $fillColor,
        ];

        return $this;
    }

    /**
     * Execute processing and return the final URL (or storage path if url() unsupported).
     */
    public function run(): string
    {
        return $this->runInfo()->urlOrPath;
    }

    public function runInfo(): MediaForgeResult
    {
        $diskName = (string) ($this->config['disk'] ?? 'public');
        $baseDir = trim((string) ($this->config['base_dir'] ?? 'uploads'), " \t\n\r\0\x0B/");
        $driver = (string) ($this->config['driver'] ?? 'auto');
        $store = $this->store($diskName);

        $sourcePath = $this->sourcePath();
        $originalExt = $this->guessExtension($sourcePath);

        $baseName = $this->randomizeFileName
            ? Str::lower(Str::random(12))
            : pathinfo($sourcePath, PATHINFO_FILENAME);

        $dateSegment = null;
        if ($this->useDateFolders) {
            $dateSegment = now()->format('Y/m/d');
        } elseif ($this->useYearFolder) {
            $dateSegment = now()->format('Y');
        }

        $dirParts = array_filter([
            $this->storageBasePrefix(),
            $baseDir,
            $this->targetSubdir ?: null,
            $dateSegment,
        ]);
        $dir = implode('/', $dirParts);

        $manager = $this->engine->manager($driver);
        $originalImage = $manager->read($this->input);

        $working = clone $originalImage;

        if (($this->convert['preserve_originals'] ?? (bool) ($this->config['preserve_originals'] ?? true)) === true) {
            $originalOut = $this->joinPath($dir, "{$baseName}.{$originalExt}");
            $store->put(
                $this->maybeUniquePath($store, $originalOut, (bool) ($this->convert['override'] ?? (bool) ($this->config['auto_override'] ?? false))),
                $this->sourceBytes()
            );
        }

        if ($this->resize) {
            $working = $this->applyResize($working, $this->resize);
        }

        $finalExt = $originalExt;
        $finalQuality = (int) ($this->config['default_quality'] ?? 85);

        if ($this->convert) {
            $finalExt = $this->normalizeFormat((string) ($this->convert['format'] ?? $finalExt));
            $finalQuality = (int) ($this->convert['quality'] ?? $finalQuality);
        }

        // Naming rules (per README):
        // - Resize: {baseName}-{w}-{h}.{ext}
        // - Resize + Convert: {baseName}-{w}-{h}-{format}.{format}
        $finalName = $baseName;
        if ($this->resize) {
            $finalName .= '-' . $this->resize['width'] . '-' . $this->resize['height'];
        }
        if ($this->resize && $this->convert) {
            $finalName .= '-' . $finalExt;
        }

        $finalPath = $this->joinPath($dir, "{$finalName}.{$finalExt}");
        $finalPath = $this->maybeUniquePath($store, $finalPath, (bool) ($this->convert['override'] ?? (bool) ($this->config['auto_override'] ?? false)));

        $encoded = $this->encode($working, $finalExt, $finalQuality);
        $store->put($finalPath, (string) $encoded);

        if ($this->thumbnails) {
            $thumbSource = ($this->thumbnails['from_original'] ?? false) ? $originalImage : $working;
            $this->writeThumbnails(
                store: $store,
                dir: $dir,
                baseName: $finalName,
                ext: $finalExt,
                quality: $finalQuality,
                source: $thumbSource,
                options: $this->thumbnails,
            );
        }

        $urlOrPath = $store->urlOrPath($finalPath);
        $url = $this->looksLikeHttpUrl($urlOrPath) ? $urlOrPath : null;

        return new MediaForgeResult(
            disk: $diskName,
            path: $finalPath,
            url: $url,
            urlOrPath: $urlOrPath,
        );
    }

    private function looksLikeHttpUrl(string $value): bool
    {
        return (bool) preg_match('#^https?://#i', trim($value));
    }

    private function applyResize(ImageInterface $image, array $resize): ImageInterface
    {
        $w = (int) $resize['width'];
        $h = (int) $resize['height'];
        $keep = (bool) ($resize['keep_aspect'] ?? false);

        if (! $keep) {
            return $image->resize($w, $h);
        }

        $fill = (string) ($resize['fill'] ?? 'ffffff');
        $fill = ltrim($fill, '#');

        // contain() pads to exact size and can upscale (fits README expectation of exact output dims).
        return $image->contain($w, $h, $fill);
    }

    private function writeThumbnails(
        MediaStore $store,
        string $dir,
        string $baseName,
        string $ext,
        int $quality,
        ImageInterface $source,
        array $options,
    ): void {
        $keepAspect = (bool) ($options['keep_aspect'] ?? true);
        $fill = $options['fill'] ?? null;

        foreach (($options['specs'] ?? []) as $spec) {
            [$w, $h, $suffix] = $spec;

            $thumb = clone $source;

            if ($keepAspect) {
                $bg = (string) ($fill ?? 'ffffff');
                $bg = ltrim($bg, '#');
                $thumb = $thumb->contain((int) $w, (int) $h, $bg);
            } else {
                $thumb = $thumb->resize((int) $w, (int) $h);
            }

            $thumbPath = $this->joinPath($dir, "{$baseName}_{$suffix}.{$ext}");
            $thumbPath = $this->maybeUniquePath($store, $thumbPath, (bool) ($this->convert['override'] ?? (bool) ($this->config['auto_override'] ?? false)));
            $store->put($thumbPath, (string) $this->encode($thumb, $ext, $quality));
        }
    }

    private function encode(ImageInterface $image, string $format, int $quality): EncodedImage
    {
        $format = $this->normalizeFormat($format);

        return match ($format) {
            'webp' => $image->toWebp($quality),
            'jpg', 'jpeg' => $image->toJpeg($quality),
            'png' => $image->toPng(),
            'gif' => $image->toGif(),
            'avif' => $image->toAvif($quality),
            'heic' => $image->toHeic($quality),
            'tif', 'tiff' => $image->toTiff($quality),
            default => $image->toWebp($quality),
        };
    }

    private function normalizeFormat(string $format): string
    {
        $format = strtolower(trim($format));

        return match ($format) {
            'jpeg' => 'jpg',
            default => $format,
        };
    }

    private function sourcePath(): string
    {
        if ($this->input instanceof UploadedFile) {
            return $this->input->getRealPath() ?: $this->input->getPathname();
        }

        if (is_string($this->input)) {
            return $this->input;
        }

        if ($this->input instanceof \SplFileInfo) {
            return $this->input->getRealPath() ?: $this->input->getPathname();
        }

        // Best effort: string-cast (some supported inputs are binary strings etc.)
        return (string) $this->input;
    }

    private function sourceBytes(): string
    {
        if ($this->input instanceof UploadedFile) {
            return (string) file_get_contents($this->sourcePath());
        }

        if (is_string($this->input) && is_file($this->input)) {
            return (string) file_get_contents($this->input);
        }

        if (is_string($this->input)) {
            // Could be raw binary data / base64 / data uri; let Intervention handle decoding,
            // but for "preserve_originals" we can only safely store it as-is.
            return $this->input;
        }

        return (string) file_get_contents($this->sourcePath());
    }

    private function guessExtension(string $path): string
    {
        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        $ext = trim($ext, '.');

        if ($ext === '') {
            return 'jpg';
        }

        return $this->normalizeFormat($ext);
    }

    private function joinPath(string ...$parts): string
    {
        $parts = array_values(array_filter(array_map(
            fn ($p) => trim((string) $p, " \t\n\r\0\x0B/"),
            $parts
        ), fn ($p) => $p !== ''));

        return implode('/', $parts);
    }

    private function maybeUniquePath(MediaStore $store, string $path, bool $override): string
    {
        if ($override) {
            return $path;
        }

        if (! $store->exists($path)) {
            return $path;
        }

        $dir = pathinfo($path, PATHINFO_DIRNAME);
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        $attempt = 0;
        do {
            $attempt++;
            $candidate = $this->joinPath($dir, "{$filename}-" . Str::lower(Str::random(6)) . ".{$ext}");
        } while ($store->exists($candidate) && $attempt < 25);

        return $candidate;
    }

    private function storageRule(): string
    {
        return strtolower(trim((string) ($this->config['storage_rule'] ?? 'laravel')));
    }

    /**
     * Prefix under public webroot in legacy mode (e.g. 'media' or 'media-private').
     */
    private function storageBasePrefix(): ?string
    {
        if ($this->storageRule() !== 'vormia') {
            return null;
        }

        return trim((string) ($this->config['public_dir'] ?? 'media'), " \t\n\r\0\x0B/");
    }

    private function store(string $diskName): MediaStore
    {
        if ($this->storageRule() !== 'vormia') {
            return new LaravelDiskStore($this->filesystems, $diskName);
        }

        $publicRoot = public_path();
        $appUrl = (string) (config('app.url') ?? '');

        return new VormiaWebrootStore(new Filesystem(), $publicRoot, $appUrl);
    }
}

