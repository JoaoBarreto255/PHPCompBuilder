<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Lexer\Traits;

use JB255\PHPCompBuilder\Lexer\Pattern\TokenRuleIterator;
use JB255\PHPCompBuilder\Lexer\Pattern\TokenRulePattern;
use JB255\PHPCompBuilder\Lexer\Pattern\TokenState;

trait BuildAndProcessTokenIteratorsTrait
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

    private function peekRightToken(): ?TokenState
    {
        $result = null;
        foreach ($this->iterators as $key => $iterator) {
            // exlude any invalid tokens between another big one.
            while ($iterator->valid() && $iterator->key() < $this->column()) {
                $iterator->next();
            }

            if (!$iterator->valid()) {
                unset($this->iterators[$key]);
                continue;
            }

            // avoid process token not in current position.
            if ($iterator->key() > $this->column()) {
                continue;
            }

            $new = new TokenState($iterator->tokenRulePattern, $iterator->current());
            $iterator->next();
            if (!$result instanceof TokenState) {
                $result = $new;
                continue;
            }

            $result = match ($new->compare($result)) {
                1 => $new,
                default => $result,
            };
        }

        return $result;
    }
}