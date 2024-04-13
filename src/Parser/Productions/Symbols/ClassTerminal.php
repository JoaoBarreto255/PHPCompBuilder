<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Parser\Productions\Symbols;

class ClassTerminal extends Terminal implements SymbolInterface
{
    public const SYMBOL_PATTERN = '/^\'[:alpha:](\w|\_)*(\\\[:alpha:](\w|\_)*)*\'$/i';

    protected function extraValueValidation(string $value): void
    {
        if (!class_exists($value)) {
            throw new \InvalidArgumentException("Value isn't a class");
        }
    }
}
