<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol;

class ClassSymbol implements SymbolInterface
{
    use SymbolTrait;

    public const CODE_BAR = 92;
    public const CODE_NEG_SET = 94;
    public const CODE_MINUS = 45;

    public static function newFrom(
        string $symbol,
        bool $starRepeat = false,
        bool $plusRepeat = false,
        bool $maybeExist = false,
        int $start = 1,
        int $end = 1,
    ): self {
        if (empty($symbol)) {
            throw new \LogicException('ClassSymbol cannot be empty', 503);
        }

        if ('.' === $symbol) {
            $chars = range(0, 255);
            foreach ($chars as $key => $code) {
                $chars[$key] = chr($code);
            }

            $result = implode('', $chars);

            return static::createSymbol($result, $starRepeat, $plusRepeat, $maybeExist, $start, $end);
        }

        $stack = [];
        $reverseSet = false;
        $append = false;
        foreach (str_split($symbol) as $key => $char) {
            $charCode = ord($char);
            if (0 === $key && static::CODE_NEG_SET === $charCode) {
                $reverseSet = true;
                continue;
            }

            if (static::CODE_BAR === $charCode) {
                $append = true;
                continue;
            }

            if ($append) {
                $append = false;
                $stack[] = $charCode;
                continue;
            }

            if (static::CODE_MINUS === $charCode) {
                if (0 > ($last = count($stack) - 1)) {
                    throw new \LogicException("Could not process pattern that starts with \"-\": '{$set}'", 503);
                }

                $aux = $stack[$last];
                $stack[$last] = true;
                $stack[] = $aux;
                continue;
            }

            $stack[] = $charCode;
        }

        $result = static::processClass($stack, $reverseSet);
        foreach ($result as $key => $code) {
            $result[$key] = chr($code);
        }

        $result = array_unique($result);
        sort($result);
        $result = array_values($result);
        $result = implode('', $result);

        return static::createSymbol($result, $starRepeat, $plusRepeat, $maybeExist, $start, $end);
    }

    public static function processClass(array $originalSet, bool $reverse = false): array
    {
        $size = count($originalSet);
        $stack = [];
        for ($k = 0; $k < $size; ++$k) {
            $curr = $originalSet[$k];
            if (true !== $curr) {
                $stack[] = $curr;
                continue;
            }

            if ($k + 2 === $size) {
                throw new \LogicException('Missing continuation in set. Ex.: [A-]');
            }

            if (true === $originalSet[$k + 2]) {
                throw new \LogicException("Ranges must no be sequenced. Ex.:\n wrong: [A-C-E].\n correct: [A-CE-G]");
            }

            $start = $originalSet[$k + 1];
            $end = $originalSet[$k + 2];

            if ($start >= $end) {
                throw new \LogicException("Range must be from lowest char code to greater. got (start: {$start}, end: {$end})");
            }

            $stack = array_merge($stack, range($start, $end));
            $k += 2;
        }

        if ($reverse) {
            return array_diff(range(0, 255), $stack);
        }

        return $stack;
    }
}
