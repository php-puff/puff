<?php

declare(strict_types=1);

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

namespace Puff\Tests;

use Pipeline\Page;
use PHPUnit\Framework\TestCase;
use Puff\Http\Request;

final class PageTest extends TestCase
{
    public function testPaginatesCycleStyleSelect(): void
    {
        $select = new class () {
            /** @var list<array{id: int}> */
            private array $data = [];
            private ?int $limit = null;
            private ?int $offset = null;

            public function __construct()
            {
                for ($id = 1; $id <= 10; ++$id) {
                    $this->data[] = ['id' => $id];
                }
            }

            public function count(): int
            {
                return \count($this->data);
            }

            public function limit(int $limit): self
            {
                $this->limit = $limit;
                return $this;
            }

            public function offset(int $offset): self
            {
                $this->offset = $offset;
                return $this;
            }

            /** @return list<array{id: int}> */
            public function fetchAll(): array
            {
                return \array_slice($this->data, $this->offset ?? 0, $this->limit ?? \count($this->data));
            }
        };

        $result = (new Page())->handle(
            new Request(queryParams: ['page' => 2, 'size' => 3]),
            static fn (): object => $select,
        );

        self::assertSame([['id' => 4], ['id' => 5], ['id' => 6]], $result['data']);
        self::assertSame([
            'page' => 2,
            'size' => 3,
            'total' => 10,
            'last' => 4,
        ], $result['meta']);
    }

    public function testLeavesExecutedResultsUntouched(): void
    {
        $result = [['id' => 1]];

        self::assertSame($result, (new Page())->handle(
            new Request(),
            static fn (): array => $result,
        ));
    }
}
