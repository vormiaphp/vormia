<?php

namespace Vormia\Vormia\Services\MediaForge;

use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

final class MediaFileForgeJob
{
    private string $targetSubdir = '';
    private bool $useYearFolder = false;
    private bool $useDateFolders = false;
    private bool $randomizeFileName = false;

    public function __construct(
        private readonly mixed $input,
        private readonly array $config,
        private readonly FilesystemFactory $filesystems,
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

    /**
     * Store the original file and return the final URL (or storage path if url() unsupported).
     */
    public function run(): string
    {
        return $this->runInfo()->urlOrPath;
    }

    public function runInfo(): MediaForgeResult
    {
        $diskName = (string) ($this->config['disk'] ?? 'public');
        $baseDir = trim((string) ($this->config['base_dir'] ?? 'uploads'), " \t\n\r\0\x0B/");
        $override = (bool) ($this->config['auto_override'] ?? false);
        $store = $this->store($diskName);

        $sourcePath = $this->sourcePath();
        $ext = $this->guessExtension($sourcePath);

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

        $path = $this->joinPath($dir, "{$baseName}.{$ext}");
        $path = $this->maybeUniquePath($store, $path, $override);

        $store->put($path, $this->sourceBytes());

        $urlOrPath = $store->urlOrPath($path);
        $url = $this->looksLikeHttpUrl($urlOrPath) ? $urlOrPath : null;

        return new MediaForgeResult(
            disk: $diskName,
            path: $path,
            url: $url,
            urlOrPath: $urlOrPath,
        );
    }

    private function looksLikeHttpUrl(string $value): bool
    {
        return (bool) preg_match('#^https?://#i', trim($value));
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
            // Could be raw bytes/base64/data URI; caller is responsible for passing the intended bytes.
            return $this->input;
        }

        return (string) file_get_contents($this->sourcePath());
    }

    private function guessExtension(string $path): string
    {
        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        $ext = trim($ext, '.');

        // For generic file uploads we prefer a neutral extension when unknown.
        return $ext !== '' ? $ext : 'bin';
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

