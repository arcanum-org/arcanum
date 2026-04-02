<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | The store that CacheInterface resolves to when no name is specified.
    |
    */

    'default' => 'file',

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Each store defines a driver and its configuration. The app accesses
    | stores by name via CacheManager::store('name').
    |
    | Supported drivers: "file", "array", "null", "apcu", "redis"
    |
    */

    'stores' => [
        'file' => [
            'driver' => 'file',
            'path'   => 'cache' . DIRECTORY_SEPARATOR . 'app',
        ],
        'array' => [
            'driver' => 'array',
        ],
        // 'redis' => [
        //     'driver' => 'redis',
        //     'host'   => '127.0.0.1',
        //     'port'   => 6379,
        // ],
        // 'apcu' => [
        //     'driver' => 'apcu',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Framework Store Mapping
    |--------------------------------------------------------------------------
    |
    | Maps framework cache purposes to store names. The framework reads this
    | mapping during bootstrap. Override to move framework caches to faster
    | drivers (e.g., 'pages' => 'apcu').
    |
    */

    'framework' => [
        'pages'      => 'file',
        'middleware'  => 'file',
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy Page Route Cache
    |--------------------------------------------------------------------------
    |
    | Controls the existing page discovery cache (pre-Vault). Will be migrated
    | to use the Vault framework store in a future update.
    |
    */

    'pages' => [
        'enabled' => ($_ENV['APP_DEBUG'] ?? 'false') !== 'true',
        'max_age' => 0,
    ],

];
