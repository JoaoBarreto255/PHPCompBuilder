<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol;

class EitherSymbol implements SymbolInterface
{
    use SymbolTrait;

    public static function newFrom(
        SymbolInterface $right,
        SymbolInterface $left,
        bool $starRepeat = false,
        bool $plusRepeat = false,
        bool $maybeExist = false,
        int $start = 1,
        int $end = 1,
    ): SymbolInterface {
        # if is not a sequence of symbols
        if ($right instanceof GroupSymbol || $left instanceof GroupSymbol || $right instanceof static || $left instanceof static || !$right->happenOneTime() || !$left->happenOneTime()) {
            return static::createSymbol([$left, $right], $starRepeat, $plusRepeat, $maybeExist, $start, $end);
        }

        return ClassSymbol::newFrom($left->value . $right->value, $starRepeat, $plusRepeat, $maybeExist, $start, $end);
    }

    public function getRightSymbol(): SymbolInterface
    {
        return $this->value[1];
    }

    public function getLeftSymbol(): SymbolInterface
    {
        return $this->value[0];
    }
}
