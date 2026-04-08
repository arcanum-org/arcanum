<?php

declare(strict_types=1);

namespace App\Test\Helpers;

use App\Helpers\IncantationHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IncantationHelper::class)]
final class IncantationHelperTest extends TestCase
{
    public function testTodayReturnsAnIncantation(): void
    {
        $helper = new IncantationHelper();

        $tip = $helper->pick();

        $this->assertNotEmpty($tip['title']);
        $this->assertNotEmpty($tip['body']);
    }

    public function testTodayIsStableWithinTheSameDay(): void
    {
        $helper = new IncantationHelper();

        $first = $helper->pick();
        $second = $helper->pick();

        $this->assertSame($first, $second);
    }

    public function testCountReturnsAtLeastOneIncantation(): void
    {
        $helper = new IncantationHelper();

        $this->assertGreaterThanOrEqual(1, $helper->count());
    }
}
