<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Lexer;

/**
 * Interface used in parser to registry and returns tokens.
 *
 * Usage:
 * ```php
 * <?php
 *
 * use JB255\PHPCompBuilder\Lexer\TokenInterface;
 * use JB255\PHPCompBuilder\Lexer\TokenTrait;
 *
 * readonly class For implements TokenInterface
 * {
 *      use TokenTrait;
 *
 *      public function __construct(string $value)
 *      {
 *          $this->initToken($value);
 *      }
 * }
 * ```
 */
trait TokenTrait
{
    private readonly string|float|int $_value;
    private readonly int $_pos;
    private readonly int $_lineno;
    private readonly int $_col;
    private readonly ?string $_filename;

    public function initToken(string|float|int $value, int $pos = 0, int $lineno = 0, int $col = 0, ?string $filename = null)
    {
        $this->_value = $value;
        $this->_pos = $pos;
        $this->_lineno = $lineno;
        $this->_col = $col;
        $this->_filename = $filename;
    }

    public function value(): string|float|int
    {
        return $this->_value;
    }

    public function position(): int
    {
        return $this->_pos;
    }

    public function lineno(): int
    {
        return $this->_lineno;
    }

    public function column(): int
    {
        return $this->_col;
    }

    public function filename(): ?string
    {
        return $this->_filename;
    }
}
