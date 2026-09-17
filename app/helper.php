<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

use Puff\Application\Application;

if (!\function_exists('application')) {
    function application(): Application
    {
        static $app;
        if ($app instanceof Application) {
            return $app;
        }

        $app = new Application();
        $config = $app->container()->get('config');

        $timezone = $config->get('timezone');
        if (\is_string($timezone) && $timezone !== '') {
            \date_default_timezone_set($timezone);
        }

        $charset = $config->get('charset');
        if (\is_string($charset) && $charset !== '' && \function_exists('mb_internal_encoding')) {
            \mb_internal_encoding($charset);
        }

        return $app;
    }
}

if (!function_exists('root')) {
    function root(string ...$paths): string
    {
        $separator = DIRECTORY_SEPARATOR;
        $parts = array_filter(array_map(
            static fn (string $path): string => trim($path, $separator),
            $paths,
        ));
        $root = dirname(__DIR__);
        return $root . ($parts === [] ? '' : $separator . implode($separator, $parts));
    }
}

if (!function_exists('app_dir')) {
    function app_dir(string ...$paths): string
    {
        return root((string) env('APP', 'app'), ...$paths);
    }
}

if (!function_exists('base_path')) {
    function base_path(string ...$paths): string
    {
        return root(...$paths);
    }
}

if (!function_exists('runtime')) {
    function runtime(string ...$paths): string
    {
        $path = root('runtime', ...$paths);
        $directory = pathinfo($path, PATHINFO_EXTENSION) ? dirname($path) : $path;
        is_dir($directory) || mkdir($directory, 0777, true);
        return $path;
    }
}

if (!function_exists('www')) {
    function www(string ...$paths): string
    {
        $separator = DIRECTORY_SEPARATOR;
        $parts = $paths;
        $first = (string) ($parts[0] ?? '');
        $prefix = str_starts_with($first, '/') ? '' : (string) env('APP', 'app') . $separator;
        return $separator . $prefix . trim(implode($separator, array_filter(
            $parts,
            static fn (string $path): bool => trim($path, $separator) !== '',
        )), $separator);
    }
}

if (!function_exists('cli')) {
    function cli(): bool
    {
        return PHP_SAPI === 'cli';
    }
}
