<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer;

use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\TokenRuleIterator;
use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\TokenRulePattern;

abstract class AbstractLexer implements \Iterator
{
    private readonly array $patterns;
    private string $input = '';
    private string $val = '';
    private int $pos = 0;
    private int $lineno = 0;
    private int $col = 0;
    protected ?\Generator $tokenStream = null;

    public function __construct(
        readonly private \Iterator $streamIterator,
        readonly public string $filename
    ) {
        $this->patterns = $this->getTokenRuleFromMethods();
        $this->tokenStream = $this->buildTokenStream();
    }

    /** return regex from caracteres to be ignored */
    abstract public function ignorePattern(): string;

    public function current()
    {
        return $this->tokenStream->current();
    }

    public function next(): void
    {
        $this->tokenStream->next();
    }

    public function key()
    {
        return $this->tokenStream->key();
    }

    public function rewind(): void
    {
        $this->tokenStream->rewind();
    }

    public function valid(): bool
    {
        return $this->tokenStream->valid();
    }

    protected function buildTokenStream(): \Generator
    {
        foreach ($this->streamIterator as $lineno => $line) {
            $this->input = $line;
            $this->lineno = $lineno;
            $iterators = $this->factoryIteratorsFromInput();

            $this->col = 0;
            $curr = null;
            while (true) {
                foreach ($iterators as $key => $iteratorData) {
                    $func = $iteratorData['method'];
                    $iterator = $iteratorData['iterator'];

                    if (!$iterator->valid()) {
                        unset($iterators[$key]);
                        continue;
                    }

                    // exlude any invalid tokens between another big one.
                    while ($iterator->key() < $this->col) {
                        $iterator->next();
                    }

                    // avoid process token not in current position.
                    if ($iterator->key() > $this->col) {
                        continue;
                    }

                    $new = new \stdClass();
                    $new->func = $func;
                    $new->value = $iterator->current();
                    $new->len = strlen($new->value);

                    $iterator->next();
                    if (null === $curr || $new->len > $curr->len) {
                        $curr = $new;
                        continue;
                    }

                    if ($iterator->tokenRulePattern->reserved) {
                        $curr = $new;
                    }
                }

                if (empty($iterators)) {
                    break;
                }

                if (null !== $curr) {
                    $this->val = $curr->value;
                    $method = $curr->func;

                    yield $this->{$method}();

                    $this->val = '';
                    $this->pos += $curr->len;
                    $this->col += $curr->len;
                    continue;
                }

                $this->throwInvalidCharacterException();
            }

            if ($this->streamIterator->valid()) {
                $this->throwInvalidCharacterException();
            }
        }
    }

    protected function throwInvalidCharacterException(): void
    {
        throw new InvalidCharacterException($this->filename, $this->input[$this->col], $this->pos, $this->lineno, $this->col);
    }

    private function factoryIteratorsFromInput(): array
    {
        $iterators = $this->patterns;
        foreach ($iterators as $key => $pattern) {
            $pattern['iterator'] = new TokenRuleIterator($this->input, $pattern['rule']);
            unset($pattern['rule']);
            $iterators[$key] = $pattern;
        }

        return $iterators;
    }

    protected function ignorePatternAction()
    {
        return null;
    }

    public function value(): string
    {
        return $this->val;
    }

    public function position(): int
    {
        return $this->pos;
    }

    public function column(): int
    {
        return $this->col;
    }

    public function lineNumber(): int
    {
        return $this->lineno;
    }

    public function line(): string
    {
        return $this->input;
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

        $result[] = [
            'method' => 'ignorePatternAction',
            'tokenRule' => new TokenRulePattern($this->ignorePattern()),
        ];

        if ($result) {
            return $result;
        }

        throw new \LogicException('Error no methods to fetch pattern', 1);
    }
}
