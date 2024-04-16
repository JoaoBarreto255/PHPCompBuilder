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
 * namespace App\Lexer;
 *
 * use JB255\PHPCompBuilder\Lexer\TokenInterface;
 * use JB255\PHPCompBuilder\Lexer\TokenTrait;
 *
 * class For implements TokenInterface
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
interface TokenInterface
{
    /**
     * Value tokenized from token.
     */
    public function value(): string|float|int;

    /**
     * Start token position in scanned file.
     */
    public function position(): int;

    /**
     * Number of current file line scanned.
     */
    public function lineno(): int;

    /**
     * Start of token in current line.
     */
    public function column(): int;

    /**
     * Scanned file.
     */
    public function filename(): ?string;
}
