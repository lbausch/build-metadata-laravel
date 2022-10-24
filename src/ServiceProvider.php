<?php

namespace Lbausch\BuildMetadataLaravel;

use Illuminate\Cache\CacheManager;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use InvalidArgumentException;

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
        $this->cacheBuildMetadata(config('build-metadata.file'));
    }

    /**
     * Cache build metadata.
     *
     * @throws InvalidArgumentException
     * @throws FileNotFoundException
     */
    protected function cacheBuildMetadata(string $file): void
    {
        // Verify a cache key is configured
        $cache_key = trim((string) config('build-metadata.cache.key'));

        if (!$cache_key) {
            throw new InvalidArgumentException('Invalid cache key "'.$cache_key.'" provided');
        }

        // Avoid re-caching metadata
        if ($this->cache->has($cache_key)) {
            return;
        }

        // Verify build metadata file exists
        if (!file_exists($file)) {
            throw new FileNotFoundException('File containing build metadata "'.$file.'" not found');
        }

        // Read build metadata
        $metadata_raw = file_get_contents($file);

        // Try to parse JSON
        $metadata = json_decode($metadata_raw, $associative = true, 512, JSON_THROW_ON_ERROR);

        // Cache build metadata forever
        $this->cache->forever($cache_key, $metadata);
    }
}
