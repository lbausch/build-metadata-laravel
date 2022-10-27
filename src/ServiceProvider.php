<?php

namespace Lbausch\BuildMetadataLaravel;

use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../config/build-metadata.php', 'build-metadata'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(CacheManager $cacheManager, Repository $config)
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/build-metadata.php' => config_path('build-metadata.php'),
        ]);

        // Bind singleton
        $this->app->singleton(BuildMetadataManager::class, function ($app) use ($cacheManager, $config) {
            // Obtain a cache store instance
            $cache = $cacheManager->store(
                $config->get('build-metadata.cache.store')
            );

            return new BuildMetadataManager($cache, $config);
        });
    }
}
