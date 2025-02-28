<?php

namespace Tests\Unit;

use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository;
use Lbausch\BuildMetadataLaravel\ServiceProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(ServiceProvider::class)]
#[CoversClass(\Lbausch\BuildMetadataLaravel\BuildMetadataManager::class)]
#[CoversClass(\Lbausch\BuildMetadataLaravel\Events\CachingBuildMetadata::class)]
final class ServiceProviderTest extends TestCase
{
    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array<int, string>
     */
    #[\Override]
    protected function getPackageProviders($app)
    {
        // Disable automatic loading of service provider
        return [];
    }

    public function test_service_provider_boots(): void
    {
        $cacheManager = $this->app->make(CacheManager::class);
        $config = $this->app->make(Repository::class);

        $service_provider = new ServiceProvider($this->app);

        $service_provider->boot($cacheManager, $config);

        $this->assertFalse($cacheManager->store(config('build-metadata.cache.store'))->has(config('build-metadata.cache.key')));
    }
}
