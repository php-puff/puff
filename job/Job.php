<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace Job;

use Puff\Job\JobInterface;
use Puff\Job\Schedule;

/*
* Six-field cron format with seconds
* 0    1    2    3    4    5
* *    *    *    *    *    *
* -    -    -    -    -    -
* |    |    |    |    |    |
* |    |    |    |    |    +----- day of week (0 - 6) (Sunday=0)
* |    |    |    |    +----- month (1 - 12)
* |    |    |    +------- day of month (1 - 31)
* |    |    +--------- hour (0 - 23)
* |    +----------- min (0 - 59)
* +------------- sec (0-59)
*
* '00 * * * * *' // Run at second 00 of every minute
* '01 * * * * *' // Run at second 01 of every minute
* '/5 * * * * *' // Run every five seconds
* '/1 * * * * *' // Run every second
*/
#[Schedule('/1 * * * * *')]
final class Job implements JobInterface
{
    public function run(): void
    {
        // Log::debug("test warn .....", $_SERVER);
        logger()->debug('execute job at: ' . \date('Y-m-d H:i:s'));
    }
}
