<?php

namespace Tests;

use Lbausch\BuildMetadataLaravel\ServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * File which metadata are written to.
     */
    public static string $build_metadata_file = 'build-metadata.json';

    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array<int, string>
     */
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function defineEnvironment($app)
    {
        $app['config']->set('build-metadata.cache.key', 'BUILD_METADATA');
        $app['config']->set('build-metadata.file', static::$build_metadata_file);
    }

    /**
     * Clean up the testing environment before the next test.
     */
    #[\Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        if (file_exists(static::$build_metadata_file)) {
            unlink(static::$build_metadata_file);
        }
    }
}
