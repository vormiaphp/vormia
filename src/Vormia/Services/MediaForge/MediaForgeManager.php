<?php

namespace Vormia\Vormia\Services\MediaForge;

use DateTimeInterface;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;

class MediaForgeManager
{
    public function __construct(
        private readonly ConfigRepository $config,
        private readonly FilesystemFactory $filesystems,
        private readonly ImageEngine $engine = new ImageEngine(),
    ) {
    }

    public function url(string $urlOrPath, ?string $disk = null): string
    {
        $cfg = (array) $this->config->get('vormia.mediaforge', []);

        $disk = $disk ?? (string) ($cfg['disk'] ?? 'public');
        $disk = strtolower(trim($disk));

        $storageRule = (string) ($cfg['storage_rule'] ?? 'laravel');
        $passthrough = (bool) ($cfg['url_passthrough'] ?? false);

        return (new MediaPublicUrl($this->filesystems))->forUrlOrPath(
            urlOrPath: $urlOrPath,
            disk: $disk,
            storageRule: $storageRule,
            passthroughUrls: $passthrough,
        );
    }

    public function upload(mixed $input): MediaForgeJob
    {
        $cfg = (array) $this->config->get('vormia.mediaforge', []);

        return new MediaForgeJob(
            input: $input,
            config: $cfg,
            filesystems: $this->filesystems,
            engine: $this->engine,
        );
    }

    /**
     * Build a browser-previewable URL from a MediaForge `run()` return value.
     *
     * If the underlying disk supports `temporaryUrl()`, this can generate an expiring signed URL
     * for private buckets. Otherwise it falls back to `url()` or returns the raw path.
     *
     * @param array{
     *   mode?: 'auto'|'public'|'private',
     *   private?: bool,
     *   force_sign?: bool,
     * } $options
     */
    public function previewUrl(
        string $urlOrPath,
        ?string $disk = null,
        ?DateTimeInterface $expiresAt = null,
        array $options = [],
    ): string {
        $cfg = (array) $this->config->get('vormia.mediaforge', []);

        $disk = $disk ?? (string) ($cfg['disk'] ?? 'public');
        $mode = (string) ($options['mode'] ?? ($cfg['preview_mode'] ?? 'auto'));
        $options['mode'] = $mode;

        if ($expiresAt === null) {
            $minutes = (int) ($cfg['preview_expires_minutes'] ?? 10);
            $expiresAt = now()->addMinutes(max(1, $minutes));
        }

        return (new MediaPreviewUrl($this->filesystems))->forUrlOrPath(
            urlOrPath: $urlOrPath,
            disk: $disk,
            expiresAt: $expiresAt,
            options: $options,
        );
    }
}

