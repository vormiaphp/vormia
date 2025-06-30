<?php

namespace App\Helpers\Vrm;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Services\Vrm\MediaForgeService;

class MediaForgeUpload
{
    protected array $files = [];
    protected string $targetFolder = '';
    protected array $operations = [];
    protected MediaForgeService $service;

    public function __construct(MediaForgeService $service)
    {
        $this->service = $service;
    }

    public function upload(array $files): self
    {
        $this->files = $files;
        return $this;
    }

    public function fromUrl(string $url): self
    {
        $tempPath = storage_path('app/temp_' . Str::random(10));
        $content = Http::get($url)->body();
        file_put_contents($tempPath, $content);
        $name = basename(parse_url($url, PHP_URL_PATH));
        $uploaded = new UploadedFile($tempPath, $name);
        $this->files[] = $uploaded;
        return $this;
    }

    public function to(string $folder): self
    {
        $this->targetFolder = $folder;
        return $this;
    }

    public function onDisk(string $disk): self
    {
        $this->service->setDisk($disk);
        return $this;
    }

    public function visibility(string $visibility): self
    {
        $this->service->setVisibility($visibility);
        return $this;
    }

    public function resize(int $width, int $height): self
    {
        $this->operations[] = ['operation' => 'resize', 'width' => $width, 'height' => $height];
        return $this;
    }

    public function convert(string $format, int $quality = 90, bool $progressive = true): self
    {
        $this->operations[] = [
            'operation' => 'convert',
            'format' => $format,
            'quality' => $quality,
            'progressive' => $progressive,
        ];
        return $this;
    }

    public function compress(string $format, int $quality = 80, bool $overwrite = true): self
    {
        $this->operations[] = [
            'operation' => 'compress',
            'format' => $format,
            'quality' => $quality,
            'overwrite' => $overwrite,
        ];
        return $this;
    }

    public function watermark(string $imagePath, string $position = 'bottom-right', int $x = 10, int $y = 10, int $opacity = 25): self
    {
        $this->operations[] = [
            'operation' => 'watermark',
            'path' => $imagePath,
            'position' => $position,
            'x' => $x,
            'y' => $y,
            'opacity' => $opacity,
        ];
        return $this;
    }

    public function thumbnail(int $width = 200, int $height = 200, string $suffix = '_thumb'): self
    {
        $this->operations[] = [
            'operation' => 'thumbnail',
            'width' => $width,
            'height' => $height,
            'suffix' => $suffix,
        ];
        return $this;
    }

    public function avatar(int $size = 300, bool $rounded = true, string $suffix = '_avatar'): self
    {
        $this->operations[] = [
            'operation' => 'avatar',
            'size' => $size,
            'rounded' => $rounded,
            'suffix' => $suffix,
        ];
        return $this;
    }

    public function run(): array
    {
        $this->service->setFolder($this->targetFolder);
        $this->service->setOperations($this->operations);
        return $this->service->handle($this->files);
    }
}
