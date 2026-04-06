<?php

declare(strict_types=1);

use Arcanum\Auth\SimpleIdentity;

return [

    // Guard type: 'session', 'token', or ['session', 'token'] to try both.
    'guard' => 'token',

    'resolvers' => [
        // TODO: Replace with real authentication (database lookup, etc.)
        // These hardcoded credentials are for development only.

        // Session guard: maps identity ID → Identity
        'identity' => fn(string $id) => match ($id) {
            'admin-1' => new SimpleIdentity('admin-1', ['admin']),
            'user-1' => new SimpleIdentity('user-1', ['user']),
            default => null,
        },

        // Token guard: maps bearer token → Identity
        'token' => fn(string $token) => match (trim($token)) {
            'admin-token' => new SimpleIdentity('admin-1', ['admin']),
            'user-token' => new SimpleIdentity('user-1', ['user']),
            default => null,
        },

        // CLI login: validates credentials, returns Identity|null
        'credentials' => fn(string $email, string $password) => match ([$email, $password]) {
            ['admin@example.com', 'password'] => new SimpleIdentity('admin-1', ['admin']),
            ['user@example.com', 'password'] => new SimpleIdentity('user-1', ['user']),
            default => null,
        },
    ],

    // CLI login settings
    'login' => [
        'fields' => ['email', 'password'],
        'ttl' => 86400,
    ],
];
