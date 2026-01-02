<?php

namespace App\Providers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNAdapter;

class BunnyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Storage::extend('bunny', function ($app, $config) {
            $adapter = new BunnyCDNAdapter(
                $config['storage_zone'],
                $config['api_key'],
                $config['hostname'] ?? 'storage.bunnycdn.com'
            );

            return new Filesystem($adapter, [
                'url' => $config['cdn_url'] . '/' . ($config['path'] ?? ''),
            ]);
        });
    }
}
