<?php

declare(strict_types=1);

namespace App\Pages;

final class IndexHandler
{
    /** @return array<string, string> */
    public function __invoke(Index $query): array
    {
        return [
            'name' => 'Arcanum',
            'message' => 'Welcome to the Arcanum framework.',
        ];
    }
}
