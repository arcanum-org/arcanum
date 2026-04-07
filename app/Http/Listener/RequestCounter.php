<?php

declare(strict_types=1);

namespace App\Http\Listener;

use Arcanum\Hyper\Event\RequestHandled;
use Psr\SimpleCache\CacheInterface;

/**
 * Increments a persistent request counter on every handled request.
 *
 * Listens to the RequestHandled lifecycle event and stores the running
 * total in the default Vault store under the `framework.requests` key.
 * The welcome page reads this via EnvCheckHelper::requestCount() to
 * surface a "you are request #N since boot" footer crumb that quietly
 * demonstrates the framework's lifecycle event system without ever
 * mentioning it.
 *
 * The counter survives across requests but resets when the cache is
 * cleared (`php arcanum cache:clear`). For a dev welcome page that's
 * the right semantics — a fresh install reads 1.
 *
 * No locking — a dev machine serves one request at a time and a
 * race-lost increment doesn't cost anything load-bearing. If this
 * counter ever needs production accuracy under concurrency, swap to
 * an atomic increment driver.
 */
final class RequestCounter
{
    public const string KEY = 'framework.requests';

    public function __construct(
        private readonly CacheInterface $cache,
    ) {
    }

    public function onRequestHandled(RequestHandled $event): RequestHandled
    {
        $current = $this->cache->get(self::KEY, 0);
        $count = is_int($current) ? $current : 0;

        $this->cache->set(self::KEY, $count + 1);

        return $event;
    }
}
