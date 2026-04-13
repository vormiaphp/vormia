<?php

namespace VormiaPHP\Vormia\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
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

    public function test_use_date_folders_writes_to_yyyy_mm_dd(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 10, 12, 0, 0, 'UTC'));
        Storage::fake('public');

        $this->app['config']->set('vormia.mediaforge.storage_rule', 'laravel');
        $this->app['config']->set('vormia.mediaforge.disk', 'public');
        $this->app['config']->set('vormia.mediaforge.base_dir', 'uploads');
        $this->app['config']->set('vormia.mediaforge.driver', 'gd');
        $this->app['config']->set('vormia.mediaforge.auto_override', true);
        $this->app['config']->set('vormia.mediaforge.preserve_originals', false);

        $tmp = sys_get_temp_dir() . '/vormia-mediaforge-' . uniqid('', true) . '.png';
        $encoded = ImageManager::gd()->create(16, 16)->fill('00ff00')->toPng();
        file_put_contents($tmp, (string) $encoded);
        $file = new UploadedFile($tmp, 'example.png', 'image/png', null, true);

        MediaForge::upload($file)
            ->useDateFolders(true)
            ->to('products')
            ->run();

        $files = Storage::disk('public')->allFiles('uploads/products/2026/04/10');
        $this->assertNotEmpty($files, 'Expected MediaForge to write files into uploads/products/YYYY/MM/DD on public disk');

        Carbon::setTestNow();
    }

    public function test_upload_file_stores_pdf_without_image_processing(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 10, 12, 0, 0, 'UTC'));
        Storage::fake('public');

        $this->app['config']->set('vormia.mediaforge.storage_rule', 'laravel');
        $this->app['config']->set('vormia.mediaforge.disk', 'public');
        $this->app['config']->set('vormia.mediaforge.base_dir', 'uploads');
        $this->app['config']->set('vormia.mediaforge.auto_override', true);

        $tmp = sys_get_temp_dir() . '/vormia-mediaforge-' . uniqid('', true) . '.pdf';
        file_put_contents($tmp, "%PDF-1.4\n% Vormia test\n");

        $file = new UploadedFile($tmp, 'example.pdf', 'application/pdf', null, true);

        $urlOrPath = MediaForge::uploadFile($file)
            ->useYearFolder(true)
            ->randomizeFileName(true)
            ->to('docs')
            ->run();

        $this->assertNotSame('', trim((string) $urlOrPath));

        $files = Storage::disk('public')->allFiles('uploads/docs/2026');
        $this->assertNotEmpty($files, 'Expected MediaForge::uploadFile() to write into uploads/docs/YYYY on public disk');

        Carbon::setTestNow();
    }

    public function test_upload_is_file_stores_pdf_without_image_processing(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 10, 12, 0, 0, 'UTC'));
        Storage::fake('public');

        $this->app['config']->set('vormia.mediaforge.storage_rule', 'laravel');
        $this->app['config']->set('vormia.mediaforge.disk', 'public');
        $this->app['config']->set('vormia.mediaforge.base_dir', 'uploads');
        $this->app['config']->set('vormia.mediaforge.auto_override', true);

        $tmp = sys_get_temp_dir() . '/vormia-mediaforge-' . uniqid('', true) . '.pdf';
        file_put_contents($tmp, "%PDF-1.4\n% Vormia test\n");

        $file = new UploadedFile($tmp, 'example.pdf', 'application/pdf', null, true);

        $urlOrPath = MediaForge::upload($file)
            ->isFile()
            ->useYearFolder(true)
            ->randomizeFileName(true)
            ->to('docs')
            ->run();

        $this->assertNotSame('', trim((string) $urlOrPath));

        $files = Storage::disk('public')->allFiles('uploads/docs/2026');
        $this->assertNotEmpty($files, 'Expected MediaForge::upload(...)->isFile() to write into uploads/docs/YYYY on public disk');

        Carbon::setTestNow();
    }

    public function test_upload_is_file_treats_png_as_raw_file_keeps_extension(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 10, 12, 0, 0, 'UTC'));
        Storage::fake('public');

        $this->app['config']->set('vormia.mediaforge.storage_rule', 'laravel');
        $this->app['config']->set('vormia.mediaforge.disk', 'public');
        $this->app['config']->set('vormia.mediaforge.base_dir', 'uploads');
        $this->app['config']->set('vormia.mediaforge.auto_override', true);

        $tmp = sys_get_temp_dir() . '/vormia-mediaforge-' . uniqid('', true) . '.png';
        $encoded = ImageManager::gd()->create(10, 10)->fill('00ff00')->toPng();
        file_put_contents($tmp, (string) $encoded);

        $file = new UploadedFile($tmp, 'example.png', 'image/png', null, true);

        MediaForge::upload($file)
            ->isFile()
            ->useYearFolder(true)
            ->randomizeFileName(true)
            ->to('raw-images')
            ->run();

        $files = Storage::disk('public')->allFiles('uploads/raw-images/2026');
        $this->assertNotEmpty($files);

        $hasPng = false;
        foreach ($files as $path) {
            if (str_ends_with(strtolower($path), '.png')) {
                $hasPng = true;
                break;
            }
        }
        $this->assertTrue($hasPng, 'Expected raw upload to keep .png extension when isFile() is used');

        Carbon::setTestNow();
    }

    public function test_storage_rule_vormia_writes_to_public_webroot(): void
    {
        $tmpPublic = sys_get_temp_dir() . '/vormia-public-' . uniqid('', true);
        mkdir($tmpPublic, 0755, true);
        $this->app->usePublicPath($tmpPublic);

        $this->app['config']->set('app.url', 'http://localhost');
        $this->app['config']->set('vormia.mediaforge.storage_rule', 'vormia');
        $this->app['config']->set('vormia.mediaforge.public_dir', 'media');
        $this->app['config']->set('vormia.mediaforge.base_dir', 'uploads');
        $this->app['config']->set('vormia.mediaforge.driver', 'gd');
        $this->app['config']->set('vormia.mediaforge.auto_override', true);
        $this->app['config']->set('vormia.mediaforge.preserve_originals', false);

        $tmp = sys_get_temp_dir() . '/vormia-mediaforge-' . uniqid('', true) . '.png';
        $encoded = ImageManager::gd()->create(8, 8)->fill('0000ff')->toPng();
        file_put_contents($tmp, (string) $encoded);
        $file = new UploadedFile($tmp, 'example.png', 'image/png', null, true);

        $url = MediaForge::upload($file)
            ->to('products')
            ->run();

        $this->assertStringContainsString('http://localhost/', $url);

        $expectedDir = $tmpPublic . '/media/uploads/products';
        $this->assertDirectoryExists($expectedDir);

        $files = glob($expectedDir . '/*');
        $this->assertNotEmpty($files, 'Expected MediaForge to write files into public/media/uploads/products in legacy mode');
    }
}

