<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Parser\Productions;

use JB255\PHPCompBuilder\Parser\Productions\Symbols\ClassTerminal;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\NonTerminal;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\SymbolInterface;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\Terminal;
use RuntimeException;

/**
 * Container for Rule of one production.
 */
class ProductionRule
{
    /**
     * @var SymbolInterface[]
     */
    public readonly array $rule;

    public function __construct(
        public readonly NonTerminal $header,
        string $rule
    ) {
        $result = [];
        if (!empty($rule = trim($rule))) {
            $valuesArray = preg_split('/\s+/', $rule) ?: [$rule];
            foreach ($valuesArray as $value) {
                $result[] = $this->processValue($value);
            }
        }

        $this->rule = $result;
    }

    protected function processValue(string $value): SymbolInterface
    {
        if ($symbol = $this->trySymbol(NonTerminal::class, $value)) {
            return $symbol;
        }

        if ($symbol = $this->trySymbol(ClassTerminal::class, $value)) {
            return $symbol;
        }

        if ($symbol = $this->trySymbol(Terminal::class, $value)) {
            return $symbol;
        }

        throw new RuntimeException("Unrecoginized symbol in rule: $value");
    }

    public function trySymbol(string $classname, string $value): SymbolInterface|false
    {
        try {
            return new $classname($value);
        } catch (\Exception|\Error) {
            return false;
        }
    }

    public function __toString()
    {
        return implode(' ', $this->rule);
    }
}
