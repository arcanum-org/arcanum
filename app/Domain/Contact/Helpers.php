<?php

declare(strict_types=1);

// Domain-scoped helpers for the Contact domain.
// Only available in templates rendered for DTOs under App\Domain\Contact.

return [
    'Contact' => App\Domain\Contact\ContactHelper::class,
];
