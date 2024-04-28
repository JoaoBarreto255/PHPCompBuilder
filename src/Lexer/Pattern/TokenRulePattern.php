<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Lexer\Pattern;

use JB255\PHPCompBuilder\Lexer\Pattern\Exception\InvalidParameterTypeException;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
readonly class TokenRulePattern
{
    public string $pattern;
    protected \Closure $callback;

    public function __construct(
        public string $tokenName,
        string $pattern,
        ?\Closure $callback = null,
        public bool $reserved = false,
        public bool $caseInsensitive = false,
    ) {
        if (empty($pattern)) {
            throw new \LogicException('Pattern must not be empty');
        }

        if (0 !== strpos($pattern, '/')) {
            $pattern = '/'.$pattern;
        }

        if (!preg_match('/.*[^\\\]\/\w?$/', $pattern)) {
            $pattern .= '/';
        }

        if ($caseInsensitive) {
            $pattern .= 'i';
        }

        $this->pattern = $pattern;
        if (null === $callback) {
            $callback = fn($v) => null;
        }

        $this->validateParameters($callback);
        $this->callback = $callback;
    }

    protected function validateParameters(\Closure $closure): void
    {
        $reflFunc = new \ReflectionFunction($closure);
        if (0 === $reflFunc->getNumberOfParameters()) {
            throw new \LogicException('You shall not pass with zero parameters!');
        }

        if (4 < $reflFunc->getNumberOfRequiredParameters()) {
            throw new \LogicException('Too many required arguments in closure!');
        }

        foreach ($reflFunc->getParameters() as $param) {
            if (4 < ($position = $param->getPosition())) {
                continue;
            }

            $this->validateParamType(
                $param, 0 === $position ? 'string' : 'int'
            );
        }
    }

    protected function validateParamType(\ReflectionParameter $param, string $expected = 'string'): void
    {
        if (!$param->hasType()) {
            return;
        }
        if (($type = $param->getType()) instanceof \ReflectionNamedType
            && $type->getName() === $expected
        ) {
            return;
        }

        if ($type instanceof \ReflectionNamedType) {
            throw $this->buildInvalidParameterTypeException($param, $expected, $type->getName());
        }

        if (!$type instanceof \ReflectionUnionType
            && !$type instanceof \ReflectionIntersectionType
            && (string) $type !== $expected
        ) {
            throw $this->buildInvalidParameterTypeException($param, $expected, (string) $type);
        }

        $subTypes = array_map(fn ($t) => $t->getName(), $type->getTypes());
        if ($type instanceof \ReflectionIntersectionType) {
            throw $this->buildInvalidParameterTypeException($param, $expected, implode('&', $subTypes));
        }

        if ($type instanceof \ReflectionUnionType 
            && 0 === count(array_filter($subTypes, fn ($t) => $t === $expected))
        ) {
            throw $this->buildInvalidParameterTypeException($param, $expected, implode('&', $subTypes));
        }
    }

    protected function buildInvalidParameterTypeException(
        \ReflectionParameter $param, string $expectedType, string $currentType
    ): InvalidParameterTypeException {
        return new InvalidParameterTypeException(
            'callback', $param->getName(), $expectedType, $currentType
        );
    }

    public function executeCallback(
        string $value, int $positon, int $lineno, int $column
    ) {
        $callback = $this->callback;
        $totalOfArguments = (new \ReflectionFunction($this->callback))->getNumberOfRequiredParameters();

        return match ($totalOfArguments) {
            1 => $callback($value),
            2 => $callback($value, $positon),
            3 => $callback($value, $positon, $lineno),
            4 => $callback($value, $positon, $lineno, $column),
        };
    }
}
