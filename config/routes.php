<?php

declare(strict_types=1);

return [

    /**
     * PAGES
     * -----
     *
     * Pages are explicitly registered Query routes that bypass convention-based
     * URL-to-namespace mapping. The root path "/" maps to the Index class.
     *
     * Each entry is a path => format mapping. Use null to use the page default
     * format from config/formats.php.
     */
    'pages' => [
        '/' => 'json',
    ],

];
