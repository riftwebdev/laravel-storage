<?php

namespace Riftweb\Storage\Objects;

use Illuminate\Support\Carbon;
use Riftweb\Storage\Classes\RiftStorage;
use Riftweb\Storage\Helpers\RiftStorageHelper;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FilePath
{
    public string $storagePathClean;
    public string $realFileName;
    public string $preparedPathForStorage;
    public ?string $fullPath;
    public ?string $directory;
    public ?int $size;
    public ?string $mimeType;
    public ?string $extension;
    public ?Carbon $lastModified;
    public bool $exists;

    public function __construct(
        public string $path,
        public ?string $fileName = null,
        public string $disk = 'public'
    )
    {
        $this->realFileName = $this->realFileName();

        if (is_null($this->fileName)) {
            $this->fileName = $this->realFileName;
        }

        $this->preparedPathForStorage = $this->preparePathForStorage();
        $this->storagePathClean = $this->getStoragePathClean();
        $this->fullPath = $this->getFullPath();

        $this->exists = $this->getExists();

        $this->size = $this->getSize();
        $this->mimeType = $this->getMimeType();
        $this->extension = $this->getFileExtension();
        $this->lastModified = $this->getLastModified();

        $this->fixNameWithDifferentExtension();

        $this->directory = str($this->path)->beforeLast('/');
    }

    public static function create(array $array): FilePath
    {
        return new self(
            $array['path'],
                $array['fileName'] ?? null,
                $array['disk'] ?? 'public'
        );
    }

    public function realFileName(): string
    {
        return str($this->path)->afterLast('/');
    }

    public function download(): ?StreamedResponse
    {
        return RiftStorage::download($this);
    }

    public function delete(): bool
    {
        return RiftStorage::delete($this);
    }

    private function getExists(): bool
    {
        return RiftStorage::exists($this);
    }

    private function getStoragePathClean(): string
    {
        return RiftStorageHelper::getStoragePathClean($this->preparedPathForStorage, $this->disk);
    }

    private function preparePathForStorage(): string
    {
        return RiftStorageHelper::preparePathForStorage($this->path);
    }

    private function getSize(): mixed
    {
        return RiftStorage::size($this);
    }

    private function getMimeType(): mixed
    {
        return RiftStorage::mimeType($this);
    }

    private function getFileExtension(): ?string
    {
        return RiftStorage::fileExtension($this);
    }

    private function fixNameWithDifferentExtension(): void
    {
        if (!is_null($this->extension) && !str($this->fileName)->endsWith('.' . $this->extension)) {
            $this->fileName .= '.' . $this->extension;
        }
    }

    private function getLastModified(): mixed
    {
        return RiftStorage::lastModified($this);
    }

    private function getFullPath(): string
    {
        return RiftStorageHelper::getFullPath($this->path, $this->disk);
    }
}
