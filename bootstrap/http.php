<?php

declare(strict_types=1);

use Arcanum\Cabinet\Container;
use Arcanum\Ignition\Kernel;
use Arcanum\Hyper\Server;
use Arcanum\Hyper\PHPServerAdapter;
use App\Http\Kernel as WebKernel;

/**
 * Create The Application Container
 */
$container = new Container();

/**
 * Register the container as the Application instance
 */
$container->instance(\Arcanum\Cabinet\Application::class, $container);
$container->instance(\Psr\Container\ContainerInterface::class, $container);

/**
 * Register Hyper Server with a standard PHPServerAdapter
 *
 * The Hyper Server is responsible for generating request objects for
 * the Kernel and sending responses from the Kernel to the client.
 */
$container->serviceWith(Server::class, [PHPServerAdapter::class]);

/**
 * Register the application's HTTP Kernel
 */
$container->service(Kernel::class, WebKernel::class);

/**
 * Specify the WebKernel's primitive constructor arguments
 */
$rootDirectoryEnv = $_ENV['APP_ROOT_DIR'] ?? null;
$rootDirectory = is_string($rootDirectoryEnv)
    ? $rootDirectoryEnv
    : realpath(__DIR__ . '/..');
if ($rootDirectory === false) {
    throw new \RuntimeException('Could not resolve application root directory');
}

$container->specify(
    when: WebKernel::class,
    needs: '$rootDirectory',
    give: $rootDirectory
);

$container->specify(
    when: WebKernel::class,
    needs: '$configDirectory',
    give: $rootDirectory . '/config'
);

$container->specify(
    when: WebKernel::class,
    needs: '$filesDirectory',
    give: $rootDirectory . '/files'
);

/**
 * Register the Exception Renderer
 *
 * Hyper's JsonExceptionResponseRenderer converts exceptions into JSON responses.
 * Debug mode controls stack traces; verbose_errors controls suggestion hints.
 */
$container->service(
    \Arcanum\Glitch\ExceptionRenderer::class,
    \Arcanum\Hyper\JsonExceptionResponseRenderer::class,
);

$isDebug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';

$container->specify(
    when: \Arcanum\Hyper\JsonExceptionResponseRenderer::class,
    needs: '$debug',
    give: $isDebug,
);

$container->specify(
    when: \Arcanum\Hyper\JsonExceptionResponseRenderer::class,
    needs: '$verboseErrors',
    give: ($_ENV['APP_VERBOSE_ERRORS'] ?? null) !== null
        ? ($_ENV['APP_VERBOSE_ERRORS'] === 'true')
        : $isDebug,
);

/**
 * Register the HTML Exception Renderer
 *
 * Used for .html endpoints — the kernel picks the right renderer
 * based on the URL extension.
 */
$container->service(\Arcanum\Hyper\HtmlExceptionResponseRenderer::class);

$container->specify(
    when: \Arcanum\Hyper\HtmlExceptionResponseRenderer::class,
    needs: '$debug',
    give: $isDebug,
);

$container->specify(
    when: \Arcanum\Hyper\HtmlExceptionResponseRenderer::class,
    needs: '$verboseErrors',
    give: ($_ENV['APP_VERBOSE_ERRORS'] ?? null) !== null
        ? ($_ENV['APP_VERBOSE_ERRORS'] === 'true')
        : $isDebug,
);

/**
 * Register the Event Dispatcher
 */
$provider = new \Arcanum\Echo\Provider();
$container->instance(
    \Psr\EventDispatcher\EventDispatcherInterface::class,
    new \Arcanum\Echo\Dispatcher($provider),
);

/**
 * Register Lifecycle Event Listeners
 *
 * RequestLogger logs method, path, status, and duration on RequestHandled,
 * reading the elapsed time from the framework Stopwatch. RequestCounter
 * increments a Vault counter so the welcome page can show "request #N
 * since boot".
 */
$container->service(\App\Http\Listener\RequestLogger::class);
$container->service(\App\Http\Listener\RequestCounter::class);

$provider->listen(
    \Arcanum\Hyper\Event\RequestHandled::class,
    function (\Arcanum\Hyper\Event\RequestHandled $event) use ($container) {
        /** @var \App\Http\Listener\RequestLogger $logger */
        $logger = $container->get(\App\Http\Listener\RequestLogger::class);
        return $logger->onRequestHandled($event);
    },
);
$provider->listen(
    \Arcanum\Hyper\Event\RequestHandled::class,
    function (\Arcanum\Hyper\Event\RequestHandled $event) use ($container) {
        /** @var \App\Http\Listener\RequestCounter $counter */
        $counter = $container->get(\App\Http\Listener\RequestCounter::class);
        return $counter->onRequestHandled($event);
    },
);

/**
 * Register the Application's Error Handler
 */
$container->service(\Arcanum\Glitch\ErrorHandler::class, \App\Error\Handler::class);

/**
 * Register the Application's Exception Handler
 */
$container->service(\Arcanum\Glitch\ExceptionHandler::class, \App\Error\Handler::class);

/**
 * Register the Application's Shutdown Handler
 */
$container->service(\Arcanum\Glitch\ShutdownHandler::class, \App\Error\Handler::class);

/**
 * Register the Conveyor (Command Bus)
 */
$container->service(\Arcanum\Flow\Conveyor\Bus::class, \Arcanum\Flow\Conveyor\MiddlewareBus::class);

$container->specify(
    when: \Arcanum\Flow\Conveyor\MiddlewareBus::class,
    needs: '$debug',
    give: ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
);

/**
 * Register the EmptyResponseRenderer (used by the kernel for Commands)
 */
$container->service(\Arcanum\Hyper\EmptyResponseRenderer::class);

/**
 * Register the App template helper
 *
 * AppHelper is exposed in templates as the `App` alias via app/Helpers.php.
 * It needs the debug flag and the public directory path so it can decide
 * whether the production CSS bundle is available.
 */
$container->service(\App\Helpers\AppHelper::class);

$container->specify(
    when: \App\Helpers\AppHelper::class,
    needs: '$debug',
    give: $isDebug,
);

$container->specify(
    when: \App\Helpers\AppHelper::class,
    needs: '$publicDirectory',
    give: $rootDirectory . '/public',
);

/**
 * Return the configured container
 *
 * Atlas routing, FormatRegistry, Hydrator, and JsonRenderer are registered
 * automatically by the Routing bootstrapper (Ignition\Bootstrap\Routing)
 * from config/routes.php and config/formats.php.
 */
return $container;
