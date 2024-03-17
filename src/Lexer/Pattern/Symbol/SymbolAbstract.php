<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol;

abstract class SymbolAbstract
{
    // const TYPE_SYMBOL = 1;
    // const TYPE_SET = 2;
    // const TYPE_GROUP = 4;

    // const ARITY_WITHOUT = 8;
    // const ARITY_START = 16;
    // const ARITY_PLUS = 32;
    // const ARITY_WINDOWS = 64;

    // const SET_NO_VALID = 128;

    protected function __construct(
        readonly public array|string $value,
        readonly public int $begin = 1,
        readonly public false|int $end = 1,
    ) {
        if (empty($this->value)) {
            throw new \InvalidArgumentException('Property "value" must be not empty');
        }
        if (0 > $this->begin) {
            throw new \InvalidArgumentException('Property "begin" must be not negative!');
        }

        if (false !== $this->end && $this->begin >= $this->end) {
            throw new \InvalidArgumentException(sprintf('Property "end" must be false or greater or equals to "begin"! got: %d', $this->end));
        }
    }

    public static function createSymbol(
        array|string $symbol,
        bool $starRepeat = false,
        bool $plusRepeat = false,
        bool $maybeExist = false,
        int $start = 1,
        int $end = 1,
    ): static {
        if ($startRepeat) {
            return new self($symbol, 0, false);
        }

        if ($plusRepeat) {
            return new self($symbol, 1, false);
        }

        if ($maybeExist) {
            return new self($symbol, 0, 1);
        }

        return new self($symbol, $start, $end);
    }

    public function symbolMaybeExistOrRepeat(): static
    {
        return static::createSymbol($this->value, true);
    }

    public function symbolMaybeRepeat(): static
    {
        return static::createSymbol($this->value, plusRepeat: true);
    }

    public function symbolMayNotHappen(): static
    {
        return static::createSymbol($this->value, maybeExist: true);
    }

    public function symbolWillRepeatNTimes(int $times): static
    {
        return $this->symbolWillRepeatFromNToMTimes($times, $times);
    }

    public function symbolWillRepeatFromNToMTimes(int $n, int $m): static
    {
        return static::createSymbol($this->value, start: $n, end: $m);
    }
}
