<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Lexer\Pattern;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
readonly class TokenRulePattern
{
    public string $pattern;

    public function __construct(
        string $pattern,
        public bool $reserved = false,
        public bool $caseInsensitive = false
    ) {
        if (empty($pattern)) {
            throw new \LogicException('Pattern must not be empty');
        }

        if (0 !== strpos($pattern, '/')) {
            $pattern = '/'.$pattern;
        }

        if (!preg_match('/.*[^\\\]\/\w?$/', $pattern)) {
            $pattern .= '/';
        }

        if ($caseInsensitive) {
            $pattern .= 'i';
        }

        $this->pattern = $pattern;
    }
}
