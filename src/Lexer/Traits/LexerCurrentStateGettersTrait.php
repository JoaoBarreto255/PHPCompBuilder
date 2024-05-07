<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Lexer\Traits;

trait LexerCurrentStateGettersTrait
{
    private string $_line = '';
    private string $_val = '';
    private int $_pos = 0;
    private int $_lineno = 0;
    private int $_col = 0;

    /**
     * Returns current string processed.
     */
    final public function value(): string
    {
        return $this->_val;
    }

    final public function setValue(string $value): self
    {
        $this->_val = $value;

        return $this;
    }

    /**
     * Current token position in file.
     */
    final public function position(): int
    {
        return $this->_pos;
    }

    final public function increasePosition(int $amount): self
    {
        $this->_pos += $amount;

        return $this;
    }

    final public function resetPosition(): self
    {
        $this->_pos = 0;

        return $this;
    }

    /**
     * Position in line where current token is.
     */
    final public function column(): int
    {
        return $this->_col;
    }

    final public function increaseColumn(int $amount): self
    {
        $this->_col += $amount;

        return $this;
    }

    final public function resetColumn(): self
    {
        $this->_col = 0;

        return $this;
    }

    /**
     * Number from current token.
     */
    final public function lineno(): int
    {
        return $this->_lineno;
    }

    final public function setLineno(int $lineno): self
    {
        $this->_lineno = $lineno;

        return $this;
    }

    final public function line(): string
    {
        return $this->_line;
    }

    final public function setLine(string $line): self
    {
        $this->_line = $line;

        return $this;
    }
}