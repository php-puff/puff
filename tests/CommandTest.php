<?php

declare(strict_types=1);
/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

namespace Puff\Cmd\Tests;

use Cmd\Application;
use PHPUnit\Framework\TestCase;
use Puff\Application\Exception as ApplicationException;
use Puff\Console\Output;

final class CommandTest extends TestCase
{
    private string $projectRoot;

    protected function setUp(): void
    {
        $this->projectRoot = \sys_get_temp_dir() . '/puff-cmd-' . \bin2hex(\random_bytes(6));
        self::assertTrue(\mkdir($this->projectRoot, 0777, true));
    }

    protected function tearDown(): void
    {
        ApplicationException::restore();
        if (!\is_dir($this->projectRoot)) {
            return;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->projectRoot, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($iterator as $item) {
            $item->isDir() ? \rmdir($item->getPathname()) : \unlink($item->getPathname());
        }
        \rmdir($this->projectRoot);
    }

    public function testGeneratesController(): void
    {
        $stream = \fopen('php://memory', 'w+');
        self::assertIsResource($stream);
        $application = new Application($this->projectRoot, new Output($stream));

        self::assertSame(0, $application->run(['puff', 'controller', 'Admin/User']));
        self::assertFileExists($this->projectRoot . '/app/Controller/Admin/User.php');
    }

    public function testSupportsShortOptions(): void
    {
        $stream = \fopen('php://memory', 'w+');
        self::assertIsResource($stream);
        $application = new Application($this->projectRoot, new Output($stream));

        self::assertSame(0, $application->run(['puff', 'service', 'Mailer', '-N', 'Domain']));
        self::assertFileExists($this->projectRoot . '/domain/Mailer.php');
    }

    public function testGeneratesJobFromEnglishStub(): void
    {
        $stream = \fopen('php://memory', 'w+');
        self::assertIsResource($stream);
        $application = new Application($this->projectRoot, new Output($stream));

        self::assertSame(0, $application->run(['puff', 'job', 'Cleanup']));
        $path = $this->projectRoot . '/job/Cleanup.php';
        self::assertFileExists($path);
        $contents = (string) \file_get_contents($path);
        self::assertStringContainsString("#[Schedule('0 * * * * *')]", $contents);
        self::assertStringContainsString('public function run(): void', $contents);
        self::assertStringContainsString('Add the scheduled task here.', $contents);
    }

    public function testGeneratesPipelineFromInstalledComponent(): void
    {
        $stream = \fopen('php://memory', 'w+');
        self::assertIsResource($stream);
        $application = new Application($this->projectRoot, new Output($stream));

        self::assertSame(0, $application->run(['puff', 'pipeline', 'Authenticate']));
        self::assertFileExists($this->projectRoot . '/app/Pipeline/Authenticate.php');
    }

    public function testModelCommandIsOnlyRegisteredWithEloquent(): void
    {
        $stream = \fopen('php://memory', 'w+');
        self::assertIsResource($stream);
        $application = new Application($this->projectRoot, new Output($stream));

        self::assertSame(1, $application->run(['puff', 'model', 'User']));
        self::assertFileDoesNotExist($this->projectRoot . '/app/Model/User.php');
    }

    public function testListsMigrationCommands(): void
    {
        $stream = \fopen('php://memory', 'w+');
        self::assertIsResource($stream);
        $application = new Application($this->projectRoot, new Output($stream));

        self::assertSame(0, $application->run(['puff']));
        \rewind($stream);
        $help = (string) \stream_get_contents($stream);
        self::assertStringContainsString('migration', $help);
        self::assertStringNotContainsString('make:migration', $help);
        self::assertStringNotContainsString('migrate:rollback', $help);
    }

    public function testCallsClassMethodWithNamedParameters(): void
    {
        $stream = \fopen('php://memory', 'w+');
        self::assertIsResource($stream);
        $application = new Application($this->projectRoot, new Output($stream));

        self::assertSame(0, $application->run([
            'puff',
            'call',
            CallTarget::class . '@format',
            '--params={"name":"Puff","count":2}',
        ]));
        \rewind($stream);

        self::assertSame("Puff-Puff\n", \stream_get_contents($stream));
    }

    public function testCallsSlashClassTargetWithInlineNamedParameters(): void
    {
        $stream = \fopen('php://memory', 'w+');
        self::assertIsResource($stream);
        $application = new Application($this->projectRoot, new Output($stream));

        self::assertSame(0, $application->run([
            'puff',
            'call',
            \str_replace('\\', '/', CallTarget::class) . '@format',
            'name=Puff',
            'count=2',
        ]));
        \rewind($stream);

        self::assertSame("Puff-Puff\n", \stream_get_contents($stream));
    }

    public function testCallsFunctionAndPrintsJsonResult(): void
    {
        $stream = \fopen('php://memory', 'w+');
        self::assertIsResource($stream);
        $application = new Application($this->projectRoot, new Output($stream));

        self::assertSame(0, $application->run([
            'puff',
            'call',
            __NAMESPACE__ . '\\callFixture',
            'async',
        ]));
        \rewind($stream);
        $result = \json_decode((string) \stream_get_contents($stream), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(['value' => 'async', 'called' => true], $result);
    }

    public function testCallRejectsInvalidJsonParameters(): void
    {
        $stdout = \fopen('php://memory', 'w+');
        $stderr = \fopen('php://memory', 'w+');
        self::assertIsResource($stdout);
        self::assertIsResource($stderr);
        $application = new Application($this->projectRoot, new Output($stdout, $stderr));

        self::assertSame(1, $application->run([
            'puff',
            'call',
            CallTarget::class . '@format',
            '--params={invalid}',
        ]));
        \rewind($stderr);

        self::assertStringContainsString('Syntax error', (string) \stream_get_contents($stderr));
    }

    public function testCallRejectsUnknownInlineParameter(): void
    {
        $stdout = \fopen('php://memory', 'w+');
        $stderr = \fopen('php://memory', 'w+');
        self::assertIsResource($stdout);
        self::assertIsResource($stderr);
        $application = new Application($this->projectRoot, new Output($stdout, $stderr));

        self::assertSame(1, $application->run([
            'puff',
            'call',
            CallTarget::class . '@format',
            'missing=value',
        ]));
        \rewind($stderr);

        self::assertStringContainsString('Unknown parameter [missing]', (string) \stream_get_contents($stderr));
    }
}

final class CallTarget
{
    public function format(string $name, int $count): string
    {
        return \implode('-', \array_fill(0, $count, $name));
    }
}

/** @return array{value: string, called: true} */
function callFixture(string $value): array
{
    return ['value' => $value, 'called' => true];
}
