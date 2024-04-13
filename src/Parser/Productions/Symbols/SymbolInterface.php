<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Parser\Productions\Symbols;

interface SymbolInterface
{
    public const SYMBOL_PATTERN = '';

    public function __construct(string $value);

    public function __toString(): string;
}
