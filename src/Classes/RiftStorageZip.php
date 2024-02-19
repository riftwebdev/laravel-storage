<?php

namespace Riftweb\Storage\Classes;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Riftweb\Storage\Helpers\RiftStorageHelper;
use Riftweb\Storage\Objects\FilePath;
use ZipArchive;
use Throwable;
use InvalidArgumentException;

class RiftStorageZip
{
    public static function zipFiles(FilePath $zipFilePath, Collection $filePaths): ?FilePath
    {
        try {
            $zip = new ZipArchive();
            $zip->open($zipFilePath->fullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            foreach ($filePaths as $filePath) {
                if (!$filePath instanceof FilePath) {
                    throw new InvalidArgumentException('All elements in the collection must be instances of FilePath');
                }

                $zip->addFile($filePath->storagePathClean, $filePath->fileName);
            }

            $zip->close();

            return FilePath::create([
                    'path' => $zipFilePath->path,
                    'fileName' => $zipFilePath->fileName,
                    'disk' => $filePath->disk
                ]);
        } catch (Throwable $e) {
            report($e);
        }

        return null;
    }

    public static function deleteTemporaryZips(): bool
    {
        try {
            $files = RiftStorage::files(RiftStorageHelper::ZIP_TEMP_DIR);

            if ($files->isEmpty()) {
                return true;
            }

            foreach ($files as $file) {
                $file->delete();
            }
        } catch (Throwable $e) {
            report($e);
        }

        return true;
    }
}
