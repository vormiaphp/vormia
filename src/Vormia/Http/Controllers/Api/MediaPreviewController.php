<?php

namespace Vormia\Vormia\Http\Controllers\Api;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Storage;

class MediaPreviewController
{
    public function show(Request $request): Response
    {
        $mode = (string) (config('vormia.mediaforge.preview_mode') ?? 'auto');
        if (strtolower($mode) !== 'proxy') {
            abort(404);
        }

        $disk = (string) ($request->query('disk') ?? (string) (config('vormia.mediaforge.disk') ?? 'public'));
        $path = (string) ($request->query('path') ?? '');
        $path = ltrim($path, '/');

        if ($path === '' || str_contains($path, '..')) {
            abort(400, 'Invalid path');
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $fs */
        $fs = Storage::disk($disk);

        try {
            if (! $fs->exists($path)) {
                abort(404);
            }
        } catch (\Throwable) {
            // Some disks may not support exists reliably; fall through and try stream.
        }

        try {
            $stream = $fs->readStream($path);
        } catch (\Throwable) {
            $stream = false;
        }

        if (! is_resource($stream)) {
            abort(404);
        }

        $mime = null;
        if (method_exists($fs, 'mimeType')) {
            try {
                $mime = $fs->mimeType($path);
            } catch (\Throwable) {
                $mime = null;
            }
        }

        return response()->stream(
            function () use ($stream) {
                fpassthru($stream);
                fclose($stream);
            },
            200,
            array_filter([
                'Content-Type' => $mime ?: 'application/octet-stream',
                'Content-Disposition' => 'inline',
                'X-Content-Type-Options' => 'nosniff',
            ], fn ($v) => $v !== null && $v !== '')
        );
    }
}

