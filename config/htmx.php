<?php

return [
    /*
    |--------------------------------------------------------------------------
    | htmx Version Pin
    |--------------------------------------------------------------------------
    |
    | The htmx version to load from CDN. The {{ Htmx::script() }} helper
    | interpolates this into the CDN URL template below.
    |
    */
    'version' => '4.0.0-beta1',

    /*
    |--------------------------------------------------------------------------
    | CDN URL Template
    |--------------------------------------------------------------------------
    |
    | The {version} placeholder is replaced by the version above.
    |
    */
    'cdn_url' => 'https://unpkg.com/htmx.org@{version}/dist/htmx.min.js',

    /*
    |--------------------------------------------------------------------------
    | Subresource Integrity Hash
    |--------------------------------------------------------------------------
    |
    | SRI hash for the CDN script. Leave empty to skip integrity checking
    | (useful during beta when the hash changes with each release).
    |
    */
    'integrity' => '',

    /*
    |--------------------------------------------------------------------------
    | Vary Header
    |--------------------------------------------------------------------------
    |
    | When true (default), the HtmxRequestMiddleware adds Vary: HX-Request
    | to every response so HTTP caches distinguish between htmx and
    | full-page requests.
    |
    */
    'vary' => true,

    /*
    |--------------------------------------------------------------------------
    | Auth Redirect
    |--------------------------------------------------------------------------
    |
    | URL to redirect to when an htmx request gets a 401 or 403.
    | Set 'auth_refresh' to true to use HX-Refresh instead of HX-Location.
    |
    */
    'auth_redirect' => '/login',
    'auth_refresh' => false,
];
