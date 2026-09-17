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

final class Demo
{
    public function handle(Request $request, Closure $next, mixed ...$guards): mixed
    {
        return $next($request);
    }
}
