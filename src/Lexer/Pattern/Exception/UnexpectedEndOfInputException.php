<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Exception;

class UnexpectedEndOfInputException extends UnexpectedSymbolException
{
    public function __construct(string $pattern, string $symbol)
    {
        parent::__construct($pattern, $symbol, strlen($pattern));
    }

    public function factoryMessage(): string
    {
        $position_str = str_pad('\x21\x91', $this->position, ' ', STR_PAD_LEFT);

        $symbol_str = '';
        if ($this->symbol) {
            $symbol_str = ', waiting for "'
                .$this->symbol
                .'"';
        }

        return <<<ERROR
        Unexpected end of input at {$this->position}{$symbol_st}:
            {$this->pattern}
            {$position_str}
        ERROR;
    }
}
