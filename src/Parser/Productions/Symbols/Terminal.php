<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Parser\Productions\Symbols;

class Terminal extends AbstractSymbol implements SymbolInterface
{
    public const SYMBOL_PATTERN = '/^\'([[:punct:]]+|\w+)\'$/';

    public function __toString(): string
    {
        return sprintf("'%s'", $this->value);
    }
}
