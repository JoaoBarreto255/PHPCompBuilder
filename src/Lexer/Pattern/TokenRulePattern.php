<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Lexer\Pattern;

use JB255\PHPCompBuilder\Lexer\Pattern\Exception\InvalidParameterTypeException;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
readonly class TokenRulePattern
{
    public string $pattern;

    public function __construct(
        public string $tokenName,
        string $pattern,
        public string $container = 'array',
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
        $this->validateParameters($container);
    }

    protected function validateParameters(string $container): void
    {
        if ('array' === $container || \stdClass::class === $container) {
            return;
        }
        if (!class_exists($container)) {
            throw new \LogicException("Token Container isn't a valid class: $container");
        }

        $reflection = new \ReflectionClass($container);
        $constructor = $reflection->getConstructor();
        if ($constructor->isPrivate() || $constructor->isProtected()) {
            throw new \LogicException("Can't create an object with constructor not public. ($container)");
        }
        if (0 === $constructor->getNumberOfParameters()) {
            throw new \LogicException(sprintf('Container "%s" must have at least one parameter!', $container));
        }

        if (4 < $constructor->getNumberOfRequiredParameters()) {
            throw new \LogicException('Too many parameters required for token instantiation!');
        }

        foreach ($constructor->getParameters() as $param) {
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

    public function createToken(
        string $value, int $positon, int $lineno, int $column
    ) {
        if (\in_array($this->container, [
            'array',
            \stdClass::class,
            \ArrayObject::class,
            \ArrayIterator::class,
        ], true)) {

            $data = [
                'value' => $value,
                'pos' => $positon,
                'lineno' => $lineno,
                'col' => $column,
            ];

            return match ($this->container) {
                'array' => $data,
                \stdClass::class => (object) $data,
                \ArrayObject::class => new \ArrayObject($data),
                \ArrayIterator::class => new \ArrayIterator($data),
            };
        }

        $classname = $this->container;
        $totalOfArguments = (new \ReflectionClass($classname))
            ->getConstructor()
            ->getNumberOfRequiredParameters();

        return match ($totalOfArguments) {
            1 => new $classname($value),
            2 => new $classname($value, $positon),
            3 => new $classname($value, $positon, $lineno),
            4 => new $classname($value, $positon, $lineno, $column),
        };
    }
}
