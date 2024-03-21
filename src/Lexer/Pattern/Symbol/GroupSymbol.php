<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol;

class GroupSymbol implements SymbolInterface
{
    use SymbolTrait;

    public static function newFrom(
        array $sequence,
        bool $starRepeat = false,
        bool $plusRepeat = false,
        bool $maybeExist = false,
        int $start = 1,
        int $end = 1,
    ): static {
        if (0 === count($sequence)) {
            throw new \InvalidArgumentException("Empty group symbol!", 1);
        }

        return static::createSymbol($sequence, $starRepeat, $plusRepeat, $maybeExist, $start, $end);
    }

    public function append(SymbolInterface $symbol): static
    {
        $sequence = $this->value;

        $sequence[] = $symbol;

        return new static($sequence, $this->start, $this->end);
    }
}
