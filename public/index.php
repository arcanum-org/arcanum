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
