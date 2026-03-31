<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Arcanum\Gather\Configuration;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Adds CORS headers to every response.
 *
 * Reads settings from config/cors.php via the Configuration registry.
 */
final class Cors implements MiddlewareInterface
{
    public function __construct(private Configuration $config)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        /** @var list<string> $origins */
        $origins = $this->config->get('cors.allowed_origins') ?? ['*'];

        /** @var list<string> $methods */
        $methods = $this->config->get('cors.allowed_methods') ?? [];

        /** @var list<string> $headers */
        $headers = $this->config->get('cors.allowed_headers') ?? [];

        /** @var list<string> $exposed */
        $exposed = $this->config->get('cors.exposed_headers') ?? [];

        /** @var int $maxAge */
        $maxAge = $this->config->get('cors.max_age') ?? 0;

        /** @var bool $credentials */
        $credentials = $this->config->get('cors.allow_credentials') ?? false;

        $response = $response
            ->withHeader('Access-Control-Allow-Origin', implode(', ', $origins))
            ->withHeader('Access-Control-Allow-Methods', implode(', ', $methods))
            ->withHeader('Access-Control-Allow-Headers', implode(', ', $headers));

        if ($exposed !== []) {
            $response = $response->withHeader('Access-Control-Expose-Headers', implode(', ', $exposed));
        }

        if ($maxAge > 0) {
            $response = $response->withHeader('Access-Control-Max-Age', (string) $maxAge);
        }

        if ($credentials) {
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }
}
