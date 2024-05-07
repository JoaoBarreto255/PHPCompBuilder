<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Lexer\Traits;

use JB255\PHPCompBuilder\Lexer\Pattern\TokenRulePattern;

trait LoadTokenRulePatternsTrait
{
    private array $patterns = [];
    private static array $lexerRules = [];

    protected function initTokenPatterns(array $foreignPatterns = []): self
    {
        $this->patterns = $foreignPatterns ?: $this->getTokenRuleFromClass();

        return $this;
    }

    /**
     * Build for lexer list of rules to apply to each line.
     *
     * @return \JB255\PHPCompBuilder\Lexer\Pattern\TokenRulePattern[] - methods with pattern to be processed
     */
    protected function getTokenRuleFromClass(): array
    {
        if (count(static::$lexerRules)) {
            return static::$lexerRules;
        }

        $reflection = new \ReflectionClass($this);
        if (empty($attributes = $reflection->getAttributes(TokenRulePattern::class))) {
            throw new \LogicException("Missing token attributes on lexer");
        }

        if (!method_exists($this, 'ignorePattern')) {
            throw new \LogicException('There\'s not such method "ignorePattern" in this class!');
        }

        if ($this->ignorePattern()) {
            $attributes = array_map(fn(\ReflectionAttribute $attr) => $attr->newInstance(), $attributes);
            $attributes[] = new TokenRulePattern(
                '__ignoreToken', $this->ignorePattern()
            );
        }

        $attributesNames = array_map(fn(TokenRulePattern $trp) => $trp->tokenName, $attributes);

        static::$lexerRules = array_combine($attributesNames, $attributes);

        return $attributes;
    }
}