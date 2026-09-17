<?php

declare(strict_types=1);
/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

namespace Cmd;

use Puff\Console\Console;
use Puff\Console\Discovery;
use Puff\Console\GenerateCommand;
use Puff\Console\Generator;
use Puff\Console\Output;

final readonly class Application
{
    private Console $console;

    public function __construct(string $root, ?Output $output = null)
    {
        $generator = new Generator($root);
        $commands = [
            new Call(),
            new GenerateCommand('controller', 'App\\Controller', __DIR__ . '/stub/controller.stub', $generator),
            new GenerateCommand('service', 'Service', __DIR__ . '/stub/service.stub', $generator),
            ...Discovery::commands($root, application()->container()),
        ];
        $this->console = new Console(
            $commands,
            $output,
        );
    }

    /** @param list<string>|null $argv */
    public function run(?array $argv = null): int
    {
        return $this->console->run($argv);
    }
}
