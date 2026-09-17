<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

Router::group([
    'id' => 'data',
    'prefix' => '/',
    'namespace' => 'App\Controller',
], static function (): void {
    Router::get('/page', 'Data@page')->id('data.page');
    Router::get('/(id:str)', 'Data@index')->id('data.index');
    Router::get('/eloquent', 'Data@eloquent')->id('data.eloquent');
    Router::get('/think', 'Data@thinkOrm')->id('data.thinkOrm');
    Router::get('/cycle', 'Data@cycleOrm')->id('data.cycleOrm');
});
