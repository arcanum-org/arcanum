<?php

declare(strict_types=1);

namespace App\Test\Http;

use App\Http\RenderMetrics;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RenderMetrics::class)]
final class RenderMetricsTest extends TestCase
{
    public function testStartTimeNullByDefault(): void
    {
        $metrics = new RenderMetrics();

        $this->assertNull($metrics->startTime());
    }

    public function testElapsedNullBeforeStartTimeSet(): void
    {
        $metrics = new RenderMetrics();

        $this->assertNull($metrics->elapsedMs());
    }

    public function testStartTimeIsReturned(): void
    {
        $metrics = new RenderMetrics();

        $metrics->setStartTime(1234.5);

        $this->assertSame(1234.5, $metrics->startTime());
    }

    public function testElapsedReturnsPositiveDurationAfterStartTime(): void
    {
        $metrics = new RenderMetrics();

        $metrics->setStartTime(microtime(true));
        usleep(1000); // 1ms
        $elapsed = $metrics->elapsedMs();

        $this->assertNotNull($elapsed);
        $this->assertGreaterThanOrEqual(1.0, $elapsed);
    }
}
