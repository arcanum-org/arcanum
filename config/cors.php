<?php

declare(strict_types=1);

return [

    /**
     * ALLOWED ORIGINS
     * ---------------
     *
     * Origins permitted to make cross-origin requests.
     * Use '*' to allow any origin (not recommended for production).
     * Use specific origins for production: ['https://example.com', 'https://app.example.com']
     */
    'allowed_origins' => ['*'],

    /**
     * ALLOWED METHODS
     * ---------------
     *
     * HTTP methods permitted in cross-origin requests.
     */
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    /**
     * ALLOWED HEADERS
     * ---------------
     *
     * Request headers permitted in cross-origin requests.
     */
    'allowed_headers' => ['Content-Type', 'Authorization'],

    /**
     * EXPOSED HEADERS
     * ---------------
     *
     * Response headers that the browser is allowed to access.
     * Leave empty to use browser defaults.
     */
    'exposed_headers' => [],

    /**
     * MAX AGE
     * -------
     *
     * How long (in seconds) the browser should cache preflight results.
     * 0 means no caching.
     */
    'max_age' => 0,

    /**
     * ALLOW CREDENTIALS
     * -----------------
     *
     * Whether the browser should send cookies/auth headers with cross-origin
     * requests. Cannot be true when allowed_origins is '*'.
     */
    'allow_credentials' => false,

];
