<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol;

class GroupSymbol extends SymbolAbstract
{
    public static function newFrom(
        array $sequence,
        bool $starRepeat = false,
        bool $plusRepeat = false,
        bool $maybeExist = false,
        int $start = 1,
        int $end = 1,
    ): static
    {
        if (0 === strlen($sequence)) {
            throw new \LogicException("GroupSequenceSymbol can't receive more than one", 1);
        }

        return static::createSymbol($symbol, $starRepeat, $plusRepeat, $maybeExist, $start, $end);
    }
}