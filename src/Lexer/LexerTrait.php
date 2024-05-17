<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Lexer;

use JB255\PHPCompBuilder\Lexer\Traits\BuildAndProcessTokenIteratorsTrait;
use JB255\PHPCompBuilder\Lexer\Traits\LoadTokenRulePatternsTrait;

/**
 * Trait that defines a Lexer for a lexer engine.
 *
 * This trait provides a base implementation for lexers used in the lexer engine.
 * It defines methods for initializing the lexer, retrieving the current token,
 * moving to the next token, and checking for the end of the token stream.
 *
 * ## Example:
 *
 * ```php
 * #[TokenRulePattern('identifier', '^[a-zA-Z_]+', MyTokenContainer::class)]
 * // ... outros attributos.
 * class SimpleLexer {
 *     use JB255\PHPCompBuilder\Lexer\LexerTrait;
 *
 *     public function ignorePattern(): string {
 *         // Ignorar espaços em branco e comentários
 *         return '\s+|#.*';
 *     }
 * }
 *
 * $lexer = new SimpleLexer(new \ArrayIterator(file('my_code.php')));
 *
 * foreach ($lexer as $token) {
 *     echo $token->getType() . ': ' . $token->getValue() . PHP_EOL;
 * }
 * ```
 */
trait LexerTrait
{
    use LoadTokenRulePatternsTrait;
    use BuildAndProcessTokenIteratorsTrait;

    /**
     * Initializes the lexer.
     *
     * This method must be called in the constructor of the implementing class.
     * It takes an iterator for the stream to be lexed, the filename of the stream,
     * and an optional array of additional token patterns.
     *
     * @param \Iterator $streamIterator the iterator for the stream to be lexed
     * @param string    $filename       the filename of the stream
     * @param array     $patterns       an optional array of additional token patterns
     */
    public function initLexer(
        \Iterator $streamIterator, string $filename, array $patterns = []
    ) {
        $this->initTokenPatterns($patterns);
        $this->initTokenStream($streamIterator, $filename);
        $this->tokenStream = $this->buildTokenStream();
    }

    /**
     * Defines the pattern for characters to be ignored during lexing.
     *
     * This method must be implemented by the descendant class and should return a regular expression
     * that matches the characters to be ignored.
     *
     * @return string the regular expression for characters to be ignored
     */
    abstract public function ignorePattern(): string;

    /**
     * Returns the current token from the token stream.
     *
     * This method returns the current token from the token stream. The returned value can be either
     * a TokenInterface object or an integer representing the type of the token.
     *
     * @return TokenInterface|string|array|\stdClass the current token from the token stream
     */
    public function current(): mixed
    {
        return $this->tokenStream->current();
    }

    /**
     * Moves the lexer to the next token in the stream.
     *
     * This method advances the internal pointer of the token stream to the next token.
     */
    public function next(): void
    {
        $this->tokenStream->next();
    }

    /**
     * Returns the current key of the token stream.
     *
     * This method returns the current key of the token stream, which represents the number of tokens
     * processed so far.
     *
     * @return int the current key of the token stream
     */
    public function key(): mixed
    {
        return $this->tokenStream->key();
    }

    /**
     * Rewinds the lexer to the first token in the stream.
     *
     * This method resets the internal pointer of the token stream to the beginning.
     */
    public function rewind(): void
    {
        $this->tokenStream->rewind();
    }

    /**
     * Checks whether the end of the token stream has been reached.
     *
     * This method returns true if the end of the token stream has been reached, false otherwise.
     *
     * @return bool true if the end of the token stream has been reached, false otherwise
     */
    public function valid(): bool
    {
        return $this->tokenStream->valid();
    }
}
