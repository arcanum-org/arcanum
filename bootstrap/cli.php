<?php

declare(strict_types=1);

use Arcanum\Cabinet\Container;
use Arcanum\Ignition\Kernel;
use App\Cli\Kernel as CliKernel;

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
 * Register the application's CLI Kernel
 */
$container->service(Kernel::class, CliKernel::class);

/**
 * Specify the CliKernel's primitive constructor arguments
 */
$rootDirectory = $_ENV['APP_ROOT_DIR'] ?? realpath(__DIR__ . '/..');

$container->specify(
    when: CliKernel::class,
    needs: '$rootDirectory',
    give: $rootDirectory
);

$container->specify(
    when: CliKernel::class,
    needs: '$configDirectory',
    give: $rootDirectory . '/config'
);

$container->specify(
    when: CliKernel::class,
    needs: '$filesDirectory',
    give: $rootDirectory . '/files'
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
 * Return the configured container
 *
 * CliRouter, Hydrator, ConsoleOutput, CliFormatRegistry, CliExceptionWriter,
 * and built-in commands (list, help, validate:handlers) are registered
 * automatically by the CliRouting bootstrapper (Ignition\Bootstrap\CliRouting)
 * from config/app.php and config/routes.php.
 */
return $container;
