<?php

declare(strict_types=1);

namespace App\Domain\Auth\Query;

use Arcanum\Auth\Identity;

final class AdminOnlyHandler
{
    public function __construct(private readonly Identity $identity)
    {
    }

    /** @return array<string, string> */
    public function __invoke(AdminOnly $query): array
    {
        return [
            'message' => 'Welcome, admin ' . $this->identity->id(),
        ];
    }
}
