<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Parser\Productions\Symbols;

class NonTerminal extends AbstractSymbol implements SymbolInterface
{
    public const SYMBOL_PATTERN = '/^[[:alpha:]][\w_]*$/';

    public function __toString(): string
    {
        return $this->value;
    }
}
