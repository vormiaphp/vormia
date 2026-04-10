<?php

namespace Vormia\Vormia\Services\MediaForge;

use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;

class MediaPublicUrl
{
    public function __construct(
        private readonly FilesystemFactory $filesystems,
    ) {
    }

    public function forUrlOrPath(
        string $urlOrPath,
        string $disk,
        string $storageRule,
        bool $passthroughUrls = false,
    ): string {
        $urlOrPath = trim($urlOrPath);

        if ($urlOrPath === '') {
            return $urlOrPath;
        }

        $disk = strtolower(trim($disk));
        $storageRule = strtolower(trim($storageRule));

        if ($this->looksLikeUrl($urlOrPath)) {
            if ($passthroughUrls) {
                return $urlOrPath;
            }

            $key = $this->extractKeyFromS3StyleUrl($urlOrPath);
            if ($key === null) {
                return $urlOrPath;
            }

            return $this->buildFromPath($key, $disk, $storageRule);
        }

        return $this->buildFromPath($urlOrPath, $disk, $storageRule);
    }

    private function buildFromPath(string $path, string $disk, string $storageRule): string
    {
        $path = ltrim(trim($path), '/');

        if ($storageRule === 'vormia') {
            return asset($path);
        }

        $diskObj = $this->filesystems->disk($disk);

        try {
            $url = $diskObj->url($path);
        } catch (\Throwable) {
            return $path;
        }

        $url = trim((string) $url);
        if ($url === '') {
            return $path;
        }

        // If the disk returns a relative URL (common for local/public: '/storage/...'),
        // wrap it in asset() to make it absolute.
        if (str_starts_with($url, '/')) {
            return asset(ltrim($url, '/'));
        }

        return $url;
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

