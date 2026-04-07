<?php

declare(strict_types=1);

namespace App\Test\Helpers;

use Arcanum\Cabinet\Application;
use Arcanum\Ignition\Kernel;

/**
 * Bare-minimum Kernel implementation for tests that only need
 * rootDirectory(). Every other method either returns a sensible
 * default or throws — tests should never reach them.
 */
final class FixtureKernel implements Kernel
{
    public function __construct(
        private readonly string $rootDirectory,
    ) {
    }

    public function rootDirectory(): string
    {
        return $this->rootDirectory;
    }

    public function configDirectory(): string
    {
        return $this->rootDirectory . '/config';
    }

    public function filesDirectory(): string
    {
        return $this->rootDirectory . '/files';
    }

    public function bootstrap(Application $container): void
    {
        // no-op
    }

    public function terminate(): void
    {
        // no-op
    }

    /**
     * @return list<string>
     */
    public function requiredEnvironmentVariables(): array
    {
        return [];
    }
}
