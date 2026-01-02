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
            // Map region code to BunnyCDNRegion constant
            $region = $this->mapRegion($config['region'] ?? 'de');

            // Create the BunnyCDN client
            $client = new BunnyCDNClient(
                $config['storage_zone'],
                $config['api_key'],
                $region
            );

            // Create the adapter with the client and CDN URL
            $adapter = new BunnyCDNAdapter($client, $config['cdn_url'] ?? '');

            // Create the Flysystem filesystem
            $filesystem = new Filesystem($adapter, $config);

            // Wrap in Laravel's FilesystemAdapter for proper Storage facade integration
            return new FilesystemAdapter($filesystem, $adapter, $config);
        });
    }

    /**
     * Map region code to BunnyCDNRegion constant.
     */
    protected function mapRegion(string $region): string
    {
        return match (strtolower($region)) {
            'de', 'falkenstein' => BunnyCDNRegion::FALKENSTEIN,
            'ny', 'new_york', 'new-york' => BunnyCDNRegion::NEW_YORK,
            'la', 'los_angeles', 'los-angeles' => BunnyCDNRegion::LOS_ANGELES,
            'sg', 'singapore' => BunnyCDNRegion::SINGAPORE,
            'syd', 'sydney' => BunnyCDNRegion::SYDNEY,
            'uk', 'london' => BunnyCDNRegion::UNITED_KINGDOM,
            'se', 'stockholm' => BunnyCDNRegion::STOCKHOLM,
            'br', 'sao_paulo', 'sao-paulo' => BunnyCDNRegion::SAO_PAULO,
            'jh', 'johannesburg' => BunnyCDNRegion::JOHANNESBURG,
            default => BunnyCDNRegion::FALKENSTEIN,
        };
    }
}
