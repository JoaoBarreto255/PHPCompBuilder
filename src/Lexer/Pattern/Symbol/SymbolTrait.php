<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol;

trait SymbolTrait
{
    protected function __construct(
        readonly public array|string $value,
        readonly public int $start = 1,
        readonly public false|int $end = 1,
    ) {
        if (empty($value)) {
            throw new \InvalidArgumentException('Property "value" must be not empty');
        }

        if (0 > $start) {
            throw new \InvalidArgumentException('Property "start" must be not negative!');
        }

        if (false !== $end && $start > $end) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Property "end" must be false or greater or equals to "start"! got: %d',
                    $end
                ));
        }
    }

    public static function createSymbol(
        array|string $symbol,
        bool $starRepeat = false,
        bool $plusRepeat = false,
        bool $maybeExist = false,
        int $start = 1,
        int|false $end = 1,
    ): static {
        if ($starRepeat) {
            return new static($symbol, 0, false);
        }

        if ($plusRepeat) {
            return new static($symbol, 1, false);
        }

        if ($maybeExist) {
            return new static($symbol, 0, 1);
        }

        if (false !== $end && $start > $end) {
            return new static($symbol, $start, $start);
        }

        return new static($symbol, $start, $end);
    }

    public function symbolMaybeExistOrRepeat(): static
    {
        return static::createSymbol($this->value, starRepeat: true);
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
