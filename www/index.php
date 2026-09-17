<?php

declare(strict_types=1);
/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

require \dirname(__DIR__) . '/vendor/autoload.php';

$request = (new Puff\Http\Factory\ServerRequestFactory())->fromGlobals();
$response = application()
            ->container()
            ->make(Puff\HttpServer\ServiceProvider::class)
            ->handle($request);

(new Puff\Http\Response\Emitter())->emit($response);
