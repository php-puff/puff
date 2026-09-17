<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace App\Event;

/** Raised after an access token has been signed successfully. */
final readonly class TokenIssued
{
    public function __construct(public string $subject)
    {
        logger()->info(sprintf('Token issued for subject: %s', $subject));
    }
}
