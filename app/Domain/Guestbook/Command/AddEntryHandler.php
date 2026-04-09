<?php

declare(strict_types=1);

namespace App\Domain\Guestbook\Command;

use App\Domain\Guestbook\Event\EntryAdded;
use App\Domain\Guestbook\Model\Model;
use Psr\EventDispatcher\EventDispatcherInterface;

final class AddEntryHandler
{
    public function __construct(
        private readonly Model $guestbook,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function __invoke(AddEntry $command): void
    {
        $this->guestbook->insertEntry(
            name: $command->name,
            message: $command->message,
        );

        $this->dispatcher->dispatch(new EntryAdded($command->name));
    }
}
