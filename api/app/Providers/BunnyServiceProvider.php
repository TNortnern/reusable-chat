<?php

namespace App\Providers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNAdapter;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNClient;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNRegion;

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
            // Create the BunnyCDN client
            $client = new BunnyCDNClient(
                $config['storage_zone'],
                $config['api_key'],
                $config['region'] ?? BunnyCDNRegion::FALKENSTEIN
            );

            // Create the adapter with the client and CDN URL
            $adapter = new BunnyCDNAdapter($client, $config['cdn_url'] ?? '');

            // Create the Flysystem filesystem
            $filesystem = new Filesystem($adapter, $config);

            // Wrap in Laravel's FilesystemAdapter for proper Storage facade integration
            return new FilesystemAdapter($filesystem, $adapter, $config);
        });
    }
}
