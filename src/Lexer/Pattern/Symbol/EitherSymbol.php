<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol;

class EitherSymbol extends SymbolAbstract
{
    public static function newFrom(
        SymbolAbstract $right,
        SymbolAbstract $left,
        bool $starRepeat = false,
        bool $plusRepeat = false,
        bool $maybeExist = false,
        int $start = 1,
        int $end = 1,
    ): static {
        if (0 === strlen($sequence)) {
            throw new \LogicException("GroupSequenceSymbol can't receive more than one", 1);
        }

        return static::createSymbol([$right, $left], $starRepeat, $plusRepeat, $maybeExist, $start, $end);
    }
}
