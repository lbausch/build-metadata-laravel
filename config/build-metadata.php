<?php

return [
    /*
     * File which contains build metadata.
     */
    'file' => env('BUILD_METADATA_FILE', base_path('build-metadata.json')),

    'cache' => [
        /*
         * Cache store to use.
         */
        'store' => env('BUILD_METADATA_CACHE_STORE', config('cache.default')),

        /*
         * Cache key for storing metadata.
         */
        'key' => env('BUILD_METADATA_CACHE_KEY', 'BUILD_METADATA'),
    ],
];
