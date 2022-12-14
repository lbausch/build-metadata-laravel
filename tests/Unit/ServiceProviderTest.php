<?php

namespace Tests\Unit;

use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository;
use Lbausch\BuildMetadataLaravel\ServiceProvider;
use Tests\TestCase;

final class ServiceProviderTest extends TestCase
{
    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array<int, string>
     */
    protected function getPackageProviders($app)
    {
        // Disable automatic loading of service provider
        return [];
    }

    /**
     * @covers \LBausch\BuildMetadataLaravel\ServiceProvider::boot
     * @covers \LBausch\BuildMetadataLaravel\ServiceProvider::register
     * @covers \Lbausch\BuildMetadataLaravel\BuildMetadataManager::cache
     * @covers \Lbausch\BuildMetadataLaravel\Events\CachingBuildMetadata::__construct
     */
    public function test_service_provider_boots(): void
    {
        $cacheManager = $this->app->make(CacheManager::class);
        $config = $this->app->make(Repository::class);

        $service_provider = new ServiceProvider($this->app);

        $service_provider->boot($cacheManager, $config);

        $this->assertFalse($cacheManager->store(config('build-metadata.cache.store'))->has(config('build-metadata.cache.key')));
    }
}
