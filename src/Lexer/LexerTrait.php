<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Lexer;

use JB255\PHPCompBuilder\Lexer\Traits\BuildAndProcessTokenIteratorsTrait;
use JB255\PHPCompBuilder\Lexer\Traits\LoadTokenRulePatternsTrait;

/**
 * Classe abstrata que fornece uma estrutura básica para criar analisadores lexicais (lexers) em PHP.
 *
 * ## Como usar:
 *
 * 1. **Criar uma classe concreta que estenda AbstractLexer:**
 *    - Implemente o método `ignorePattern()` para retornar a expressão regular dos caracteres a serem ignorados.
 *    - Defina métodos adicionais para processar tokens específicos, utilizando a anotação `@TokenRulePattern` para associar padrões a esses métodos.
 *
 * 2. **Instanciar a classe:**
 *    - Forneça um `\Iterator` contendo as linhas do código de origem como argumento para o construtor.
 *    - Opcionalmente, forneça o nome do arquivo de origem como um segundo argumento.
 *
 * 3. **Iterar sobre os tokens:**
 *    - Utilize os métodos `current()`, `next()`, `key()`, `valid()`, `rewind()`, e outros métodos de iteração para processar os tokens gerados pelo lexer.
 *
 * ## Exemplo:
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
     * Must be added in __constructor.
     * 
     * @param $patterns used to inject other patterns.
     */
    public function initLexer(
        \Iterator $streamIterator, string $filename, array $patterns = [],
    ) {
        $this->initTokenPatterns($patterns);
        $this->initTokenStream($streamIterator, $filename);
        $this->tokenStream = $this->buildTokenStream();
    }

    /** 
     * @return string regex from caracteres to be ignored 
     */
    abstract public function ignorePattern(): string;

    /**
     * returns current token.
     *
     * @return TokenInterface|int
     */
    public function current(): mixed
    {
        return $this->tokenStream->current();
    }

    /**
     * Move cursor to next token.
     */
    public function next(): void
    {
        $this->tokenStream->next();
    }

    /**
     * Returns "key", number off tokens processed.
     */
    public function key(): mixed
    {
        return $this->tokenStream->key();
    }

    /**
     * rewind lexer to first token.
     */
    public function rewind(): void
    {
        $this->tokenStream->rewind();
    }

    /**
     * verify if stream of tokens finish.
     */
    public function valid(): bool
    {
        return $this->tokenStream->valid();
    }
}
