<?php

declare(strict_types=1);

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

namespace Puff\Tests;

use Pipeline\Auth;
use PHPUnit\Framework\TestCase;
use Puff\Http\Request;
use Puff\Http\Response;

final class AuthTest extends TestCase
{
    public function testAllowsVerifiedJwtClaims(): void
    {
        $response = (new Auth())->handle(
            (new Request())->withAttribute('jwt', ['sub' => 'user-1']),
            static fn (): Response => new Response(),
        );

        self::assertInstanceOf(Response::class, $response);
        self::assertSame(200, $response->getStatusCode());
    }

    public function testRejectsUnauthenticatedRequest(): void
    {
        $response = (new Auth())->handle(new Request(), static fn (): Response => new Response());

        self::assertInstanceOf(Response::class, $response);
        self::assertSame(401, $response->getStatusCode());
        self::assertSame('Bearer', $response->getHeaderLine('WWW-Authenticate'));
        self::assertSame('no-store', $response->getHeaderLine('Cache-Control'));
    }
}
