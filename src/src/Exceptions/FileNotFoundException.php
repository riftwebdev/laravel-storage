<?php

namespace Riftweb\Storage\Exceptions;

use Exception;
use Riftweb\Storage\Objects\FilePath;

class FileNotFoundException extends Exception
{
    public function __construct(FilePath $filePath)
    {
        parent::__construct("File [$filePath->path] not found on disk [$filePath->disk].", 404);
    }
}
