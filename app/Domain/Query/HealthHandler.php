<?php

declare(strict_types=1);

namespace App\Domain\Query;

final class HealthHandler
{
    /** @return array<string, string|int> */
    public function __invoke(Health $query): array
    {
        $result = [
            'status' => 'ok',
        ];

        if ($query->verbose) {
            $result['timestamp'] = time();
            $result['php'] = PHP_VERSION;
        }

        return $result;
    }
}
