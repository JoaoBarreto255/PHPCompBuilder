<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer;

use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\TokenRuleIterator;
use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\TokenRulePattern;

abstract class AbstractLexer
{
    private array $patterns;
    private string $currentInput = '';

    public function __construct(
        readonly private \Iterator $streamIterator
    ) {
        $this->patterns = $this->getTokenRuleFromMethods();
    }

    /** return regex from caracteres to be ignored */
    abstract public function ignorePattern(): string;

    public function getToken()
    {
    }

    private function factoryIteratorsFromInput(): array
    {
        $iterators = $this->patterns;
        foreach ($iterators as $key => $pattern) {
            $pattern['iterator'] = new TokenRuleIterator($this->currentInput, $pattern['rule']);
            unset($pattern['rule']);
            $iterators[$key] = $pattern;
        }

        return $iterators;
    }

    /**
     * @return array - methods with pattern to be processed
     */
    private function getTokenRuleFromMethods(): array
    {
        $reflection = new \ReflectionClass($this);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PROTECTED | \ReflectionMethod::IS_PUBLIC);

        $result = [];
        foreach ($methods as $reflMethod) {
            if (empty($tokenRules = $reflMethod->getAttributes(TokenRulePattern::class))) {
                continue;
            }

            $methodName = $reflMethod->getName();
            foreach ($tokenRules as $rule) {
                $result[] = [
                    'method' => $methodName,
                    'tokenRule' => $rule,
                ];
            }
        }

        if ($result) {
            return $result;
        }

        throw new \LogicException('Error no methods to fetch pattern', 1);
    }
}
