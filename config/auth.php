<?php

declare(strict_types=1);

use Arcanum\Auth\SimpleIdentity;

return [
    'guard' => 'token',

    'resolvers' => [
        // Session guard: maps identity ID → Identity
        'identity' => fn(string $id) => match ($id) {
            'admin-1' => new SimpleIdentity('admin-1', ['admin']),
            'user-1' => new SimpleIdentity('user-1', ['user']),
            default => null,
        },

        // Token guard: maps bearer token → Identity
        // For smoke testing: "admin-token" resolves to an admin user.
        'token' => fn(string $token) => match ($token) {
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
