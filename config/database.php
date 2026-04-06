<?php

declare(strict_types=1);

/**
 * Database Configuration
 *
 * Arcanum uses SQL files as first-class methods via Forge. Each domain's
 * Model/ directory contains .sql files that become callable methods on
 * $db->model. See the Forge README for full documentation.
 */
return [

    // Auto-generate typed Model classes from SQL files in development.
    // true = regenerate when SQL files change, false = throw if stale, null = skip.
    'auto_forge' => ($_ENV['APP_DEBUG'] ?? false) === 'true',

    // Default connection name.
    'default' => 'sqlite',

    // Connection definitions.
    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            // SQLite file created automatically in the files/ directory.
            'database' => dirname(__DIR__) . '/files/app.sqlite',
        ],
    ],

];
