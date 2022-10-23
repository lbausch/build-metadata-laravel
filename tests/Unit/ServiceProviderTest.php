<?php

namespace Tests\Unit;

use Illuminate\Cache\Repository;
use Lbausch\BuildMetadataLaravel\ServiceProvider;
use Tests\TestCase;

final class ConfigTest extends TestCase
{
    /**
     * @covers \LBausch\BuildMetadataLaravel\ServiceProvider::boot
     * @covers \LBausch\BuildMetadataLaravel\ServiceProvider::register
     */
    public function test_service_provider_boots(): void
    {
        $cache = $this->app->make(Repository::class);

        $this->assertFalse($cache->has(ServiceProvider::BUILD_REF_CACHE_KEY));
        $this->assertFalse($cache->has(ServiceProvider::BUILD_DATE_CACHE_KEY));
    }
}
