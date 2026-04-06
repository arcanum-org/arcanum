<?php

declare(strict_types=1);

namespace App\Domain\Contact\Command;

use App\Domain\Contact\Model\Model;
use Psr\Log\LoggerInterface;

final class SubmitHandler
{
    public function __construct(
        private readonly Model $model,
        private readonly LoggerInterface|null $logger = null,
    ) {
    }

    public function __invoke(Submit $command): void
    {
        $this->model->createTable();
        $this->model->save(
            name: $command->name,
            email: $command->email,
            message: $command->message,
        );

        $this->logger?->info('Contact form submitted', [
            'name' => $command->name,
            'email' => $command->email,
        ]);
    }
}
