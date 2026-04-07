<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Arcanum\Gather\Configuration;
use Arcanum\Glitch\HttpException;
use Arcanum\Hyper\StatusCode;
use Arcanum\Throttle\RateLimiter;
use Arcanum\Throttle\SlidingWindow;
use Arcanum\Throttle\TokenBucket;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Global rate-limiting middleware.
 *
 * Extracts the client IP from the request and checks the rate limiter.
 * On reject, throws a 429 Too Many Requests exception.
 * On allow, adds X-RateLimit-* headers to the response.
 */
final class RateLimit implements MiddlewareInterface
{
    private readonly RateLimiter $limiter;
    /** @var positive-int */
    private readonly int $limit;
    /** @var positive-int */
    private readonly int $window;

    public function __construct(CacheInterface $cache, Configuration $config)
    {
        /** @var int $limit */
        $limit = $config->get('throttle.limit') ?? 60;

        /** @var int $window */
        $window = $config->get('throttle.window') ?? 60;

        /** @var string $strategy */
        $strategy = $config->get('throttle.strategy') ?? 'token_bucket';

        $throttler = match ($strategy) {
            'sliding_window' => new SlidingWindow(),
            default => new TokenBucket(),
        };

        $this->limiter = new RateLimiter($cache, $throttler);
        $this->limit = $limit > 0 ? $limit : 60;
        $this->window = $window > 0 ? $window : 60;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $remote = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
        // PSR-16 cache keys forbid {}()/\@: — IPv6 addresses contain colons,
        // so we hash to produce a safe deterministic key.
        $key = 'throttle.' . hash('xxh128', (string) $remote);
        $quota = $this->limiter->attempt($key, $this->limit, $this->window);

        if (! $quota->isAllowed()) {
            throw new HttpException(StatusCode::TooManyRequests);
        }

        $response = $handler->handle($request);

        foreach ($quota->headers() as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }
}
