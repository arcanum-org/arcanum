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
     * The namespace for explicitly registered page routes. Pages bypass
     * convention-based routing and live under this namespace directly.
     */
    'pages_namespace' => 'App\\Pages',
];
