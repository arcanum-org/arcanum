<?php

declare(strict_types=1);

namespace App\Test\Domain\Query;

use App\Domain\Query\Health;
use App\Domain\Query\HealthHandler;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Example test: how to test a Query handler in Arcanum.
 *
 * Handlers are plain classes with __invoke() — no framework needed.
 * Construct the DTO, call the handler, assert the result.
 */
#[CoversClass(HealthHandler::class)]
final class HealthHandlerTest extends TestCase
{
    public function testReturnsStatusOk(): void
    {
        // Arrange
        $query = new Health();
        $handler = new HealthHandler();

        // Act
        $result = $handler($query);

        // Assert
        $this->assertSame('ok', $result['status']);
    }

    public function testVerboseIncludesPhpVersion(): void
    {
        // Arrange
        $query = new Health(verbose: true);
        $handler = new HealthHandler();

        // Act
        $result = $handler($query);

        // Assert
        $this->assertSame('ok', $result['status']);
        $this->assertSame(PHP_VERSION, $result['php']);
        $this->assertArrayHasKey('timestamp', $result);
    }

    public function testNonVerboseExcludesExtras(): void
    {
        // Arrange
        $query = new Health(verbose: false);
        $handler = new HealthHandler();

        // Act
        $result = $handler($query);

        // Assert
        $this->assertSame(['status' => 'ok'], $result);
    }
}
