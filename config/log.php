<?php

declare(strict_types=1);

use Arcanum\Quill\Handler;
use Psr\Log\LogLevel;

/**
 * Log Configuration
 *
 * Minimal default: one file handler, one channel. For advanced setups
 * (rotating files, syslog, error_log, process handlers, multiple channels),
 * see the Quill README: src/Quill/README.md
 */
return [

    'handlers' => [

        // Primary app log — writes to {files}/logs/app.log
        'app' => [
            'type' => Handler::STREAM,
            'path' => 'logs/app.log',
            'level' => LogLevel::DEBUG,
        ],

        // HTTP access log — writes to {files}/logs/requests.log
        'requests' => [
            'type' => Handler::STREAM,
            'path' => 'logs/requests.log',
            'level' => LogLevel::DEBUG,
        ],

    ],

    'channels' => [

        // Default channel used when no channel is specified.
        'default' => ['app'],

        // Deprecation notices from the error handler.
        'deprecations' => ['app'],

        // HTTP request logging via RequestLogger listener.
        'requests' => ['requests'],

    ],
];
