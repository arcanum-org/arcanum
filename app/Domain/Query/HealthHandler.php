<?php

declare(strict_types=1);

namespace App\Domain\Query;

use Arcanum\Hourglass\Clock;
use Arcanum\Hourglass\SystemClock;

final class HealthHandler
{
    public function __construct(
        private readonly Clock $clock = new SystemClock(),
    ) {
    }

    /** @return array<string, string|int> */
    public function __invoke(Health $query): array
    {
        $result = [
            'status' => 'ok',
        ];

        if ($query->verbose) {
            $result['timestamp'] = $this->clock->now()->getTimestamp();
            $result['php'] = PHP_VERSION;
        }

        return $result;
    }
}
