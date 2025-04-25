<?php

namespace Riftweb\Storage\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class RiftStorageHelper
{
    public const ZIP_TEMP_DIR = 'temp-zip-files';

    public static function getDomain(): string
    {
        $domain = config('app.url');
        return Str::ensureLeft($domain, '/');
    }

    public static function preparePathForStorage(string $path): ?string
    {
        if (empty($path)) {
            return $path;
        }

        if (Str::startsWith($path, ['storage/', '/storage/'])) {
            return Str::replaceFirst(['storage/', '/storage/'], '', $path);
        }

        return $path;
    }

    public static function getStoragePathClean(string $path, string $disk = 'public'): ?string
    {
        try {
            $storageFileUrl = Storage::disk($disk)->url($path);
            return Str::replaceFirst(config('app.url'), '', $storageFileUrl);
        } catch (Throwable $e) {
            report($e);
            return null;
        }
    }

    public static function getFullPath(string $path, string $disk = 'public'): string
    {
        try {
            return Storage::disk($disk)->path(Str::replaceFirst('storage/', '', $path));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
