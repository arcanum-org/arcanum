<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Arcanum\Shodo\Formatters\HtmlFormatter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * HTMX integration middleware.
 *
 * - Detects HX-Request header and enables fragment rendering (layout-less
 *   HTML output for partial swaps).
 * - Copies Location headers to HX-Location so HTMX follows redirects
 *   automatically (e.g., after a command returns 201 Created + Location).
 */
final class Htmx implements MiddlewareInterface
{
    public function __construct(
        private readonly HtmlFormatter $formatter,
    ) {
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        if ($request->hasHeader('HX-Request')) {
            $this->formatter->setFragment(true);
        }

        $response = $handler->handle($request);

        if ($request->hasHeader('HX-Request') && $response->hasHeader('Location')) {
            $response = $response->withHeader(
                'HX-Location',
                $response->getHeaderLine('Location'),
            );
        }

        return $response;
    }
}
