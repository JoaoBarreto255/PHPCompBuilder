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
     * Number used.
     */
    public function id(): int;
}
