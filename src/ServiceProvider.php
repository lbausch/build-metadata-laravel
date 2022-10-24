<?php

namespace Lbausch\BuildMetadataLaravel;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Cache\Repository;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    final public const BUILD_REF_FILE = 'BUILD_REF';
    final public const BUILD_REF_CACHE_KEY = 'BUILD_REF';

    final public const BUILD_DATE_FILE = 'BUILD_REF';
    final public const BUILD_DATE_CACHE_KEY = 'BUILD_REF';

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(Repository $cache)
    {
        // Cache build reference
        if (!$cache->has(static::BUILD_REF_CACHE_KEY) && file_exists(base_path(static::BUILD_REF_FILE))) {
            $build_ref = trim(file_get_contents(base_path(static::BUILD_REF_FILE)));

            $cache->forever(static::BUILD_REF_CACHE_KEY, $build_ref);
        }

        // Cache build date
        if (!$cache->has(static::BUILD_DATE_CACHE_KEY) && file_exists(base_path(static::BUILD_DATE_FILE))) {
            $build_date = trim(file_get_contents(base_path(static::BUILD_DATE_FILE)));

            try {
                $build_date = Carbon::createFromTimestamp($build_date, 'UTC');
            } catch (InvalidFormatException) {
                $build_date = null;
            }

            $cache->forever(static::BUILD_DATE_CACHE_KEY, $build_date);
        }
    }
}
