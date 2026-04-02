<?php

declare(strict_types=1);

namespace App\Domain\Auth\Query;

use Arcanum\Auth\Identity;

final class WhoamiHandler
{
    public function __construct(private readonly Identity $identity)
    {
    }

    /** @return array<string, mixed> */
    public function __invoke(Whoami $query): array
    {
        return [
            'id' => $this->identity->id(),
            'roles' => $this->identity->roles(),
        ];
    }
}
