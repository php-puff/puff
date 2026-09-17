<?php

declare(strict_types=1);
/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

use App\Controller\WebSocket;

return [
    '/chat' => [
        'events' => [
            'message.echo' => [WebSocket::class, 'index'],
        ],
    ],
];
