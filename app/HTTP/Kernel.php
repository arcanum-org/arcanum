<?php

declare(strict_types=1);

namespace App\Http;

use Arcanum\Atlas\Router;
use Arcanum\Codex\Hydrator;
use Arcanum\Flow\Conveyor\Bus;
use Arcanum\Flow\Conveyor\Command;
use Arcanum\Flow\Conveyor\EmptyDTO;
use Arcanum\Flow\Conveyor\Query;
use Arcanum\Hyper\StatusCode;
use Arcanum\Ignition\HyperKernel;
use Arcanum\Shodo\EmptyResponseRenderer;
use Arcanum\Shodo\FormatRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The application's HTTP kernel.
 */
final class Kernel extends HyperKernel
{
    /**
     * Handle the request through the application.
     */
    protected function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        /** @var Router $router */
        $router = $this->container->get(Router::class);

        /** @var Hydrator $hydrator */
        $hydrator = $this->container->get(Hydrator::class);

        /** @var Bus $bus */
        $bus = $this->container->get(Bus::class);

        // Resolve the request to a Route
        $route = $router->resolve($request);

        // Hydrate the DTO from query params (GET) or request body (PUT/POST/PATCH/DELETE)
        if ($route->isQuery()) {
            $data = $request->getQueryParams();
        } else {
            $data = (array) ($request->getParsedBody() ?? []);
        }

        // If an explicit DTO class exists, hydrate it. Otherwise, create a
        // dynamic Command or Query — this allows handler-only routes where
        // the developer defines only the handler without a paired DTO class.
        if (class_exists($route->dtoClass)) {
            /** @var class-string<object> $dtoClass */
            $dtoClass = $route->dtoClass;
            $dto = $hydrator->hydrate($dtoClass, $data);
        } elseif ($route->isCommand()) {
            $dto = new Command($route->dtoClass, $data);
        } else {
            $dto = new Query($route->dtoClass, $data);
        }

        // Dispatch through Conveyor — handler returns result
        $result = $bus->dispatch($dto, prefix: $route->handlerPrefix);

        // Commands return status-code-only responses (no body)
        if ($route->isCommand()) {
            /** @var EmptyResponseRenderer $emptyRenderer */
            $emptyRenderer = $this->container->get(EmptyResponseRenderer::class);
            $status = $result instanceof EmptyDTO ? StatusCode::NoContent : StatusCode::Created;
            return $emptyRenderer->render($status);
        }

        // Unwrap QueryResult if the handler returned a non-object value
        if ($result instanceof \Arcanum\Flow\Conveyor\QueryResult) {
            $result = $result->data;
        }

        // Select renderer based on the route's format (parsed from URL extension)
        /** @var FormatRegistry $formats */
        $formats = $this->container->get(FormatRegistry::class);

        /** @var ResponseInterface $response */
        $response = $formats->renderer($route->format)->render($result);

        return $response;
    }
}
