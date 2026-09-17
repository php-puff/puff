<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace Cmd;

use Puff\Console\Contract;
use Puff\Console\Input;
use Puff\Console\Output;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

final readonly class Call implements Contract
{
    public function name(): string
    {
        return 'call';
    }

    public function description(): string
    {
        return 'Call a class method or function';
    }

    public function usage(): string
    {
        return 'call <class@method|class::method|function> [arguments] [--params=<json>]';
    }

    /** @return array<string, string> */
    public function valueOptions(): array
    {
        return ['params' => 'p'];
    }

    /** @return array<string, string> */
    public function flagOptions(): array
    {
        return [];
    }

    public function execute(Input $input, Output $output): int
    {
        $target = \str_replace('/', '\\', (string) $input->argument(0));
        if ($target === '') {
            throw new \InvalidArgumentException('Callable target is required.');
        }

        $arguments = $input->arguments(1);
        $parameters = $input->hasOption('params')
            ? $this->jsonParameters($input, $arguments)
            : $this->parameters($arguments);
        $this->validate($target, $parameters);

        $result = application()->container()->call($target, $parameters);
        if ($result !== null) {
            $output->write($this->format($result));
        }

        return 0;
    }

    /**
     * @param list<string> $arguments
     * @return array<int|string, mixed>
     */
    private function jsonParameters(Input $input, array $arguments): array
    {
        if ($arguments !== []) {
            throw new \InvalidArgumentException('Inline arguments cannot be combined with --params.');
        }

        $parameters = \json_decode(
            (string) $input->option('params'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        if (!\is_array($parameters)) {
            throw new \InvalidArgumentException('Call parameters must decode to an array or object.');
        }

        return $parameters;
    }

    /**
     * @param list<string> $arguments
     * @return array<int|string, mixed>
     */
    private function parameters(array $arguments): array
    {
        $parameters = [];
        foreach ($arguments as $argument) {
            if (!\str_contains($argument, '=')) {
                $parameters[] = $this->value($argument);
                continue;
            }

            [$name, $value] = \explode('=', $argument, 2);
            if ($name === '') {
                throw new \InvalidArgumentException('Named parameter cannot be empty.');
            }
            if (\array_key_exists($name, $parameters)) {
                throw new \InvalidArgumentException("Parameter [{$name}] is specified more than once.");
            }
            $parameters[$name] = $this->value($value);
        }

        return $parameters;
    }

    private function value(string $value): mixed
    {
        $lower = \strtolower($value);
        if ($lower === 'true' || $lower === 'false') {
            return $lower === 'true';
        }
        if ($lower === 'null') {
            return null;
        }
        if (\preg_match('/^-?(?:0|[1-9]\d*)$/D', $value) === 1) {
            $integer = \filter_var($value, FILTER_VALIDATE_INT);
            return $integer === false ? $value : $integer;
        }
        if (\preg_match('/^-?(?:0|[1-9]\d*)\.\d+(?:[eE][+-]?\d+)?$/D', $value) === 1) {
            return (float) $value;
        }

        return $value;
    }

    /** @param array<int|string, mixed> $parameters */
    private function validate(string $target, array $parameters): void
    {
        $reflection = $this->reflection($target);
        $names = \array_map(
            static fn (\ReflectionParameter $parameter): string => $parameter->getName(),
            $reflection->getParameters(),
        );
        foreach (\array_keys($parameters) as $name) {
            if (\is_string($name) && !\in_array($name, $names, true)) {
                throw new \InvalidArgumentException("Unknown parameter [{$name}] for [{$target}].");
            }
        }

        $positionals = \count(\array_filter(\array_keys($parameters), \is_int(...)));
        if ($positionals > \count($names)) {
            throw new \InvalidArgumentException("Too many positional parameters for [{$target}].");
        }
    }

    private function reflection(string $target): ReflectionFunctionAbstract
    {
        foreach (['@', '::'] as $separator) {
            if (\str_contains($target, $separator)) {
                [$class, $method] = \explode($separator, $target, 2);
                return new ReflectionMethod($class, $method);
            }
        }

        return new ReflectionFunction($target);
    }

    private function format(mixed $result): string
    {
        if (\is_string($result) || \is_int($result) || \is_float($result)) {
            return (string) $result;
        }

        return \json_encode(
            $result,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );
    }
}
