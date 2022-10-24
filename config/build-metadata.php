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
        'store' => env('BUILD_METADATA_CACHE_STORE'),

        /*
         * Cache key to store metadata in.
         */
        'key' => env('BUILD_METADATA_CACHE_KEY', 'BUILD_METADATA'),
    ],
];
