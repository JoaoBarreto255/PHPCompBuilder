<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Lexer\Traits;

use JB255\PHPCompBuilder\Lexer\Pattern\TokenRuleIterator;
use JB255\PHPCompBuilder\Lexer\Pattern\TokenRulePattern;

trait FactoryIteratorsFromLineTrait
{
    use LexerCurrentStateGettersTrait;
    /**
     * @var JB255\PHPCompBuilder\Lexer\Pattern\TokenRuleIterator[]
     */
    private array $iterators = [];

    /**
     * build for current line TokenRuleIterator.
     */
    protected function factoryIteratorsFromLine(): self
    {
        $this->iterators = array_map(
            fn(TokenRulePattern $trp) => new TokenRuleIterator(
                $this->line(), $trp
            ), $this->patterns
        );

        return $this;
    }
}