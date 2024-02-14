<?php

namespace Riftweb\Storage;

use Illuminate\Support\ServiceProvider;
use Riftweb\Storage\Classes\RiftStorage;

class RiftStorageProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(RiftStorage::class, function ($app) {
            return new RiftStorage();
        });
    }
}