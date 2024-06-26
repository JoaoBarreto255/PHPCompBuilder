<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Lexer\Pattern;

class TokenRuleIterator extends \ArrayIterator
{
    public readonly string $input;
    public readonly TokenRulePattern $tokenRulePattern;

    public function __construct(string $input, TokenRulePattern $tokenRulePattern)
    {
        $this->input = $input;
        $this->tokenRulePattern = $tokenRulePattern;

        $results = [];
        if (false !== preg_match_all($tokenRulePattern->pattern, $input, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if (empty($match) || !is_array($match)) {
                    continue;
                }

                [$value, $key] = array_shift($match);
                $results[$key] = $value;
            }
        }

        parent::__construct($results);
    }
}
