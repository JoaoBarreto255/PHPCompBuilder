<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Parser\Productions;

use JB255\PHPCompBuilder\Parser\Productions\Symbols\ClassTerminal;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\NonTerminal;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\SymbolInterface;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\Terminal;

/**
 * Container for Rule of one production.
 */
class ProductionRule
{
    public static string $bodyPattern = '';
    /**
     * @var SymbolInterface[]
     */
    public readonly array $rule;

    public function __construct(
        public readonly NonTerminal $header,
        string $rule
    ) {
        if (false !== preg_match_all(static::buildBodyPattern(), $rule, $matches, PREG_OFFSET_CAPTURE)) {
            $this->rule = $this->processMatches($matches);

            return;
        }

        throw new \InvalidArgumentException("Rule is on wrong pattern. It must be in format: '(NonTerminal:) ", 1);
    }

    private static function buildBodyPattern(): string
    {
        if (static::$bodyPattern) {
            return static::$bodyPattern;
        }

        $pattern = trim(Terminal::SYMBOL_PATTERN, '/');
        $pattern = trim($pattern, '^$');
        static::$bodyPattern = '(?<term>'.$pattern.')';

        $pattern = trim(NonTerminal::SYMBOL_PATTERN, '/');
        $pattern = trim($pattern, '^$');
        static::$bodyPattern .= '|(?<nterm>'.$pattern.')';

        $pattern = trim(ClassTerminal::SYMBOL_PATTERN, '/');
        $pattern = trim($pattern, '^$');
        static::$bodyPattern .= '|(?<cterm>'.$pattern.')';
        static::$bodyPattern = sprintf('/^((%s)\s*)*$/', static::$bodyPattern);

        return static::$bodyPattern;
    }

    protected function processMatches(array $matches): array
    {
        $result = array_merge(
            $this->processMatchesType($matches['term'] ?? [], Terminal::class),
            $this->processMatchesType($matches['nterm'] ?? [], NonTerminal::class),
            $this->processMatchesType($matches['cterm'] ?? [], ClassTerminal::class),
        );
        $result = array_values($result);

        uasort($result, fn (array $a, array $b) => $a[1] - $b[1]);

        return array_map('current', $result);
    }

    protected function processMatchesType(array $matches, string $classname): array
    {
        return array_map(
            fn (array $match): array => [new $classname($match[0]), $match[1]],
            $matches
        );
    }

    public function __toString()
    {
        return implode(' ', $this->rule);
    }
}
