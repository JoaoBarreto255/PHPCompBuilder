<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol;

class UniqueSymbol implements SymbolInterface
{
    use SymbolTrait;

    public static function newFrom(
        string $symbol,
        bool $starRepeat = false,
        bool $plusRepeat = false,
        bool $maybeExist = false,
        int $start = 1,
        int $end = 1,
    ): static {
        if (1 !== ($len = strlen($symbol))) {
            throw new \InvalidArgumentException("Unique symbol must have length 1, found {$len}", 1);
        }

        return static::createSymbol($symbol, $starRepeat, $plusRepeat, $maybeExist, $start, $end);
    }
}
