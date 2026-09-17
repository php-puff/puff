<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace App\Listener;

use App\Event\UserRegistered;
use Psr\Log\LoggerInterface;
use Puff\Event\Listener;

/** Records a completed user registration without coupling it to its caller. */
final readonly class WriteRegistrationLog implements Listener
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function handle(object $event): void
    {
        if (!$event instanceof UserRegistered) {
            return;
        }

        $this->logger->info('User registered.', ['user_id' => $event->id]);
    }
}
