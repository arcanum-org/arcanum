<?php

declare(strict_types=1);

// Application-wide template helpers.
//
// Loaded by Bootstrap\Helpers and registered as global helpers.
// Each entry maps a template alias to a helper class. Domain-specific
// Helpers.php files (under app/Domain/<Domain>/) can override any
// alias declared here on a per-domain basis.

return [
    'App' => App\Helpers\AppHelper::class,
];
