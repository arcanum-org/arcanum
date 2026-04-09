<?php

declare(strict_types=1);

namespace App\Test\Domain\Query;

use App\Domain\Query\Health;
use App\Domain\Query\HealthHandler;
use Arcanum\Hourglass\FrozenClock;
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

    public function testVerboseTimestampComesFromInjectedClock(): void
    {
        // Arrange — pin the clock so the timestamp is deterministic.
        $clock = new FrozenClock(new \DateTimeImmutable('2026-04-08 12:00:00'));
        $query = new Health(verbose: true);
        $handler = new HealthHandler($clock);

        // Act
        $result = $handler($query);

        // Assert — timestamp matches the frozen clock exactly.
        $this->assertSame(
            $clock->now()->getTimestamp(),
            $result['timestamp'],
        );
    }
}
