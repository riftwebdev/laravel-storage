<?php

namespace Riftweb\Storage\Classes;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Enums\ImageDriver;
use Spatie\Image\Image;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;


class RiftStorage
{
    public static function store(
        ?UploadedFile $requestFile,
        string        $path = '',
        string        $disk = 'public',
        bool          $shouldResize = false,
        int           $width = 900,
        int           $height = 900
    ): ?string
    {
        try {
            if (is_null($requestFile)) {
                return null;
            }

            $path = $requestFile->store(
                path: $path,
                options: ['disk' => $disk]
            );

            $storageFileUrlClean = self::getStoragePathClean($path, $disk);

            if ($shouldResize) {
                self::resizeImage(public_path($storageFileUrlClean), $width, $height);
            }

            return $storageFileUrlClean;

        } catch (Throwable $e) {
            report($e);
        }

        return null;
    }

    private static function getStoragePathClean($path, $disk = 'public'): ?string
    {
        try {
            $storageFileUrl = Storage::disk($disk)->url($path);

            return str_replace(self::getDomain(), '', $storageFileUrl);
        } catch (Throwable $e) {
            report($e);
        }

        return null;
    }

    private static function getDomain(): string
    {
        $domain = config('app.url');
        if (!str($domain)->endsWith('/')) {
            $domain .= '/';
        }

        return $domain;
    }

    public static function resizeImage(
        string $path,
        int    $width = 900,
        int    $height = 900
    ): bool
    {
        try {
            $a = Image::useImageDriver(ImageDriver::Gd)
                ->loadFile($path)
                ->fit(Fit::Contain, $width, $height)
                ->save($path);

            return true;
        } catch (Throwable $e) {
            report($e);
            return false;
        }
    }

    public static function storeRaw(
        mixed   $content,
        string  $extension,
        string  $path = '',
        ?string $filename = null,
        string  $disk = 'public'
    ): ?string
    {
        try {
            if (is_null($content)) {
                return null;
            }

            if (is_null($filename)) {
                $filename = str()->uuid()->toString();
            }

            $fullPath = (
                str($path)->isNotEmpty()
                    ? $path . '/'
                    : ''
                ) . "$filename.$extension";

            if (str($fullPath)->startsWith('/')) {
                $fullPath = str($fullPath)->replaceFirst('/', '');
            }

            if (Storage::disk($disk)->put($fullPath, $content)) {
                return self::getStoragePathClean($fullPath, $disk);
            }
        } catch (Throwable $e) {
            report($e);
        }

        return null;
    }

    public static function delete(string $path, string $disk = 'public'): bool
    {
        try {
            $path = self::preparePathForStorage($path);

            if (!self::exists($path)) {
                return true;
            }

            return Storage::disk($disk)->delete($path);
        } catch (Throwable $e) {
            report($e);
        }

        return false;
    }

    public static function download(
        string $path,
        ?string $fileName = null,
        string $disk = 'public'
    ): ?StreamedResponse
    {
        try {

            $path = self::preparePathForStorage($path);

            if (!self::exists($path)) {
                throw new Exception("File not found",404);
            }

            return Storage::disk($disk)
                ->download(
                    $path,
                    $fileName ?? str()->uuid()->tostring(),
                );

        } catch (Throwable $e) {
            report($e);
        }

        return null;
    }

    private static function preparePathForStorage(string $path): ?string
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

    public static function exists(string $path, string $disk = 'public'): bool
    {
        try {
            return Storage::disk($disk)->exists($path);
        } catch (Throwable $e) {
            report($e);
        }

        return false;
    }

}
