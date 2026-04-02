<?php

declare(strict_types=1);

namespace App\Pages;

final class Index
{
    public function __construct(
        public readonly string $name = 'Arcanum',
        public readonly string $message = 'Welcome to the Arcanum framework.',
    ) {
    }
}
