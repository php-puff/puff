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
use Puff\Http\Request;
use Puff\Http\Response;

final class Auth
{
    /**
     * Require claims verified by Puff\Jwt\Pipeline or Puff\Paseto\Pipeline.
     *
     * Place the token-verification pipeline before this pipeline.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->getAttribute('jwt') !== null || $request->getAttribute('paseto') !== null) {
            return $next($request);
        }

        return (new Response())->json(['error' => 'unauthorized'], 401)
            ->withHeader('WWW-Authenticate', 'Bearer')
            ->withHeader('Cache-Control', 'no-store');
    }
}
