<?php

namespace Riftweb\Storage\Classes;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Enums\ImageDriver;
use Spatie\Image\Image;
use Throwable;

class RiftStorage
{
    public static function store(
        ?UploadedFile $requestFile,
                      $path,
                      $disk = 'public',
                      $shouldResize = false,
                      $width = 900,
                      $height = 900
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
            return null;
        }
    }

    private static function getStoragePathClean($path, $disk = 'public'): ?string
    {
        try {
            $storageFileUrl = Storage::disk($disk)->url($path);
            return str_replace(config('app.url'), '', $storageFileUrl);
        } catch (Throwable $e) {
            report($e);
            return null;
        }
    }

    public static function resizeImage($path, $width = 900, $height = 900): bool
    {
        try {
            Image::useImageDriver(ImageDriver::Gd)
                ->loadFile($path)
                ->fit(Fit::Contain, $width, $height)
                ->save();

            return true;
        } catch (Throwable $e) {
            report($e);
            return false;
        }
    }

    public static function storeRaw($content, $extension, $path, $filename = null, $disk = 'public'): ?string
    {
        try {
            if (is_null($content)) {
                return null;
            }

            if (is_null($filename)) {
                $filename = str()->uuid()->toString();
            }

            $fullPath = $path . '/' . "$filename.$extension";

            if (!str($fullPath)->startsWith('/')) {
                $fullPath = "/$fullPath";
            }

            if (Storage::disk($disk)->put($fullPath, $content)) {
                return self::getStoragePathClean($fullPath, $disk);
            }
        } catch (Throwable $e) {
            report($e);
        }

        return null;
    }

    public function delete($path, $disk = 'public'): bool
    {
        try {
            if (is_null($path)) {
                return false;
            }


            return Storage::disk($disk)->delete($path);
        } catch (Throwable $e) {
            report($e);
            return false;
        }
    }
}
