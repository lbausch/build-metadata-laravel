<?php

namespace Lbausch\BuildMetadataLaravel;

use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Config\Repository as ConfigRepository;
use Lbausch\BuildMetadataLaravel\Events\CachedBuildMetadata;
use Lbausch\BuildMetadataLaravel\Events\CachingBuildMetadata;

class BuildMetadataManager
{
    /**
     * Callback which is executed before metadata are indefinitely cached.
     *
     * @var callable
     */
    protected static $beforeCachingCallback;

    /**
     * Cache key.
     */
    protected string $cache_key;

    /**
     * Whether build metadata are cached.
     */
    protected static bool $cached = false;

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

        // Avoid re-caching build metadata
        if (!$this->cached()) {
            $this->cache();
        }
    }

    /**
     * Get build metadata.
     */
    public function getMetadata(): Metadata
    {
        return $this->cache->get($this->cache_key, new Metadata());
    }

    /**
     * Determine whether build metadata are cached.
     */
    public function cached(): bool
    {
        if (static::$cached) {
            return true;
        }

        if ($this->cache->has($this->cache_key)) {
            static::$cached = true;
        }

        return static::$cached;
    }

    /**
     * Register a callback which is executed before build metadata are indefinitely cached.
     */
    public static function beforeCaching(callable $callback): void
    {
        static::$beforeCachingCallback = $callback;
    }

    /**
     * Cache build metadata.
     *
     * @throws \ErrorException
     */
    protected function cache(): void
    {
        // Verify a cache key is configured
        if (!$this->cache_key) {
            throw new \ErrorException('Invalid cache key "'.$this->cache_key.'" provided');
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
            throw new \ErrorException('Failed to read build metadata from '.$file);
        }

        // Try to parse JSON
        $metadata = Metadata::fromJson($metadata_raw);

        // Execute callback
        if (is_callable(static::$beforeCachingCallback)) {
            $metadata = call_user_func_array(static::$beforeCachingCallback, [$metadata]);

            if (!$metadata instanceof Metadata) {
                throw new \ErrorException('beforeCaching callback did not return an instance of '.Metadata::class);
            }
        }

        // Cache build metadata forever
        $this->cache->forever($this->cache_key, $metadata);

        static::$cached = true;

        // Dispatch an event after caching build metadata
        CachedBuildMetadata::dispatch($metadata);
    }

    /**
     * Forget cached build metadata.
     *
     * @throws \ErrorException
     */
    public function forget(): void
    {
        // Verify a cache key is configured
        if (!$this->cache_key) {
            throw new \ErrorException('Invalid cache key "'.$this->cache_key.'" provided');
        }

        // Forget build metadata
        $this->cache->forget($this->cache_key);
    }
}
