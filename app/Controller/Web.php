<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace App\Controller;

use App\Event\TokenIssued;
use App\Event\UserRegistered;
use App\Listener\WriteRegistrationLog;
use App\Controller\Controller;
use Puff\Event\Dispatcher;
use Puff\Http\Request;
use Puff\Http\Response;
use Puff\Jwt\Jwt;

final class Web extends Controller
{
    public function index(): Response
    {
        return response()->html(view('index', ['phpVersion' => PHP_VERSION]));
    }

    public function ping(): Response
    {
        return response()->json([
            'status' => 'ok',
            'puff' => app()->version(),
            'time' => date(DATE_ATOM),
        ]);
    }

    public function event(Request $request, Dispatcher $events): Response
    {
        $user = $request->input('user', 'user-' . \random_int(1000, 9999));
        if (!\is_string($user) || \preg_match('/^[A-Za-z0-9_-]{1,128}$/D', $user) !== 1) {
            return response()->json(['error' => 'A valid user is required.'], 422);
        }

        $events->dispatch(new UserRegistered($user));

        return response()->json([
            'event' => UserRegistered::class,
            'user' => $user,
            'listener' => WriteRegistrationLog::class,
        ]);
    }

    public function token(Request $request, Jwt $jwt, Dispatcher $events): Response
    {
        $subject = $request->input('subject', 'user-' . random_int(1000, 9999));
        if (!\is_string($subject) || \preg_match('/^[A-Za-z0-9_-]{1,128}$/D', $subject) !== 1) {
            return response()->json(['error' => 'A valid subject is required.'], 422);
        }

        $token = $jwt->issue($subject);
        $events->dispatch(new TokenIssued($subject));

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'event' => TokenIssued::class,
        ]);
    }

    public function cookie(): Response
    {
        if (!\function_exists('cookie')) {
            return response()->json(['error' => 'Install puff/cookie to enable this endpoint.'], 501);
        }
        cookie('test', date('Y-m-d:H:i:s'));

        return response(cookie('test'));
    }

    public function session(): Response
    {
        if (!\function_exists('session')) {
            return response()->json(['error' => 'Install puff/session to enable this endpoint.'], 501);
        }
        session('test', 'ookk');

        return response(session('test'));
    }

}
