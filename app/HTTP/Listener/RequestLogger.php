<?php

declare(strict_types=1);

namespace App\Http\Listener;

use Arcanum\Hyper\Event\RequestHandled;
use Arcanum\Hyper\Event\RequestReceived;
use Arcanum\Quill\Logger;

/**
 * Logs HTTP requests with method, path, status, and duration.
 *
 * Listens to RequestReceived to record the start time, and
 * RequestHandled to log the completed request. Log level is
 * determined by the response status code:
 *   2xx → info, 4xx → warning, 5xx → error.
 *
 * Uses the 'requests' log channel for a separate HTTP access log.
 */
final class RequestLogger
{
    public function __construct(
        private readonly Logger $logger,
    ) {
    }

    public function onRequestReceived(RequestReceived $event): RequestReceived
    {
        $event->setRequest(
            $event->getRequest()->withAttribute(
                'arcanum.start_time',
                microtime(true),
            ),
        );

        return $event;
    }

    public function onRequestHandled(RequestHandled $event): RequestHandled
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $status = $response->getStatusCode();

        $startTime = $request->getAttribute('arcanum.start_time');
        $duration = is_float($startTime)
            ? round((microtime(true) - $startTime) * 1000, 2)
            : null;

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
