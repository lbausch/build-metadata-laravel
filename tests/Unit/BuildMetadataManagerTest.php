<?php

namespace Tests\Unit;

use Lbausch\BuildMetadataLaravel\BuildMetadataManager;
use Lbausch\BuildMetadataLaravel\Metadata;
use Tests\TestCase;

final class BuildMetadataManagerTest extends TestCase
{
    /**
     * @covers \LBausch\BuildMetadataLaravel\ServiceProvider::register
     * @covers \Lbausch\BuildMetadataLaravel\ServiceProvider::boot
     * @covers \Lbausch\BuildMetadataLaravel\Console\Commands\SaveBuildMetadata::__construct
     * @covers \Lbausch\BuildMetadataLaravel\Console\Commands\SaveBuildMetadata::handle
     * @covers \Lbausch\BuildMetadataLaravel\BuildMetadataManager::__construct
     * @covers \Lbausch\BuildMetadataLaravel\BuildMetadataManager::beforeCaching
     * @covers \Lbausch\BuildMetadataLaravel\BuildMetadataManager::cache
     * @covers \Lbausch\BuildMetadataLaravel\Events\CachingBuildMetadata::__construct
     * @covers \Lbausch\BuildMetadataLaravel\Metadata::fromJson
     * @covers \Lbausch\BuildMetadataLaravel\Metadata::__construct
     * @covers \Lbausch\BuildMetadataLaravel\Metadata::get
     * @covers \Lbausch\BuildMetadataLaravel\Metadata::set
     * @covers \Lbausch\BuildMetadataLaravel\BuildMetadataManager::cached
     * @covers \Lbausch\BuildMetadataLaravel\Events\CachedBuildMetadata::__construct
     * @covers \Lbausch\BuildMetadataLaravel\BuildMetadataManager::getMetadata
     */
    public function test_callback_is_called(): void
    {
        $this->artisan('buildmetadata:save', ['metadata' => 'FOO=bar']);

        $callback_was_called = false;

        BuildMetadataManager::beforeCaching(function (Metadata $metadata) use (&$callback_was_called): Metadata {
            $callback_was_called = true;

            $metadata->set('FOO', 'bar');

            return $metadata;
        });

        $manager = $this->app->make(BuildMetadataManager::class);

        $metadata = $manager->getMetadata();

        $this->assertTrue($callback_was_called);

        $this->assertSame('bar', $metadata->get('FOO'));
    }
}
