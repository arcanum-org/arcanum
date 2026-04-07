<?php

declare(strict_types=1);

namespace App\Http\Listener;

use Arcanum\Hourglass\Stopwatch;
use Arcanum\Hyper\Event\RequestHandled;
use Arcanum\Quill\ChannelLogger;

/**
 * Logs HTTP requests with method, path, status, and duration.
 *
 * Listens to RequestHandled and reads the elapsed time from the framework
 * Stopwatch — specifically since the request.received instant the kernel
 * records before any application code runs. Log level is determined by the
 * response status code: 2xx → info, 4xx → warning, 5xx → error.
 *
 * Uses the 'requests' log channel for a separate HTTP access log.
 */
final class RequestLogger
{
    public function __construct(
        private readonly ChannelLogger $logger,
        private readonly Stopwatch $stopwatch,
    ) {
    }

    public function onRequestHandled(RequestHandled $event): RequestHandled
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $status = $response->getStatusCode();

        $elapsed = $this->stopwatch->timeSince('request.received');
        $duration = $elapsed !== null ? round($elapsed, 2) : null;

        $context = [
            'method' => $request->getMethod(),
            'path' => $request->getUri()->getPath(),
            'status' => $status,
        ];

        if ($duration !== null) {
            $context['duration_ms'] = $duration;
        }

        $message = sprintf(
            '%s %s → %d%s',
            $request->getMethod(),
            $request->getUri()->getPath(),
            $status,
            $duration !== null ? " ({$duration}ms)" : '',
        );

        $level = match (true) {
            $status >= 500 => 'error',
            $status >= 400 => 'warning',
            default => 'info',
        };

        $this->logger->channel('requests')->log($level, $message, $context);

        return $event;
    }
}
