<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Lexer\Pattern;

use JB255\PHPCompBuilder\Lexer\TokenInterface;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
readonly class TokenRulePattern
{
    public string $pattern;

    public function __construct(
        string $pattern,
        public bool $reserved = false,
        public bool $caseInsensitive = false,
        public int|TokenInterface|null $id = null
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
