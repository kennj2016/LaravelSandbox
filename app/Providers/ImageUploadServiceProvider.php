<?php

namespace App\Providers;

use App\Services\ImageUploadService;
use Illuminate\Support\ServiceProvider;

/**
 * Class ImageUploadServiceProvider
 * @package App\Providers
 */
class ImageUploadServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind('ImageService', function () {
            return new ImageUploadService();
        });
    }
}
