<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Lexer\Traits;

use JB255\PHPCompBuilder\Lexer\InvalidCharacterException;
use JB255\PHPCompBuilder\Lexer\Pattern\TokenRuleIterator;
use JB255\PHPCompBuilder\Lexer\Pattern\TokenRulePattern;
use JB255\PHPCompBuilder\Lexer\Pattern\TokenState;

trait BuildAndProcessTokenIteratorsTrait
{
    use LexerCurrentStateGettersTrait;

    private ?string $filename = null;
    private ?\Iterator $streamIterator = null;

    protected ?\Generator $tokenStream = null;

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

    protected function initTokenStream(\Iterator $streamIterator, string $filename): self
    {
        $this->filename = $filename;
        $this->streamIterator = $streamIterator;
        $this->tokenStream = $this->buildTokenStream();

        return $this;
    }

    private function buildTokenStream(): \Generator
    {
        if (null === $this->filename || null === $this->streamIterator) {
            throw new \LogicException("You must init tokenStream before!", 1); 
        }

        foreach ($this->streamIterator as $lineno => $line) {
            $this->setLine($line)
                ->setLineno($lineno)
                ->factoryIteratorsFromLine()
                ->resetColumn();

            while (true) {
                if ($tokenData = $this->peekRightToken()) {
                    $this->setValue($tokenData->value);

                    if ('__ignoreToken' !== $tokenData->tokenRule->tokenName 
                        && $result = $tokenData->getTokenFactory()(
                            $this->value(), $this->position(), $this->lineno(), $this->column()
                        )
                    ) {
                        yield $result;
                    }

                    $this->setValue('')
                        ->increasePosition($tokenData->len)
                        ->increaseColumn($tokenData->len);

                    continue;
                }

                if (empty($iterators)) {
                    break;
                }

                $this->throwInvalidCharacterException();
            }

            if (strlen($this->line()) < $this->column()) {
                $this->throwInvalidCharacterException();
            }
        }
    }

    protected function throwInvalidCharacterException(): void
    {
        throw new InvalidCharacterException(
            $this->filename, $this->line()[$this->column()], $this->position(), $this->lineno(), $this->column()
        );
    }
}