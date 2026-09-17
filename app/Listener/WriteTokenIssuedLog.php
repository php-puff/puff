<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace App\Listener;

use App\Event\TokenIssued;
use Psr\Log\LoggerInterface;
use Puff\Event\Listener;

/** Audits token issuance without exposing the access token. */
final readonly class WriteTokenIssuedLog implements Listener
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function handle(object $event): void
    {
        if (!$event instanceof TokenIssued) {
            return;
        }

        $this->logger->info('Access token issued.', ['subject' => $event->subject]);
    }
}
