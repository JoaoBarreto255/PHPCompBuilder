<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Lexer;

class InvalidCharacterException extends \RuntimeException
{
    public function __construct(
        readonly public string $filename,
        readonly public string $char,
        readonly public int $position,
        readonly public int $lineNumber,
        readonly public int $column,
        int $code = 0
    ) {
        parent::__construct(
            sprintf('Invalid char "%s" at "%s:%d:%d"', $char, $filename, $lineNumber, $column),
            $code
        );
    }
}
