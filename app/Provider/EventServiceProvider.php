<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace App\Provider;

use App\Event\TokenIssued;
use App\Event\UserRegistered;
use App\Listener\WriteRegistrationLog;
use App\Listener\WriteTokenIssuedLog;
use Puff\Di\ServiceProvider;
use Puff\Event\Dispatcher;

/** Registers application domain-event listeners. */
final class EventServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $events = $this->app->get(Dispatcher::class);
        if (!$events instanceof Dispatcher) {
            throw new \LogicException('Unable to resolve the event dispatcher.');
        }

        $events->listen(UserRegistered::class, WriteRegistrationLog::class);
        $events->listen(TokenIssued::class, WriteTokenIssuedLog::class);
    }
}
