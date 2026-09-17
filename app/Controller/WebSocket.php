<?php

declare(strict_types=1);
/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issue
 * Copyright (c) PUFF
 */

namespace App\Controller;

final class WebSocket extends Controller
{
    /** @return array{event: string, path: string, data: array{message: string}} */
    public function index(mixed $data, string $event, string $path): array
    {
        $message = \is_array($data) ? ($data['message'] ?? $data) : $data;

        return [
            'event' => $event,
            'path' => $path,
            'data' => ['message' => 'Hello: ' . (string) $message],
        ];
    }
}
