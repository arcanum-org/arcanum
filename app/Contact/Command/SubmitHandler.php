<?php

declare(strict_types=1);

namespace App\Contact\Command;

use Psr\Log\LoggerInterface;

final class SubmitHandler
{
    public function __construct(
        private readonly LoggerInterface|null $logger = null,
    ) {
    }

    public function __invoke(Submit $command): void
    {
        $this->logger?->info('Contact form submitted', [
            'name' => $command->name,
            'email' => $command->email,
            'message' => $command->message,
        ]);
    }
}
