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

    public function url(string $urlOrPath, ?string $disk = null): MediaUrlBuilder
    {
        $cfg = (array) $this->config->get('vormia.mediaforge', []);

        $disk = $disk ?? (string) ($cfg['disk'] ?? 'public');
        $disk = strtolower(trim($disk));

        return new MediaUrlBuilder(
            urlOrPath: $urlOrPath,
            disk: $disk,
            cfg: $cfg,
            filesystems: $this->filesystems,
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

    public function uploadFile(mixed $input): MediaFileForgeJob
    {
        $cfg = (array) $this->config->get('vormia.mediaforge', []);

        return new MediaFileForgeJob(
            input: $input,
            config: $cfg,
            filesystems: $this->filesystems,
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
        $disk = strtolower(trim((string) $disk));

        if ($expiresAt === null) {
            $minutes = (int) ($cfg['preview_expires_minutes'] ?? 10);
            $expiresAt = now()->addMinutes(max(1, $minutes));
        }

        // Compatibility wrapper: prefer builder behavior.
        // - If caller explicitly provided mode/public/private flags, keep honoring them.
        // - Otherwise, default to "private" because preview URLs are expected to work for private buckets.
        $mode = strtolower(trim((string) ($options['mode'] ?? 'private')));
        $forcePrivate = $mode === 'private' || (($options['private'] ?? false) === true) || (($options['force_sign'] ?? false) === true);

        $builder = $this->url($urlOrPath, $disk);
        if ($forcePrivate) {
            $builder->private();
        } else {
            $builder->public();
        }

        return $builder->expiresAt($expiresAt)->toString();
    }
}

