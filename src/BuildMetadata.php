<?php

namespace Lbausch\BuildMetadataLaravel;

use ErrorException;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Lbausch\BuildMetadataLaravel\Events\CachedBuildMetadata;
use Lbausch\BuildMetadataLaravel\Events\CachingBuildMetadata;

class BuildMetadata
{
    /**
     * Cache key.
     */
    protected string $cache_key;

    public function __construct(
        /**
         * Cache repository.
         */
        protected CacheRepository $cache,

        /**
         * Config repository.
         */
        protected ConfigRepository $config,
    ) {
        $this->cache_key = trim((string) $this->config->get('build-metadata.cache.key'));

        $this->cache();
    }

    /**
     * Get build metadata.
     */
    public function get(string $key = null, mixed $default = null): mixed
    {
        $data = $this->cache->get($this->cache_key, []);

        if (null === $key) {
            return $data;
        }

        return Arr::get($data, $key, $default);
    }

    /**
     * Determine if an item exists in the build metadata.
     */
    public function has(string $key): bool
    {
        $data = $this->get();

        return Arr::has($data, $key);
    }

    /**
     * Cache build metadata.
     *
     * @throws ErrorException
     * @throws InvalidArgumentException
     */
    public function cache(): void
    {
        // Verify a cache key is configured
        $cache_key = trim((string) $this->config->get('build-metadata.cache.key'));

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
        $file = $this->config->get('build-metadata.file');

        // Verify build metadata file exists and is readable
        if (!file_exists($file) || !is_readable($file)) {
            // Return if the file does not exist
            return;
        }

        // Read build metadata
        $metadata_raw = file_get_contents($file);

        // Verify build metadata were read
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
