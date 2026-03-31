<?php

declare(strict_types=1);

namespace App\Domain\Query;

final class Health
{
    public function __construct(
        public readonly bool $verbose = false,
    ) {
    }
}
