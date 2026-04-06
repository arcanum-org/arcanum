<?php

declare(strict_types=1);

namespace App\Domain\Contact\Query;

use App\Domain\Contact\Model\Model;

final class MessagesHandler
{
    public function __construct(
        private readonly Model $model,
    ) {
    }

    /** @return array{messages: list<array<string, mixed>>} */
    public function __invoke(Messages $query): array
    {
        $this->model->createTable();

        return ['messages' => $this->model->findAll()->rows()];
    }
}
