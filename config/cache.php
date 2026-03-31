<?php

declare(strict_types=1);

return [

    /**
     * PAGE ROUTE CACHE
     * ----------------
     *
     * Controls caching of auto-discovered page routes. When enabled, the
     * framework scans the pages directory once and caches the results.
     * Subsequent requests read from the cache instead of scanning.
     *
     * Set enabled to false during development so new pages are picked up
     * immediately. In production, leave it enabled and set max_age to 0
     * (no expiry) — clear the cache on deploy.
     */
    'pages' => [
        'enabled' => ($_ENV['APP_DEBUG'] ?? 'false') !== 'true',
        'max_age' => 0,
    ],

];
