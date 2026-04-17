<?php

namespace Vormia\Vormia\Services\MediaForge;

use DateTimeInterface;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;

final class MediaUrlBuilder
{
    private string $mode = 'public';

    private ?DateTimeInterface $expiresAt = null;

    public function __construct(
        private readonly string $urlOrPath,
        private readonly string $disk,
        private readonly array $cfg,
        private readonly FilesystemFactory $filesystems,
    ) {
    }

    public function public(): self
    {
        $this->mode = 'public';
        return $this;
    }

    public function private(): self
    {
        $this->mode = 'private';
        return $this;
    }

    /**
     * Alias for toggling signed/private URLs.
     */
    public function preview(bool $on = true): self
    {
        return $on ? $this->private() : $this->public();
    }

    public function expiresAt(DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function seconds(int $seconds): self
    {
        $this->expiresAt = now()->addSeconds(max(1, $seconds));
        return $this;
    }

    public function minutes(int $minutes): self
    {
        $this->expiresAt = now()->addMinutes(max(1, $minutes));
        return $this;
    }

    public function hours(int $hours): self
    {
        $this->expiresAt = now()->addHours(max(1, $hours));
        return $this;
    }

    public function days(int $days): self
    {
        $this->expiresAt = now()->addDays(max(1, $days));
        return $this;
    }

    public function years(int $years): self
    {
        $this->expiresAt = now()->addYears(max(1, $years));
        return $this;
    }

    public function toString(): string
    {
        $storageRule = (string) ($this->cfg['storage_rule'] ?? 'laravel');
        $passthrough = (bool) ($this->cfg['url_passthrough'] ?? false);

        if ($this->mode === 'private') {
            $expiresAt = $this->expiresAt ?? $this->defaultExpiresAt();

            return (new MediaPreviewUrl($this->filesystems))->forUrlOrPath(
                urlOrPath: $this->urlOrPath,
                disk: $this->disk,
                expiresAt: $expiresAt,
                options: [
                    'mode' => 'private',
                ],
            );
        }

        return (new MediaPublicUrl($this->filesystems))->forUrlOrPath(
            urlOrPath: $this->urlOrPath,
            disk: $this->disk,
            storageRule: $storageRule,
            passthroughUrls: $passthrough,
        );
    }

    public function __toString(): string
    {
        try {
            return $this->toString();
        } catch (\Throwable) {
            return '';
        }
    }

    private function defaultExpiresAt(): DateTimeInterface
    {
        $raw = $this->cfg['preview_period_seconds'] ?? null;

        // Missing key: default 24h.
        if ($raw === null) {
            return now()->addDay();
        }

        // Present but empty / invalid: default 1h.
        $seconds = (int) $raw;
        if ($seconds <= 0) {
            return now()->addHour();
        }

        return now()->addSeconds($seconds);
    }
}

