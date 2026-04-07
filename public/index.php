<?php

/**
 * Arcanum Front Controller
 *
 * This is your application's front controller for HTTP requests. It will
 * bootstrap the application, create the HTTP kernel, and send the request
 * to the kernel for processing.
 */

declare(strict_types=1);

/**
 * Define The Application Start Time
 */
\define('ARCANUM_START', \microtime(true));

/**
 * Register The Auto Loader
 */
require __DIR__ . '/../vendor/autoload.php';

/**
 * Bootstrap The Application Container
 */
$container = require_once __DIR__ . '/../bootstrap/http.php';

/**
 * Get The Application's HTTP Kernel
 *
 * @var \App\Http\Kernel $kernel
 */
$kernel = $container->get(\Arcanum\Ignition\Kernel::class);

/**
 * Bootstrap the Kernel
 */
$kernel->bootstrap($container);

/**
 * Production CSS guardrail
 *
 * If the app is running in production (APP_DEBUG=false) and the built
 * Tailwind bundle is missing, log a warning so the developer notices.
 * Without this, the Tailwind CDN play script silently ships to prod —
 * functional but unsuitable.
 */
if (
    ($_ENV['APP_DEBUG'] ?? 'false') !== 'true'
    && !file_exists(__DIR__ . '/css/app.min.css')
) {
    /** @var \Arcanum\Quill\ChannelLogger $logger */
    $logger = $container->get(\Arcanum\Quill\ChannelLogger::class);
    $logger->channel('default')->warning(
        'Production CSS bundle missing — run `composer css:build` to generate '
        . 'public/css/app.min.css. The CDN play script is being served instead, '
        . 'which is not suitable for production.'
    );
}

/**
 * Get the Hyper Server to serve the request
 *
 * @var \Arcanum\Hyper\Server $server
 */
$server = $container->get(\Arcanum\Hyper\Server::class);

/**
 * Get the request from the Hyper Server
 */
$request = $server->request();

/**
 * Send the request to the kernel and get the response.
 *
 * This is the primary entrypoint into your application.
 *
 * @var \Arcanum\Hyper\Response $response
 */
$response = $kernel->handle($request);

/**
 * Normalize the response now that the Kernel is done with it
 */
$response = $server->composeResponse($request, $response);

/**
 * Send the response to the client
 */
$server->send($response);

/**
 * Terminate the application
 */
$kernel->terminate();
