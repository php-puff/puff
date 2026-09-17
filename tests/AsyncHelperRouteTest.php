<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace Puff\Tests;

use App\Controller\Api\Puff as PuffController;
use PHPUnit\Framework\TestCase;
use Puff\Async\EventLoop;
use Puff\Async\Runtime;
use Puff\Di\Container;
use Puff\Http\Request;
use Puff\Http\Response;
use Puff\Routing\Router;

final class AsyncHelperRouteTest extends TestCase
{
    protected function setUp(): void
    {
        EventLoop::reset();
        $container = new Container();
        $container->instance(Response::class, new Response());
        Container::setInstance($container);
    }

    protected function tearDown(): void
    {
        Container::setInstance(null);
        EventLoop::reset();
    }

    public function testDeferredCallbacksRunInReverseOrder(): void
    {
        $data = $this->invoke('asyncDefer');

        self::assertSame(['body', 'second defer', 'first defer'], $data['events']);
    }

    public function testChannelTransfersProducerMessage(): void
    {
        $data = $this->invoke('asyncChannel');

        self::assertSame('hello from producer', $data['received']['value']);
        self::assertIsInt($data['received']['producer_fiber_id']);
        self::assertIsInt($data['consumer_fiber_id']);
    }

    public function testWaitGroupJoinsEveryWorker(): void
    {
        $data = $this->invoke('asyncWaitGroup');

        self::assertTrue($data['finished']);
        self::assertSame([1, 2, 3], \array_column($data['results'], 'worker'));
        self::assertSame([30, 10, 20], \array_column($data['results'], 'delay_ms'));
    }

    public function testAsyncRoutesAreRegistered(): void
    {
        $container = Container::getInstance();
        self::assertInstanceOf(Container::class, $container);
        $router = new Router(new Request());
        $container->scopedInstance(Router::class, $router);
        require \dirname(__DIR__) . '/app/routes.php';

        self::assertSame('/api/async/defer', $router->route('api.async.defer')?->path);
        self::assertSame('/api/async/channel', $router->route('api.async.channel')?->path);
        self::assertSame('/api/async/wait-group', $router->route('api.async.wait-group')?->path);
    }

    /** @return array<string, mixed> */
    private function invoke(string $method): array
    {
        $response = Runtime::run(static fn (): Response => (new PuffController())->{$method}());
        $data = \json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($data);
        return $data;
    }
}
