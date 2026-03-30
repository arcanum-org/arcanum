<?php

declare(strict_types=1);

use Arcanum\Quill\Handler;
use Arcanum\Toolkit\Strings;
use Psr\Log\LogLevel;

/**
 * Log Configuration
 */
return [

    /**
     * Log Handlers
     * ------------
     *
     * This configuration defines all of the logging destinations that are
     * available to the application. Each handler should be a class that
     * implements the Arcanum\Quill\Handler interface.
     */
    'handlers' => [

        /**
         * Primary App Log
         * ---------------
         *
         * This is the primary log file for the application.
         */
        'app' => [
            // Stream handlers write to a file
            'type' => Handler::STREAM,

            // this path is relative to the app files directory
            'path' => 'logs/app.log',

            // minimum log level to write to the handler
            'level' => LogLevel::INFO,

            // whether or not to bubble the log up to other handlers
            'bubble' => true,

            // the file permissions to use when creating the log file
            'filePermission' => null,

            // whether or not to use file locking
            'useLocking' => false,
        ],

        /**
         * Daily App Log
         * -------------
         *
         * This is the daily log file for the application. Everything is
         * logged to this file, and the file is rotated daily.
         */
        'daily' => [
            // Rotating file handlers write to a file that is rotated daily
            'type' => Handler::ROTATING_FILE,

            // this path is relative to the app files directory
            'path' => 'logs/app.log',

            // the maximum number of files to keep
            'max_files' => 30,

            // minimum log level to write to the handler
            'level' => LogLevel::DEBUG,

            // whether or not to bubble the log up to other handlers
            'bubble' => true,

            // the file permissions to use when creating the log file
            'filePermission' => null,

            // whether or not to use file locking
            'useLocking' => false,
        ],

        /**
         * Syslog Handler
         * --------------
         *
         * This handler writes to syslog.
         */
        'syslog' => [
            // Syslog handlers write to syslog
            'type' => Handler::SYSLOG,

            // The string ident is added to each message
            'ident' => Strings::kebab($_ENV['APP_NAME'] ?? 'arcanum'),

            // The facility to use
            'facility' => \LOG_USER,

            // minimum log level to write to the handler
            'level' => LogLevel::INFO,

            // whether or not to bubble the log up to other handlers
            'bubble' => true,

            // Option flags for the openlog() call
            'options' => \LOG_PID,
        ],

        /**
         * Error Log Handler
         * -----------------
         *
         * This handler writes to the PHP error log using the error_log() function.
         */
        'error_log' => [
            // error_log handlers write to the PHP error log
            'type' => Handler::ERROR_LOG,

            // should the message be sent directly to the SAPI logging handler?
            'sapi' => false,

            // minimum log level to write to the handler
            'level' => LogLevel::ERROR,

            // whether or not to bubble the log up to other handlers
            'bubble' => true,

            // should expand new lines?
            'expand_newlines' => false,
        ],

        /**
         * Deprecation Log Handler
         * -----------------------
         *
         * The built-in error handler will log deprecation notices to this handler.
         */
        'deprecations' => [
            // Stream handlers write to a file
            'type' => Handler::STREAM,

            // this path is relative to the app files directory
            'path' => 'logs/deprecations.log',

            // minimum log level to write to the handler
            'level' => LogLevel::WARNING,

            // whether or not to bubble the log up to other handlers
            'bubble' => true,

            // the file permissions to use when creating the log file
            'filePermission' => null,

            // whether or not to use file locking
            'useLocking' => false,
        ]
    ],

    /**
     * Log Channels
     * ------------
     *
     * This configuration defines all of the logging channels that are
     * available to the application. Each channel should be a key in the
     * array, and the value should be an array of handlers to use for
     * that channel.
     */
    'channels' => [

        /**
         * Default Log Channel
         * -------------------
         *
         * This is the default log channel that will be used by the application
         * if no channel is specified.
         */
        'default' => ['app', 'daily', 'syslog'],

        /**
         * Local Log Channel
         * -----------------
         *
         * This channel should only write to local log files.
         */
        'local' => ['app', 'daily'],

        /**
         * Daily Log Channel
         * -----------------
         *
         * This channel should only write to daily log files. These files will
         * be rotated daily, and the number of files will be limited to the
         * number specified in the handler configuration.
         */
        'daily' => ['daily'],

        /**
         * Deprecation Log Channel
         * -----------------------
         *
         * This channel is used by the arcanum error handler to write deprecation
         * notices.
         */
        'deprecations' => ['app', 'daily', 'deprecations'],
    ],
];
