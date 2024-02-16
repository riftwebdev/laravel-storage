# riftweb/storage
Laravel package that simplifies storage services. It provides methods for storing files, retrieving paths, resizing images, and more. Developed by Leandro Santos, it uses the Spatie Image library for image manipulation and is compatible with PHP 7.3 and 8.0.

## About Us
[RIFT | Web Development](https://riftweb.com) is a software development company that provides custom solutions for businesses. We specialize in web and mobile applications, e-commerce, and digital marketing. Our team is composed of experienced professionals who are passionate about technology and innovation. We are committed to delivering high-quality products that meet our clients' needs and exceed their expectations.

## Installation
```bash
composer require riftweb/storage
```

## Usage
### Storing Files
```php
use Riftweb\Storage\Classes\RiftStorage;

$file = $request->file('file');
$path = 'path/to/store';
$disk = 'public';
$shouldResize = true;
$width = 900;
$height = 900;

$storedFilePath = RiftStorage::store($file, $path, $disk, $shouldResize, $width, $height);
```

### Storing Raw Content
```php
use Riftweb\Storage\Classes\RiftStorage;

$content = 'Raw content';
$extension = 'txt';
$path = 'path/to/store';
$filename = 'file';
$disk = 'public';

$storedFilePath = RiftStorage::storeRaw($content, $extension, $path, $filename, $disk);
```

###  Resizing Images
```php
use Riftweb\Storage\Classes\RiftStorage;

$path = 'path/to/file';
$disk = 'public';
$width = 900;
$height = 900;

$storedFilePath = RiftStorage::resizeImage($path, $disk, $width, $height);
```

### Check if File Exists
```php
use Riftweb\Storage\Classes\RiftStorage;

$path = 'path/to/file';
$disk = 'public';

$exists = RiftStorage::exists($path, $disk);
```

###  Deleting Files
```php
use Riftweb\Storage\Classes\RiftStorage;

$path = 'path/to/file';
$disk = 'public';

$deleteSuccess = RiftStorage::delete($path, $disk);
```

### Download File
```php
use Riftweb\Storage\Classes\RiftStorage;

$path = 'path/to/file';
$fileName = 'file';
$disk = 'public';

$response = RiftStorage::download($path, $fileName, $disk);
```

###  Getting File URL
```php
use Riftweb\Storage\Classes\RiftStorage;

$path = 'path/to/file';
$disk = 'public';

$url = RiftStorage::getUrl($path, $disk);
```

### Getting File Temporary URL
```php
use Riftweb\Storage\Classes\RiftStorage;

$path = 'path/to/file';
$disk = 'public';
$expiration = 60;

$url = RiftStorage::getTemporaryUrl($path, $disk, $expiration);
```

###  Getting File Size
```php
use Riftweb\Storage\Classes\RiftStorage;

$path = 'path/to/file';
$disk = 'public';

$size = RiftStorage::getSize($path, $disk);
```

###  Getting File Mime Type
```php
use Riftweb\Storage\Classes\RiftStorage;

$path = 'path/to/file';
$disk = 'public';

$mime = RiftStorage::getMimeType($path, $disk);
```

###  Getting File Extension
```php
use Riftweb\Storage\Classes\RiftStorage;

$path = 'path/to/file';
$disk = 'public';

$extension = RiftStorage::getExtension($path, $disk);
```

###  Getting File Last Modified Date
```php
use Riftweb\Storage\Classes\RiftStorage;

$path = 'path/to/file';
$disk = 'public';

$lastModified = RiftStorage::getLastModified($path, $disk);
```

## License
The Riftweb Storage package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
```