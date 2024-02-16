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
    public ?int $size;
    public ?string $mimeType;
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

        $this->storagePathClean = $this->getStoragePathClean();
        $this->preparedPathForStorage = $this->preparePathForStorage();

        $this->exists = $this->getExists();

        $this->size = $this->getSize();
        $this->mimeType = $this->getMimeType();
        $this->lastModified = $this->getLastModified();

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
        return RiftStorageHelper::getStoragePathClean($this->path, $this->disk);
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

    private function getLastModified(): mixed
    {
        return RiftStorage::lastModified($this);
    }
}
