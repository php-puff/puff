<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace App\Controller;

use Puff\Http\Request;
use Puff\Http\Response;

final class Puff extends Controller
{
    public function status(): Response
    {
        return response()->json([
            'framework' => ['name' => 'Puff', 'full_name' => 'PHP Unison Fiber Framework'],
            'runtime' => [
                'php' => PHP_VERSION,
                'sapi' => PHP_SAPI,
                'fiber_available' => class_exists(\Fiber::class),
                'fiber_id' => puff_id(),
            ],
        ]);
    }

    public function hello(string $name): Response
    {
        return response()->json(['message' => sprintf('Hello, %s!', $name), 'fiber_id' => puff_id()]);
    }

    public function fibers(): Response
    {
        $startedAt = hrtime(true);
        $first = puff_async(static function (): array {
            puff_delay(0.05);

            return ['task' => 'first', 'delay_ms' => 50, 'fiber_id' => puff_id(), 'parent_id' => puff_parent_id()];
        });
        $second = puff_async(static function (): array {
            puff_delay(0.03);

            return ['task' => 'second', 'delay_ms' => 30, 'fiber_id' => puff_id(), 'parent_id' => puff_parent_id()];
        });

        return response()->json([
            'message' => 'async tasks executed concurrently',
            'results' => [puff_await($first), puff_await($second)],
            'task_count_after_spawn' => puff_task_count(),
            'elapsed_ms' => round((hrtime(true) - $startedAt) / 1_000_000, 2),
        ]);
    }

    public function asyncDefer(): Response
    {
        $events = [];
        $task = puff_async(static function () use (&$events): void {
            puff_defer(static function () use (&$events): void { $events[] = 'first defer'; });
            puff_defer(static function () use (&$events): void { $events[] = 'second defer'; });
            $events[] = 'body';
        });
        puff_await($task);

        return response()->json(['message' => 'deferred callbacks run in LIFO order', 'events' => $events]);
    }

    public function asyncChannel(): Response
    {
        $channel = puff_channel();
        $producer = puff_async(static function () use ($channel): array {
            puff_delay(0.01);
            $message = ['value' => 'hello from producer', 'producer_fiber_id' => puff_id()];
            $channel->push($message);

            return $message;
        });
        $received = $channel->pop(1);
        puff_await($producer);
        $channel->close();

        return response()->json([
            'message' => 'channel transferred a value between fibers',
            'received' => $received,
            'consumer_fiber_id' => puff_id(),
        ]);
    }

    public function asyncWaitGroup(): Response
    {
        $group = puff_wait_group(3);
        $completed = [];
        $tasks = [];

        foreach ([30, 10, 20] as $index => $delayMs) {
            $tasks[] = puff_async(static function () use ($group, &$completed, $index, $delayMs): void {
                puff_defer(static fn () => $group->done());
                puff_delay($delayMs / 1_000);
                $completed[$index] = ['worker' => $index + 1, 'delay_ms' => $delayMs, 'fiber_id' => puff_id()];
            });
        }
        $finished = $group->wait(1);
        foreach ($tasks as $task) {
            puff_await($task);
        }
        ksort($completed);

        return response()->json([
            'message' => 'wait group joined all async workers',
            'finished' => $finished,
            'results' => array_values($completed),
        ]);
    }

    public function echo(): Response
    {
        /** @var Request $request */
        $request = request();

        return response()->json([
            'method' => $request->method(),
            'path' => $request->path(),
            'input' => $request->all(),
            'content_type' => $request->getHeaderLine('content-type'),
        ]);
    }
}
