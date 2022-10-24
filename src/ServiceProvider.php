<?php

namespace Lbausch\BuildMetadataLaravel;

use ErrorException;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use InvalidArgumentException;
use Lbausch\BuildMetadataLaravel\Events\CachedBuildMetadata;
use Lbausch\BuildMetadataLaravel\Events\CachingBuildMetadata;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Cache repository.
     */
    protected Repository $cache;

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/build-metadata.php', 'build-metadata'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(CacheManager $cacheManager)
    {
        $this->publishes([
            __DIR__.'/../config/build-metadata.php' => config_path('build-metadata.php'),
        ]);

        // Obtain a cache store instance
        $this->cache = $cacheManager->store(config('build-metadata.cache.store'));

        // Cache build metadata
        $this->cacheBuildMetadata();
    }

    /**
     * Cache build metadata.
     *
     * @throws ErrorException
     * @throws FileNotFoundException
     * @throws InvalidArgumentException
     */
    protected function cacheBuildMetadata(): void
    {
        // Verify a cache key is configured
        $cache_key = trim((string) config('build-metadata.cache.key'));

        if (!$cache_key) {
            throw new InvalidArgumentException('Invalid cache key "'.$cache_key.'" provided');
        }

        // Avoid re-caching build metadata
        if ($this->cache->has($cache_key)) {
            CachedBuildMetadata::dispatch($this->cache->get($cache_key));

            return;
        }

        // Dispatch an event before caching build metadata
        CachingBuildMetadata::dispatch();

        // Get configured build metadata file
        $file = config('build-metadata.file');

        // Verify build metadata file exists
        if (!file_exists($file)) {
            throw new FileNotFoundException('File containing build metadata "'.$file.'" not found');
        }

        // Verify build metadata file is readable
        if (!is_readable($file)) {
            throw new FileNotFoundException('File containing build metadata "'.$file.'" is unreadable');
        }

        // Read build metadata
        $metadata_raw = file_get_contents($file);

        // Verify build metadata were read correctly
        if (false === $metadata_raw) {
            throw new ErrorException('Failed to read build metadata from '.$file);
        }

        // Try to parse JSON
        $metadata = json_decode($metadata_raw, $associative = true, 512, JSON_THROW_ON_ERROR);

        // Cache build metadata forever
        $this->cache->forever($cache_key, $metadata);

        // Dispatch an event after caching build metadata
        CachedBuildMetadata::dispatch($metadata);
    }
}
