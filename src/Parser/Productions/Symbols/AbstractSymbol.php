<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Parser\Productions\Symbols;

class AbstractSymbol
{
    final public function __construct(
        readonly public string $value
    ) {
        if (!$this instanceof SymbolInterface) {
            throw new \LogicException(sprintf('"%s" must implement interface "%s"', $this::class, SymbolInterface::class));
        }

        if (empty(static::SYMBOL_PATTERN) || false === ($total = preg_match(static::SYMBOL_PATTERN, $value))) {
            throw new \LogicException(sprintf('"%s" have invalid SYMBOL_PATTERN ("%s"). Redefine yours', $this::class, static::SYMBOL_PATTERN));
        }

        if (0 === $total) {
            throw new \InvalidArgumentException(sprintf('Symbol "%s" not match with pattern: r"%s"', $value, static::SYMBOL_PATTERN));
        }

        $this->extraValueValidation($value);
    }

    /**
     * Another type validations.
     *
     * @throws \InvalidArgumentException
     */
    protected function extraValueValidation(string $value): void
    {
    }
}
