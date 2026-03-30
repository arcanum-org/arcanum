<?php

declare(strict_types=1);

namespace App\Contact\Command;

final class Submit
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $message = '',
    ) {
    }
}
