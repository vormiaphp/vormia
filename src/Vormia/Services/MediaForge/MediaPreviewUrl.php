<?php

namespace Vormia\Vormia\Services\MediaForge;

use DateTimeInterface;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;

class MediaPreviewUrl
{
    public function __construct(
        private readonly FilesystemFactory $filesystems,
    ) {
    }

    /**
     * Build a browser-previewable URL from a MediaForge `run()` result.
     *
     * @param array{
     *   mode?: 'auto'|'public'|'private',
     *   private?: bool,
     *   force_sign?: bool,
     * } $options
     */
    public function forUrlOrPath(
        string $urlOrPath,
        string $disk,
        ?DateTimeInterface $expiresAt = null,
        array $options = [],
    ): string {
        $urlOrPath = trim($urlOrPath);

        if ($urlOrPath === '') {
            return $urlOrPath;
        }

        $mode = $this->normalizeMode($options);
        $diskObj = $this->filesystems->disk($disk);

        // If it's already a URL we can't sign (data:, http(s) not S3-style), return as-is.
        // This matches the plan's "non-S3 URL passthrough" behavior.
        if ($this->looksLikeUrl($urlOrPath)) {
            $key = $this->extractKeyFromS3StyleUrl($urlOrPath);
            if ($key === null) {
                return $urlOrPath;
            }

            return $this->buildFromPath($diskObj, $key, $expiresAt, $mode);
        }

        // Otherwise treat as a disk path/key.
        return $this->buildFromPath($diskObj, $urlOrPath, $expiresAt, $mode);
    }

    private function buildFromPath(
        object $disk,
        string $path,
        ?DateTimeInterface $expiresAt,
        string $mode,
    ): string {
        $path = ltrim($path, '/');

        if ($mode === 'private') {
            $signed = $this->tryTemporaryUrl($disk, $path, $expiresAt);
            if ($signed !== null) {
                return $signed;
            }
        }

        // public/auto fallback: try url(), else return raw path
        try {
            return $disk->url($path);
        } catch (\Throwable) {
            return $path;
        }
    }

    private function tryTemporaryUrl(
        object $disk,
        string $path,
        ?DateTimeInterface $expiresAt,
    ): ?string {
        // `temporaryUrl` exists on Illuminate\Filesystem\FilesystemAdapter and some implementations.
        if (! method_exists($disk, 'temporaryUrl')) {
            return null;
        }

        try {
            /** @var callable $call */
            $call = [$disk, 'temporaryUrl'];
            return $call($path, $expiresAt ?? now()->addMinutes(10));
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param array{mode?:string, private?:bool, force_sign?:bool} $options
     */
    private function normalizeMode(array $options): string
    {
        if (($options['force_sign'] ?? false) === true) {
            return 'private';
        }

        $mode = strtolower(trim((string) ($options['mode'] ?? 'auto')));
        if ($mode === 'private' || $mode === 'public') {
            return $mode;
        }

        if (($options['private'] ?? false) === true) {
            return 'private';
        }

        return 'auto';
    }

    private function looksLikeUrl(string $value): bool
    {
        if (str_starts_with($value, 'data:')) {
            return true;
        }

        return (bool) preg_match('#^https?://#i', $value);
    }

    /**
     * Best-effort extraction for common S3-style URLs.
     *
     * Supports:
     * - https://bucket.s3.amazonaws.com/key
     * - https://bucket.s3.<region>.amazonaws.com/key
     * - https://s3.amazonaws.com/bucket/key
     * - https://s3.<region>.amazonaws.com/bucket/key
     */
    private function extractKeyFromS3StyleUrl(string $url): ?string
    {
        $parts = parse_url($url);
        if (! is_array($parts)) {
            return null;
        }

        $host = (string) ($parts['host'] ?? '');
        $path = (string) ($parts['path'] ?? '');

        if ($host === '' || $path === '') {
            return null;
        }

        $path = ltrim($path, '/');

        // Virtual-hosted–style: bucket.s3.amazonaws.com/key
        if (preg_match('#^([a-z0-9.-]+)\\.s3[.-][a-z0-9-]*\\.amazonaws\\.com$#i', $host)) {
            return $path === '' ? null : $path;
        }
        if (preg_match('#^([a-z0-9.-]+)\\.s3\\.amazonaws\\.com$#i', $host)) {
            return $path === '' ? null : $path;
        }

        // Path-style: s3.amazonaws.com/bucket/key or s3.<region>.amazonaws.com/bucket/key
        if (preg_match('#^s3[.-][a-z0-9-]*\\.amazonaws\\.com$#i', $host) || preg_match('#^s3\\.amazonaws\\.com$#i', $host)) {
            $segments = explode('/', $path, 2);
            if (count($segments) !== 2) {
                return null;
            }
            [, $key] = $segments;
            return $key !== '' ? $key : null;
        }

        return null;
    }
}

