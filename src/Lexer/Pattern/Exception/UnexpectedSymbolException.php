<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Exception;

class UnexpectedSymbolException extends \LogicException
{
    public function __construct(
        readonly public string $pattern,
        readonly public string $symbol,
        readonly public int $position,
    ) {
        parent::__construct($this->factoryMessage(), $position);
    }

    public function factoryMessage(): string
    {
        $position_str = str_pad('\x21\x91', $this->position + 1, ' ', STR_PAD_LEFT);

        return <<<ERROR
        Unexpected "{$this->symbol}" at {$this->position}:
            {$this->pattern}
            {$position_str}
        ERROR;
    }
}
