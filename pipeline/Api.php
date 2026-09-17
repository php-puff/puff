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
use Psr\Http\Message\ResponseInterface;
use Puff\Http\Request;
use Puff\Http\Response;
use Throwable;

final class Api
{
    public function handle(Request $request, Closure $next, mixed ...$guards): ResponseInterface
    {
        try {
            return $this->cors($this->normalize($next($request)));
        } catch (Throwable $e) {
            return $this->cors($this->error($e->getCode(), $e->getMessage()));
        }
    }

    private function normalize(mixed $result): ResponseInterface
    {
        if ($result instanceof ResponseInterface) {
            return $result;
        }

        return \is_scalar($result) || $result === null
            ? response()->make((string) $result)
            : response()->json($result);
    }

    private function error(int $status, string $message): Response
    {
        $responseStatus = $status >= 100 && $status <= 599 ? $status : 500;
        return response()->json(['code' => $status, 'data' => [], 'message' => $message], $responseStatus);
    }

    private function cors(ResponseInterface $response): ResponseInterface
    {
        return $response
            ->withHeader('Access-Control-Max-Age', '600')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type, Origin, X-Requested-With');
    }
}
