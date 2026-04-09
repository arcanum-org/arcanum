<?php

declare(strict_types=1);

namespace App\Domain\Guestbook\Event;

use Arcanum\Htmx\ClientBroadcast;

/**
 * Fired when a new guestbook entry is added.
 *
 * Implements ClientBroadcast so the HtmxEventTriggerMiddleware
 * projects it as an HX-Trigger header — any element listening
 * for "guestbook:entry:added" via hx-trigger="entry-added from:body"
 * will refresh automatically.
 */
final readonly class EntryAdded implements ClientBroadcast
{
    public function __construct(
        public string $name,
    ) {
    }

    public function eventName(): string
    {
        return 'guestbook:entry:added';
    }

    public function payload(): array
    {
        return ['name' => $this->name];
    }
}
