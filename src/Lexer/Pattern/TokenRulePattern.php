<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer\Pattern;

use Attribute;
use RegexIterator;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION)]
class TokenRulePattern
{
    readonly private string $pattern;
    readonly private bool $reserved;

    public function __construct(
        string $pattern,
        bool $reserved=false
    ) {
        if (empty($pattern)) {
            throw new \LogicException("Pattern must not be empty");
        }

        $this->reserved = $reserved;
        $this->pattern = $pattern;
    }

    public function buildIterator(\Iterator $input, callable $callback): TokenRuleIterator
    {
        return new TokenRuleIterator($this, $input, $callback);
    }
}