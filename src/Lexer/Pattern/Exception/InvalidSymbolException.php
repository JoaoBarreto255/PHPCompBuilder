<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Exception;

class InvalidSymbolException extends \Exception
{
    public string $pattern;

    public function __construct(string $message, string $pattern, int $code = 503)
    {
        $this->pattern = $pattern;

        parent::__construct($message, $code);
    }
}
