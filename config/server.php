<?php

/*
 * PHP Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

// Configure the shared worker count in config/config.php.
return [
    [
        'type' => 'http',
        'addr' => '0.0.0.0:8620',
        'routes' => [\dirname(__DIR__) . '/app/web.php'],
        'resource' => \dirname(__DIR__) . '/www',
        'pipeline' => [
            Puff\I18n\Pipeline::class,
            // Puff\Cookie\Pipeline::class,
            // Puff\Session\Pipeline::class,
        ],
        'trusted_proxies' => [],
    ],
    [
        'type' => 'http',
        'addr' => '0.0.0.0:8621',
        'routes' => [\dirname(__DIR__) . '/app/api.php'],
        // 'resource' => \dirname(__DIR__) . '/www/assets',
        'pipeline' => [
            Pipeline\Api::class,
            Pipeline\Page::class,

        ],
        'trusted_proxies' => [],
    ],
    [
        'type' => 'http',
        'addr' => '0.0.0.0:8622',
        'routes' => [\dirname(__DIR__) . '/app/data.php'],
        // 'resource' => \dirname(__DIR__) . '/www/assets',
        'pipeline' => [
            Puff\I18n\Pipeline::class,
            Pipeline\Api::class,
            Pipeline\Page::class,

        ],
        'trusted_proxies' => [],
    ],
    // [
    //     'type' => 'websocket',
    //     'addr' => '0.0.0.0:8791',
    //     'routes' => [\dirname(__DIR__) . '/app/websocket.php'],
    //     'allowed_origins' => [],
    //     'protocols' => [],
    // ],

    // MCP over stateless Streamable HTTP.
    [
        'type' => 'mcp',
        'addr' => '0.0.0.0:8120',
        'path' => '/mcp',
        'name' => 'Puff MCP Server',
        'version' => '1.0.0',
        'pipeline' => [
            // App\Pipeline\Auth::class,
        ],
    ],

];
