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
     * @return array{entries?: list<array<string, mixed>>, error?: string}
     */
    public function __invoke(GetEntries $query): array
    {
        try {
            $entries = $this->guestbook->allEntries()->toSeries()->all();

            return ['entries' => $entries];
        } catch (\PDOException $e) {
            if (
                str_contains($e->getMessage(), 'no such table')
                || str_contains($e->getMessage(), "doesn't exist")
            ) {
                return ['error' => 'migrate'];
            }

            return ['error' => 'connection'];
        }
    }
}
