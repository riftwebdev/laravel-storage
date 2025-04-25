<?php

namespace Riftweb\Storage\Objects;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

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
        string $path,
        ?string $fileName = null,
        string $disk = 'public'
    ) {
        if (empty($path)) {
            throw new InvalidArgumentException('Path cannot be empty');
        }

        $this->path = $path;
        $this->fileName = $fileName;
        $this->disk = $disk;
        
        $this->realFileName = $this->realFileName();
        $this->fileName ??= $this->realFileName;
        
        $this->directory = Str::beforeLast($path, '/');
        $this->preparedPathForStorage = $this->preparePathForStorage();
        $this->storagePathClean = $this->getStoragePathClean();
        $this->fullPath = $this->getFullPath();
        $this->exists = $this->getExists();
        
        $this->updateFileProperties();
        $this->fixNameWithDifferentExtension();
    }

    private function updateFileProperties(): void
    {
        if (!$this->exists) {
            return;
        }

        $disk = Storage::disk($this->disk);
        
        $this->size = $disk->size($this->preparedPathForStorage);
        $this->mimeType = $disk->mimeType($this->preparedPathForStorage);
        $this->extension = pathinfo($this->preparedPathForStorage, PATHINFO_EXTENSION);
        $this->lastModified = Carbon::createFromTimestamp($disk->lastModified($this->preparedPathForStorage));
    }

    public static function create(array $attributes): self
    {
        return new self(
            $attributes['path'] ?? '',
            $attributes['fileName'] ?? null,
            $attributes['disk'] ?? 'public'
        );
    }

    private function realFileName(): string
    {
        return pathinfo($this->path, PATHINFO_FILENAME);
    }

    private function preparePathForStorage(): string
    {
        return Str::replaceFirst(['storage/', '/storage/'], '', $this->path);
    }

    private function getStoragePathClean(): ?string
    {
        try {
            return Str::replaceFirst(config('app.url'), '', Storage::disk($this->disk)->url($this->path));
        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    private function getFullPath(): ?string
    {
        try {
            return Storage::disk($this->disk)->path($this->preparedPathForStorage);
        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    private function getExists(): bool
    {
        try {
            return Storage::disk($this->disk)->exists($this->preparedPathForStorage);
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    private function getSize(): ?int
    {
        return $this->exists ? Storage::disk($this->disk)->size($this->preparedPathForStorage) : null;
    }

    private function getMimeType(): ?string
    {
        return $this->exists ? Storage::disk($this->disk)->mimeType($this->preparedPathForStorage) : null;
    }

    private function getFileExtension(): ?string
    {
        return $this->exists ? pathinfo($this->preparedPathForStorage, PATHINFO_EXTENSION) : null;
    }

    private function getLastModified(): ?Carbon
    {
        return $this->exists ? 
            Carbon::createFromTimestamp(Storage::disk($this->disk)->lastModified($this->preparedPathForStorage)) : 
            null;
    }

    private function fixNameWithDifferentExtension(): void
    {
        if (!$this->exists || empty($this->fileName)) {
            return;
        }

        $fileExtension = pathinfo($this->fileName, PATHINFO_EXTENSION);
        $pathExtension = pathinfo($this->preparedPathForStorage, PATHINFO_EXTENSION);

        if ($fileExtension !== $pathExtension) {
            $this->fileName = pathinfo($this->fileName, PATHINFO_FILENAME) . '.' . $pathExtension;
        }
    }
}
