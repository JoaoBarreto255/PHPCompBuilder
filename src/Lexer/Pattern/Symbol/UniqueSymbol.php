<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol;

class UniqueSymbol extends SymbolAbstract
{
    public static function newFrom(
        string $symbol,
        bool $starRepeat = false,
        bool $plusRepeat = false,
        bool $maybeExist = false,
        int $start = 1,
        int $end = 1,
    ): static
    {
        if (1 !== strlen($symbol)) {
            throw new \LogicException("UniqueSymbol can't receive more than one", 1);
        }

        return static::createSymbol($symbol, $starRepeat, $plusRepeat, $maybeExist, $start, $end);
    }
}