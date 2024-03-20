<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol;

interface SymbolInterface
{
    public function symbolMaybeExistOrRepeat(): static;
    public function symbolMaybeRepeat(): static;
    public function symbolMayNotHappen(): static;
    public function symbolWillRepeatNTimes(int $times): static;
    public function symbolWillRepeatFromNToMTimes(int $n, int $m): static;
}