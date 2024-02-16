<?php

namespace Riftweb\Storage\Helpers;

use Illuminate\Support\Facades\Storage;

class RiftStorageHelper
{
    public const ZIP_TEMP_DIR = 'temp-zip-files';

    public static function getDomain(): string
    {
        $domain = config('app.url');
        if (!str($domain)->endsWith('/')) {
            $domain .= '/';
        }

        return $domain;
    }

    public static function preparePathForStorage(string $path): ?string
    {
        $startsWithStorageSlash = str($path)->startsWith('storage/');
        $startsWithSlashStorageSlash = str($path)->startsWith('/storage/');
        if (str($path)->isEmpty() || (!$startsWithSlashStorageSlash && !$startsWithStorageSlash)) {
            return $path;
        }

        if ($startsWithSlashStorageSlash) {
            return str($path)->replaceFirst('/storage/', '');
        }

        return str($path)->replaceFirst('storage/', '');
    }

    public static function getStoragePathClean($path, $disk = 'public'): ?string
    {
        try {
            $storageFileUrl = Storage::disk($disk)->url($path);

            return str_replace(RiftStorageHelper::getDomain(), '', $storageFileUrl);
        } catch (Throwable $e) {
            report($e);
        }

        return null;
    }
}
