<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer\Pattern;

use Iterator;
use \RegexIterator;

class TokenRuleIterator extends RegexIterator
{
    readonly public TokenRulePattern $tokenRulePattern;
    readonly protected callable $callback;

    public function __construct(
        \IteratorIterface $iterator,
        TokenRulePattern $tokenRulePattern,
        callable $callback,
    ) {
        $this->callback = $callback;
        $this->tokenRulePattern = $tokenRulePattern;

        parent::__construct($iterator, $tokenRulePattern->pattern, RegexIterator::ALL_MATCHES, RegexIterator::USE_KEYS, PREG_OFFSET_CAPTURE);
    }
}