<?php

declare(strict_types=1);

namespace App\Http;

use Arcanum\Atlas\Router;
use Arcanum\Codex\Hydrator;
use Arcanum\Flow\Conveyor\AcceptedDTO;
use Arcanum\Flow\Conveyor\Command;
use Arcanum\Flow\Conveyor\EmptyDTO;
use Arcanum\Flow\Conveyor\Page;
use Arcanum\Flow\Conveyor\Query;
use Arcanum\Flow\Conveyor\QueryResult;
use Arcanum\Hyper\CallableHandler;
use Arcanum\Hyper\StatusCode;
use Arcanum\Ignition\HyperKernel;
use Arcanum\Ignition\RouteDispatcher;
use Arcanum\Hyper\EmptyResponseRenderer;
use Arcanum\Hyper\FormatRegistry;
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

        /** @var RouteDispatcher $dispatcher */
        $dispatcher = $this->container->get(RouteDispatcher::class);

        // Resolve the request to a Route
        $route = $router->resolve($request);

        // Wrap the core dispatch logic in per-route HTTP middleware.
        // This allows HTTP-layer middleware (auth, rate limiting) to
        // short-circuit before hydration or handler execution.
        $core = new CallableHandler(
            fn(ServerRequestInterface $r) => $this->dispatchRoute($r, $route, $hydrator, $dispatcher)
        );

        return $dispatcher->wrapHttp($route, $core)->handle($request);
    }

    /**
     * Dispatch a resolved route through hydration and the command bus.
     */
    private function dispatchRoute(
        ServerRequestInterface $request,
        \Arcanum\Atlas\Route $route,
        Hydrator $hydrator,
        RouteDispatcher $dispatcher,
    ): ResponseInterface {
        // Pages: template-driven, no custom handler.
        if ($route->isPage()) {
            return $this->handlePage($request, $route, $hydrator, $dispatcher);
        }

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

        // Dispatch through RouteDispatcher — applies per-route before/after
        // middleware around the bus dispatch.
        $result = $dispatcher->dispatch($dto, $route);

        // Commands return status-code-only responses (no body)
        if ($route->isCommand()) {
            /** @var EmptyResponseRenderer $emptyRenderer */
            $emptyRenderer = $this->container->get(EmptyResponseRenderer::class);
            $status = match (true) {
                $result instanceof EmptyDTO => StatusCode::NoContent,
                $result instanceof AcceptedDTO => StatusCode::Accepted,
                default => StatusCode::Created,
            };
            return $emptyRenderer->render($status);
        }

        // Unwrap QueryResult if the handler returned a non-object value
        if ($result instanceof QueryResult) {
            $result = $result->data;
        }

        // Select renderer based on the route's format (parsed from URL extension)
        /** @var FormatRegistry $formats */
        $formats = $this->container->get(FormatRegistry::class);

        /** @var ResponseInterface $response */
        $response = $formats->renderer($route->format)->render($result, $route->dtoClass);

        return $response;
    }

    /**
     * Handle a page request.
     *
     * Pages are template-driven: no custom handler, optional DTO for default data,
     * query params hydrated into the DTO or passed directly as template variables.
     */
    private function handlePage(
        ServerRequestInterface $request,
        \Arcanum\Atlas\Route $route,
        Hydrator $hydrator,
        RouteDispatcher $dispatcher,
    ): ResponseInterface {
        $data = $request->getQueryParams();

        if (class_exists($route->dtoClass)) {
            /** @var class-string<object> $dtoClass */
            $dtoClass = $route->dtoClass;
            $dedicated = $hydrator->hydrate($dtoClass, $data);
            $pageData = get_object_vars($dedicated);
        } elseif ($data !== []) {
            $pageData = $data;
        } else {
            $pageData = [];
        }

        $dto = new Page($route->dtoClass, $pageData);
        $result = $dispatcher->dispatch($dto, $route);

        if ($result instanceof QueryResult) {
            $result = $result->data;
        }

        /** @var FormatRegistry $formats */
        $formats = $this->container->get(FormatRegistry::class);

        /** @var ResponseInterface $response */
        $response = $formats->renderer($route->format)->render($result, $route->dtoClass);

        return $response;
    }
}
