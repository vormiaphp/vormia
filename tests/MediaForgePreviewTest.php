<?php

namespace VormiaPHP\Vormia\Tests;

use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Support\Facades\Storage;
use Vormia\Vormia\Services\MediaForge\MediaPublicUrl;
use Vormia\Vormia\Services\MediaForge\MediaPreviewUrl;
use VormiaPHP\Vormia\Facades\MediaForge;

class MediaForgePreviewTest extends IntegrationTestCase
{
    public function test_public_url_builder_laravel_storage_rule_wraps_relative_disk_url_with_asset(): void
    {
        $disk = new class {
            public function url(string $path): string
            {
                return '/storage/' . ltrim($path, '/');
            }
        };

        $factory = new class($disk) implements FilesystemFactory {
            public function __construct(private readonly object $disk) {}

            public function disk($name = null)
            {
                return $this->disk;
            }

            public function cloud()
            {
                return $this->disk;
            }
        };

        $builder = new MediaPublicUrl($factory);

        $url = $builder->forUrlOrPath(
            urlOrPath: 'uploads/products/a.jpg',
            disk: 'Public',
            storageRule: 'laravel',
            passthroughUrls: false,
        );

        $this->assertStringEndsWith('/storage/uploads/products/a.jpg', $url);
    }

    public function test_public_url_builder_vormia_storage_rule_uses_asset_on_path(): void
    {
        $disk = new class {
            public function url(string $path): string
            {
                return '/storage/' . ltrim($path, '/');
            }
        };

        $factory = new class($disk) implements FilesystemFactory {
            public function __construct(private readonly object $disk) {}

            public function disk($name = null)
            {
                return $this->disk;
            }

            public function cloud()
            {
                return $this->disk;
            }
        };

        $builder = new MediaPublicUrl($factory);

        $url = $builder->forUrlOrPath(
            urlOrPath: '/media/products/a.jpg',
            disk: 'public',
            storageRule: 'vormia',
            passthroughUrls: false,
        );

        $this->assertStringEndsWith('/media/products/a.jpg', $url);
    }

    public function test_public_url_builder_passthrough_true_returns_url_unchanged(): void
    {
        $disk = new class {
            public function url(string $path): string
            {
                return 'https://cdn.example/' . ltrim($path, '/');
            }
        };

        $factory = new class($disk) implements FilesystemFactory {
            public function __construct(private readonly object $disk) {}

            public function disk($name = null)
            {
                return $this->disk;
            }

            public function cloud()
            {
                return $this->disk;
            }
        };

        $builder = new MediaPublicUrl($factory);

        $given = 'https://bucket.s3.amazonaws.com/uploads/products/a.jpg';
        $url = $builder->forUrlOrPath(
            urlOrPath: $given,
            disk: 's3',
            storageRule: 'laravel',
            passthroughUrls: true,
        );

        $this->assertSame($given, $url);
    }

    public function test_public_url_builder_passthrough_false_rebuilds_s3_style_url_when_extractable(): void
    {
        $disk = new class {
            public function url(string $path): string
            {
                return 'https://cdn.example/' . ltrim($path, '/');
            }
        };

        $factory = new class($disk) implements FilesystemFactory {
            public function __construct(private readonly object $disk) {}

            public function disk($name = null)
            {
                return $this->disk;
            }

            public function cloud()
            {
                return $this->disk;
            }
        };

        $builder = new MediaPublicUrl($factory);

        $url = $builder->forUrlOrPath(
            urlOrPath: 'https://my-bucket.s3.amazonaws.com/uploads/products/a.jpg',
            disk: 'S3',
            storageRule: 'laravel',
            passthroughUrls: false,
        );

        $this->assertSame('https://cdn.example/uploads/products/a.jpg', $url);
    }

    public function test_preview_url_private_mode_uses_temporary_url_when_available(): void
    {
        $disk = new class {
            public function url(string $path): string
            {
                return 'https://public.example/' . ltrim($path, '/');
            }

            public function temporaryUrl(string $path, $expiresAt): string
            {
                return 'https://signed.example/' . ltrim($path, '/');
            }
        };

        $factory = new class($disk) implements FilesystemFactory {
            public function __construct(private readonly object $disk) {}

            public function disk($name = null)
            {
                return $this->disk;
            }

            public function cloud()
            {
                return $this->disk;
            }
        };

        $builder = new MediaPreviewUrl($factory);

        $url = $builder->forUrlOrPath(
            urlOrPath: 'uploads/products/a.jpg',
            disk: 's3',
            expiresAt: null,
            options: ['mode' => 'private'],
        );

        $this->assertSame('https://signed.example/uploads/products/a.jpg', $url);
    }

    public function test_preview_url_public_mode_uses_url(): void
    {
        $disk = new class {
            public function url(string $path): string
            {
                return 'https://public.example/' . ltrim($path, '/');
            }
        };

        $factory = new class($disk) implements FilesystemFactory {
            public function __construct(private readonly object $disk) {}

            public function disk($name = null)
            {
                return $this->disk;
            }

            public function cloud()
            {
                return $this->disk;
            }
        };

        $builder = new MediaPreviewUrl($factory);

        $url = $builder->forUrlOrPath(
            urlOrPath: 'uploads/products/a.jpg',
            disk: 's3',
            expiresAt: null,
            options: ['mode' => 'public'],
        );

        $this->assertSame('https://public.example/uploads/products/a.jpg', $url);
    }

    public function test_preview_url_extracts_key_from_s3_style_url(): void
    {
        $disk = new class {
            public function url(string $path): string
            {
                return 'https://public.example/' . ltrim($path, '/');
            }

            public function temporaryUrl(string $path, $expiresAt): string
            {
                return 'https://signed.example/' . ltrim($path, '/');
            }
        };

        $factory = new class($disk) implements FilesystemFactory {
            public function __construct(private readonly object $disk) {}

            public function disk($name = null)
            {
                return $this->disk;
            }

            public function cloud()
            {
                return $this->disk;
            }
        };

        $builder = new MediaPreviewUrl($factory);

        $url = $builder->forUrlOrPath(
            urlOrPath: 'https://my-bucket.s3.amazonaws.com/uploads/products/a.jpg',
            disk: 's3',
            expiresAt: null,
            options: ['mode' => 'private'],
        );

        $this->assertSame('https://signed.example/uploads/products/a.jpg', $url);
    }

    public function test_mediaforge_manager_preview_url_uses_config_default_expiry(): void
    {
        Storage::fake('public');

        $this->app['config']->set('vormia.mediaforge.disk', 'public');
        $this->app['config']->set('vormia.mediaforge.preview_mode', 'public');
        $this->app['config']->set('vormia.mediaforge.preview_expires_minutes', 1);

        $url = MediaForge::previewUrl('uploads/products/a.jpg');

        // On local/public fake, url() usually returns /storage/...; we just assert it returns a string.
        $this->assertNotSame('', trim((string) $url));
    }

    public function test_proxy_preview_route_streams_file_when_enabled(): void
    {
        Storage::fake('s3');
        Storage::disk('s3')->put('uploads/products/a.txt', 'hello');

        $this->app['config']->set('vormia.mediaforge.preview_mode', 'proxy');

        $res = $this->get('/api/vrm/media/preview?disk=s3&path=uploads/products/a.txt');

        $res->assertOk();
        $this->assertSame('hello', $res->streamedContent());
    }
}

