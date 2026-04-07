<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Global Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware listed here runs on every HTTP request in the order listed.
    | Each entry is a class-string implementing Psr\Http\Server\MiddlewareInterface,
    | resolved from the container at dispatch time (so constructor injection works).
    |
    */
    'global' => [
        \App\Http\Middleware\Cors::class,
        \App\Http\Middleware\RateLimit::class,
        \App\Http\Middleware\Htmx::class,
    ],
];
