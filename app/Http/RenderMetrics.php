<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Request-scoped holder for render-time metrics.
 *
 * Same pattern as Forge's DomainContext or Auth's ActiveIdentity:
 * a singleton service that one component writes to early in the
 * request and another reads from later. The RequestLogger listener
 * stores the request start time on this holder; EnvCheckHelper reads
 * it at template render time to surface the elapsed-so-far duration
 * on the welcome page.
 *
 * The duration this exposes is the "render-so-far" time, not the full
 * request duration — RequestHandled fires after the response is built
 * but before it's sent. That's exactly what the welcome page wants:
 * "this page rendered in X ms".
 */
final class RenderMetrics
{
    private ?float $startTime = null;

    public function setStartTime(float $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function startTime(): ?float
    {
        return $this->startTime;
    }

    /**
     * Elapsed milliseconds since the request started, or null if no
     * start time has been recorded yet (e.g. CLI requests, or before
     * RequestReceived has fired).
     */
    public function elapsedMs(): ?float
    {
        if ($this->startTime === null) {
            return null;
        }

        return round((microtime(true) - $this->startTime) * 1000, 2);
    }
}
