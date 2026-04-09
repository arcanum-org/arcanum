<?php

declare(strict_types=1);

namespace App\Domain\Guestbook\Query;

use App\Domain\Guestbook\Model\Model;

final class GetEntriesHandler
{
    public function __construct(
        private readonly Model $guestbook,
    ) {
    }

    /**
     * @return array{entries: list<array<string, mixed>>}
     */
    public function __invoke(GetEntries $query): array
    {
        $entries = $this->guestbook->allEntries()->toSeries()->all();

        return ['entries' => $entries];
    }
}
