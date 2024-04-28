<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Lexer\Pattern\Exception;

class InvalidParameterTypeException extends \LogicException
{
    public function __construct(
        public readonly string $functionName,
        public readonly string $parameterName,
        public readonly string $expectedType,
        public readonly ?string $currentType = null,
    ) {
        $message = sprintf('"%s" function parameter must be or contain type "%s".', $functionName, $expectedType);
        if ($currentType) {
            $message = sprintf('%s Founded: %s', $message, $currentType);
        }

        parent::__construct($message);
    }
}
