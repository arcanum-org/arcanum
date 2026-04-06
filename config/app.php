<?php

declare(strict_types=1);

return [

    /**
     * ENVIRONMENT
     * -----------
     *
     * The environment maight be used to determine how you configure different
     * aspects of your application.
     */
    'environment' => $_ENV['ENVIRONMENT'] ?? 'production',

    /**
     * DEBUG
     * -----
     *
     * The debug option is used to determine if the application should be run
     * in debug mode.
     */
    'debug' => $_ENV['APP_DEBUG'] ?? false,

    /**
     * VERBOSE ERRORS
     * ---------------
     *
     * When enabled, error responses include suggestions for fixing the problem.
     * Independent from debug mode — you might want suggestions in staging but
     * not stack traces, or vice versa. Defaults to the debug value when not set.
     */
    'verbose_errors' => $_ENV['APP_VERBOSE_ERRORS'] ?? null,

    /**
     * URL
     * ---
     *
     * This should be set to the root of your app.
     */
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',

    /**
     * Name
     * ----
     *
     * This is the name of your application.
     */
    'name' => $_ENV['APP_NAME'] ?? 'Arcanum',

    /**
     * NAMESPACE
     * ---------
     *
     * The root namespace for your application. This must match your
     * Composer autoload PSR-4 mapping (e.g., "App" maps to "app/").
     */
    'namespace' => 'App\\Domain',

    /**
     * PAGES NAMESPACE
     * ---------------
     *
     * The namespace for auto-discovered page routes. Pages are scanned
     * from the pages directory (default: app/Pages) and registered as
     * GET-only routes. Creating a class in this namespace registers the route.
     */
    'pages_namespace' => 'App\\Pages',
];
