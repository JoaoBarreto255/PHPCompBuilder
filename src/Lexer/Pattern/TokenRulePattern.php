<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer\Pattern;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
readonly class TokenRulePattern
{
    public string $pattern;

    public function __construct(
        string $pattern,
        public bool $reserved=false
    ) {
        if (empty($pattern)) {
            throw new \LogicException("Pattern must not be empty");
        }

        if (0 !== strpos($pattern, '/')) {
            $pattern = '/' . $pattern;
        }

        if ('/' !== substr($pattern, -1) || '\\' === substr($pattern, -1, 1)) {
            $pattern .= '/';
        }

        $this->pattern = $pattern;
    }
}