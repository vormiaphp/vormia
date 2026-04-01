<?php

namespace Vormia\Vormia\Services\MediaForge;

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
}

