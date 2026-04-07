<?php

declare(strict_types=1);

namespace App\Test\Http\Listener;

use App\Http\Listener\RequestCounter;
use Arcanum\Hyper\Event\RequestHandled;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[CoversClass(RequestCounter::class)]
final class RequestCounterTest extends TestCase
{
    public function testFirstRequestSetsCountToOne(): void
    {
        $cache = new InMemoryCache();
        $counter = new RequestCounter($cache);

        $counter->onRequestHandled($this->makeEvent());

        $this->assertSame(1, $cache->get(RequestCounter::KEY));
    }

    public function testSubsequentRequestsIncrement(): void
    {
        $cache = new InMemoryCache();
        $counter = new RequestCounter($cache);

        $counter->onRequestHandled($this->makeEvent());
        $counter->onRequestHandled($this->makeEvent());
        $counter->onRequestHandled($this->makeEvent());

        $this->assertSame(3, $cache->get(RequestCounter::KEY));
    }

    public function testCorruptedNonIntValueResetsToOne(): void
    {
        $cache = new InMemoryCache();
        $cache->set(RequestCounter::KEY, 'not an int');

        $counter = new RequestCounter($cache);
        $counter->onRequestHandled($this->makeEvent());

        $this->assertSame(1, $cache->get(RequestCounter::KEY));
    }

    public function testEventIsReturnedUnchanged(): void
    {
        $cache = new InMemoryCache();
        $counter = new RequestCounter($cache);
        $event = $this->makeEvent();

        $result = $counter->onRequestHandled($event);

        $this->assertSame($event, $result);
    }

    private function makeEvent(): RequestHandled
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $response = $this->createStub(ResponseInterface::class);

        return new RequestHandled($request, $response);
    }
}
