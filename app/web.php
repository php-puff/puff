<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

Router::group(['id' => 'public', 'prefix' => '/', 'namespace' => 'App\Controller'], static function (): void {
    Router::get('/', 'Web@index')->id('home');
    Router::get('/ping', 'Web@ping')->id('ping');
    Router::get('/event', 'Web@event')->id('event');
    Router::get('/token', 'Web@token')->id('token');
    Router::get('/request', 'Web@request')->id('request');
    Router::get('/cookie', 'Web@cookie')->id('cookie');
    Router::get('/session', 'Web@session')->id('session');
});
