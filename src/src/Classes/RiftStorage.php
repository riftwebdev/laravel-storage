<?php

namespace Riftweb\Storage\Classes;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Riftweb\Storage\Exceptions\FileNotFoundException;
use Riftweb\Storage\Helpers\RiftStorageHelper;
use Riftweb\Storage\Objects\FilePath;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Enums\ImageDriver;
use Spatie\Image\Image;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use ZipArchive;
use InvalidArgumentException;

class RiftStorage
{
    public static function store(
        ?UploadedFile $requestFile,
        string        $path = '',
        string        $disk = 'public',
        bool          $shouldResize = false,
        int           $width = 900,
        int           $height = 900
    ): ?FilePath
    {
        try {
            if (is_null($requestFile)) {
                return null;
            }

            $path = $requestFile->store(
                path: $path,
                options: ['disk' => $disk]
            );

            $storageFileUrlClean = RiftStorageHelper::getStoragePathClean($path, $disk);

            $filePath = new FilePath($storageFileUrlClean, disk: null);

            if ($shouldResize) {
                self::resizeImage($filePath, $width, $height);
            }

            return $storageFileUrlClean;

        } catch (Throwable $e) {
            report($e);
        }

        return null;
    }



    public static function resizeImage(
        FilePath $filePath,
        int    $width = 900,
        int    $height = 900
    ): bool
    {
        try {
            $publicPath = public_path($filePath->path);

            return Image::useImageDriver(ImageDriver::Gd)
                ->loadFile($publicPath)
                ->fit(Fit::Contain, $width, $height)
                ->save($publicPath);
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
    ): ?FilePath
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
                return new FilePath(
                    RiftStorageHelper::getStoragePathClean($fullPath, $disk),
                    disk: $disk
                );
            }
        } catch (Throwable $e) {
            report($e);
        }

        return null;
    }

    public static function delete(FilePath $filePath): bool
    {
        try {
            if (!$filePath->exists) {
                return true;
            }

            return Storage::disk($filePath->disk)
                ->delete($filePath->preparedPathForStorage);
        } catch (Throwable $e) {
            report($e);
        }

        return false;
    }

    public static function download(
        FilePath $filePath
    ): ?StreamedResponse
    {
        try {
            if (!$filePath->exists) {
                throw new FileNotFoundException($filePath);
            }

            return Storage::disk($filePath->disk)
                ->download(
                    $filePath->preparedPathForStorage,
                    $filePath->fileName ?? str()->uuid()->tostring(),
                );
        } catch (Throwable $e) {
            report($e);
        }

        return null;
    }

    public static function exists(FilePath $filePath): bool
    {
        try {
            return Storage::disk($filePath->disk)->exists($filePath->preparedPathForStorage);
        } catch (Throwable $e) {
            report($e);
        }

        return false;
    }

    public static function downloadMultiple(Collection $filePaths): ?StreamedResponse
    {
        try {
            $zipFilePath = RiftStorageZip::zipFiles($filePaths);

            if (is_null($zipFilePath)) {
                return null;
            }

            return self::download($zipFilePath);
        } catch (Throwable $e) {
            report($e);
        }

        return null;
    }

    public static function files($directory = '/', $disk = 'public', $recursive = false): Collection
    {
        try {
            $storage = Storage::disk($disk);

            if ($recursive) {
                $files = $storage->allFiles($directory);
            } else {
                $files = $storage->files($directory);
            }

            return collect($files)->transform(function ($file) use ($disk) {
                return FilePath::create([
                    'path' => $file,
                    'disk' => $disk
                ]);
            });
        } catch (Throwable $e) {
            report($e);
        }

        return collect();
    }

    public static function directories($directory = '/', $disk = 'public', $recursive = false): Collection
    {
        try {
            $storage = Storage::disk($disk);
            if ($recursive) {
                return collect($storage->allDirectories($directory));
            }

            return collect($storage->directories($directory));

        } catch (Throwable $e) {
            report($e);
        }

        return collect();
    }

    public static function size(FilePath $filePath): ?int
    {
        try {
            if ($filePath->exists) {
                return Storage::disk($filePath->disk)->size($filePath->preparedPathForStorage);
            }

        } catch (Throwable $e) {
            report($e);
        }

        return null;
    }

    public static function mimeType(FilePath $filePath): ?string {
        try {
            if ($filePath->exists) {
                return Storage::disk($filePath->disk)->mimeType($filePath->preparedPathForStorage);
            }
        } catch (Throwable $e) {
            report($e);
        }

        return null;
    }

    public static function lastModified(FilePath $filePath): ?Carbon {
        try {
            if ($filePath->exists) {
                return Carbon::createFromTimestamp(Storage::disk($filePath->disk)->lastModified($filePath->preparedPathForStorage));
            }
        } catch (Throwable $e) {
            report($e);
        }

        return null;
    }
}
