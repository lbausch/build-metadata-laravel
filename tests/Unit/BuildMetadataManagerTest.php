<?php

namespace Tests\Unit;

use Lbausch\BuildMetadataLaravel\BuildMetadataManager;
use Lbausch\BuildMetadataLaravel\Metadata;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(BuildMetadataManager::class)]
#[CoversClass(Metadata::class)]
#[CoversClass(\LBausch\BuildMetadataLaravel\ServiceProvider::class)]
#[CoversClass(\Lbausch\BuildMetadataLaravel\Console\Commands\SaveBuildMetadata::class)]
#[CoversClass(\Lbausch\BuildMetadataLaravel\Console\Commands\ClearBuildMetadata::class)]
#[CoversClass(\Lbausch\BuildMetadataLaravel\Console\Commands\SaveBuildMetadata::class)]
#[CoversClass(\Lbausch\BuildMetadataLaravel\Events\CachingBuildMetadata::class)]
#[CoversClass(\Lbausch\BuildMetadataLaravel\Events\CachedBuildMetadata::class)]
final class BuildMetadataManagerTest extends TestCase
{
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
