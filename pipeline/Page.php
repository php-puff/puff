<?php

declare(strict_types=1);

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

namespace Pipeline;

use Closure;
use Puff\Database\Query;
use Puff\Http\Request;

final class Page
{
    private const DEFAULT_SIZE = 20;
    private const MAX_SIZE = 100;

    public function handle(Request $request, Closure $next, mixed ...$guards): mixed
    {
        $result = $next($request);
        [$page, $size] = $this->parameters($request);

        if ($result instanceof Query) {
            return $this->query($result, $page, $size);
        }
        if (\is_object($result)
            && \is_callable([$result, 'count'])
            && \is_callable([$result, 'limit'])
            && \is_callable([$result, 'offset'])
            && \is_callable([$result, 'fetchAll'])) {
            return $this->select($result, $page, $size);
        }

        return $result;
    }

    /** @return array{int, int} */
    private function parameters(Request $request): array
    {
        $query = $request->getQueryParams();
        $page = \max(1, (int) ($query['page'] ?? 1));
        $size = \min(self::MAX_SIZE, \max(1, (int) ($query['size'] ?? self::DEFAULT_SIZE)));
        return [$page, $size];
    }

    /** @return array{data: list<mixed>, meta: array{page: int, size: int, total: int, last: int}} */
    private function query(Query $query, int $page, int $size): array
    {
        $total = $query->count();
        return $this->result($query->limit($size, ($page - 1) * $size)->get(), $page, $size, $total);
    }

    /**
     * @param  object                                                                    $select
     * @return array{data: list<mixed>, meta: array{page: int, size: int, total: int, last: int}}
     */
    private function select(object $select, int $page, int $size): array
    {
        $total = (int) $select->count();
        $data = $select->limit($size)->offset(($page - 1) * $size)->fetchAll();
        return $this->result(\is_array($data) ? $data : \iterator_to_array($data), $page, $size, $total);
    }

    /**
     * @param  list<mixed>                                                               $data
     * @return array{data: list<mixed>, meta: array{page: int, size: int, total: int, last: int}}
     */
    private function result(array $data, int $page, int $size, int $total): array
    {
        return [
            'data' => $data,
            'meta' => [
                'page' => $page,
                'size' => $size,
                'last' => (int) \ceil($total / $size),
                'total' => $total
            ],
        ];
    }
}
