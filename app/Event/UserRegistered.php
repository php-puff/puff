<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace App\Event;

/** Raised after a user registration has completed successfully. */
final readonly class UserRegistered
{
    public function __construct(public string $id)
    {
    }
}
