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
$rootDirectory = $_ENV['APP_ROOT_DIR'] ?? realpath(__DIR__ . '/..');

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
 * Shodo's JsonExceptionRenderer converts exceptions into JSON responses.
 * Debug mode is controlled by the APP_DEBUG environment variable.
 */
$container->service(
    \Arcanum\Glitch\ExceptionRenderer::class,
    \Arcanum\Shodo\JsonExceptionRenderer::class,
);

$container->specify(
    when: \Arcanum\Shodo\JsonExceptionRenderer::class,
    needs: '$debug',
    give: ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
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
$container->service(\Arcanum\Shodo\EmptyResponseRenderer::class);

/**
 * Return the configured container
 *
 * Atlas routing, FormatRegistry, Hydrator, and JsonRenderer are registered
 * automatically by the Routing bootstrapper (Ignition\Bootstrap\Routing)
 * from config/routes.php and config/formats.php.
 */
return $container;
