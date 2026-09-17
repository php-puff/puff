<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

return [
    'driver' => 'blade',
    'paths' => [dirname(__DIR__) . '/app/View'],
    'cache' => dirname(__DIR__) . '/runtime/views',
    'options' => [],
];
