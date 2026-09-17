<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

Router::group([
    'id' => 'api',
    'prefix' => '/',
    'namespace' => 'App\Controller',
    // 'pipeline' => [Puff\Jwt\Pipeline::class],
], static function (): void {
    Router::get('/status', 'Puff@status')->id('api.status');
    Router::get('/hello/(name:str)', 'Puff@hello')->id('api.hello');
    Router::get('/fibers', 'Puff@fibers')->id('api.fibers');
    Router::get('/async/defer', 'Puff@asyncDefer')->id('api.async.defer');
    Router::get('/async/channel', 'Puff@asyncChannel')->id('api.async.channel');
    Router::get('/async/wait-group', 'Puff@asyncWaitGroup')->id('api.async.wait-group');
    Router::any('/echo', 'Puff@echo')->id('api.echo');
});
