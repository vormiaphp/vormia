<?php

namespace VormiaPHP\Vormia\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use VormiaPHP\Vormia\Facades\MediaForge;
use Vormia\Vormia\Services\MediaForge\MediaForgeManager;

class MediaForgeTest extends IntegrationTestCase
{
    public function test_container_binding_resolves_manager(): void
    {
        $resolved = $this->app->make('vrm.mediaforge');
        $this->assertInstanceOf(MediaForgeManager::class, $resolved);
    }

    public function test_facade_runs_and_writes_files_to_public_disk(): void
    {
        Storage::fake('public');

        $this->app['config']->set('vormia.mediaforge.disk', 'public');
        $this->app['config']->set('vormia.mediaforge.base_dir', 'uploads');
        $this->app['config']->set('vormia.mediaforge.driver', 'gd');
        $this->app['config']->set('vormia.mediaforge.default_format', 'webp');
        $this->app['config']->set('vormia.mediaforge.default_quality', 80);
        $this->app['config']->set('vormia.mediaforge.auto_override', true);
        $this->app['config']->set('vormia.mediaforge.preserve_originals', true);

        $tmp = sys_get_temp_dir() . '/vormia-mediaforge-' . uniqid('', true) . '.png';
        $encoded = ImageManager::gd()->create(32, 32)->fill('ff0000')->toPng();
        file_put_contents($tmp, (string) $encoded);

        $file = new UploadedFile($tmp, 'example.png', 'image/png', null, true);

        $url = MediaForge::upload($file)
            ->randomizeFileName(true)
            ->to('products')
            ->resize(64, 64, true, '#ffffff')
            ->convert('webp', 75, true, true)
            ->thumbnail([[20, 20, 'thumb']], true, false, '#ffffff')
            ->run();

        $this->assertNotSame('', (string) $url);

        $files = Storage::disk('public')->allFiles('uploads/products');
        $this->assertNotEmpty($files, 'Expected MediaForge to write files into uploads/products on public disk');
    }
}

