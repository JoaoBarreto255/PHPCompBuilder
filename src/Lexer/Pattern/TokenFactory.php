<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Lexer\Pattern;

use JB255\PHPCompBuilder\Lexer\Pattern\Exception\InvalidParameterTypeException;

class TokenFactory
{
    protected ?\Closure $tokenBuilder = null;

    protected readonly bool $reserved;
    protected readonly string $tokenName;
    protected readonly string $container;

    public function __construct(TokenRulePattern $pattern)
    {
        $this->reserved = $pattern->reserved;
        $this->tokenName = $pattern->tokenName;
        $this->container = $pattern->container;
        $this->tokenBuilder = $this->createTokenBuilder();
    }

    protected function validateParameters(): void
    {
        if (!class_exists($this->container)) {
            throw new \LogicException("Token Container isn't a valid class: {$this->container}");
        }

        $reflection = new \ReflectionClass($this->container);
        $constructor = $reflection->getConstructor();
        if ($constructor->isPrivate() || $constructor->isProtected()) {
            throw new \LogicException("Can't create an object with constructor not public. ({$this->container})");
        }

        foreach ($constructor->getParameters() as $param) {
            if (4 < ($position = $param->getPosition())) {
                continue;
            }

            $this->validateParamType(
                $param, 2 < $position ? 'string' : 'int'
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

    public function factory(
        string $value, int $position, int $lineno, int $column
    ) {
        $callback = $this->tokenBuilder;

        return $callback($value, $position, $lineno, $column);
    }

    public function __invoke(
        string $value, int $position, int $lineno, int $column
    ) {
        return $this->factory($value, $position, $lineno, $column);
    }

    protected function createTokenBuilder(): callable
    {
        return match ($this->container) {
            'value' => fn ($value, $p, $l, $c) => $this->reserved ? $value : $this->tokenName,
            'array' => fn ($value, $position, $lineno, $column) => [
                'token_name' => $this->tokenName,
                'value' => $value,
                'pos' => $position,
                'lineno' => $lineno,
                'col' => $column,
            ],
            \stdClass::class => fn ($value, $position, $lineno, $column) => (object) [
                'token_name' => $this->tokenName,
                'value' => $value,
                'pos' => $position,
                'lineno' => $lineno,
                'col' => $column,
            ],
            default => $this->createUserTokenBuilder(),
        };
    }

    protected function createUserTokenBuilder(): callable
    {
        $this->validateParameters();
        $totalOfParams = (new \ReflectionClass($classname = $this->container))
            ->getConstructor()
            ->getNumberOfParameters()
        ;

        return match ($totalOfParams) {
            1 => fn ($v, $p, $ln, $c) => new $classname($this->tokenName),
            2 => fn ($v, $p, $ln, $c) => new $classname($this->tokenName, $v),
            3 => fn ($v, $p, $ln, $c) => new $classname($this->tokenName, $v, $p),
            4 => fn ($v, $p, $ln, $c) => new $classname($this->tokenName, $v, $p, $ln),
            5 => fn ($v, $p, $ln, $c) => new $classname($this->tokenName, $v, $p, $ln, $c),
            default => throw new \LogicException(sprintf('Invalid number of required arguments! (required: %d)', $totalOfParams)),
        };
    }
}
