<?php

declare(strict_types=1);

return [

    /**
     * CUSTOM ROUTES
     * -------------
     *
     * Explicit path → class mappings that bypass convention-based resolution.
     * Each entry maps a URL path to a DTO class with optional method and format config.
     *
     * Custom routes take priority over convention routing.
     *
     * 'path' => [
     *     'class'   => 'App\Domain\Namespace\Query\ClassName',
     *     'methods' => ['GET'],        // optional, defaults to ['GET']
     *     'format'  => 'json',         // optional, defaults to formats.default
     * ],
     */
    'custom' => [
        // '/dashboard' => [
        //     'class' => 'App\Domain\Admin\Query\Dashboard',
        //     'methods' => ['GET'],
        // ],
    ],

    /**
     * PAGE FORMAT OVERRIDES
     * ---------------------
     *
     * Pages are auto-discovered from the app/Pages directory — creating a
     * class is all that's needed to register a page route. This section
     * lets you override the default format (html) for specific pages.
     *
     * 'path' => 'format',
     */
    'pages' => [
        '/' => 'json',
    ],

];
