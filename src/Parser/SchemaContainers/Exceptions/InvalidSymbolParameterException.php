<?php

namespace JB255\PHPCompBuilder\Parser\SchemaContainers\Exceptions;

class InvalidSymbolParameterException extends \Exception
{
    public function __construct(
        public readonly string $productionName,
        public readonly string $parameterName,
        public readonly string $methodName,
    ) {
        parent::__construct(sprintf(
            'Parameter "%s" from method "%s::%s", has invalid type added. Expected string types for terminals and Nonterminals',
            $parameterName,
            $productionName,
            $methodName,
        ));
    }
}
