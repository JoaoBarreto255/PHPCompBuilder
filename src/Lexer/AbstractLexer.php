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
 * class SimpleLexer extends AbstractLexer {
 *     public function ignorePattern(): string {
 *         // Ignorar espaços em branco e comentários
 *         return '\s+|#.*';
 *     }
 *
 *     #[TokenRulePattern('^[a-zA-Z_]+')]
 *     protected function processIdentifier(): Token {
 *         // Processar identificadores
 *         return new Token(Token::IDENTIFIER, $this->value());
 *     }
 *
 *     // ... outros métodos de processamento de tokens
 * }
 *
 * $lexer = new SimpleLexer(new \ArrayIterator(file('my_code.php')));
 *
 * foreach ($lexer as $token) {
 *     echo $token->getType() . ': ' . $token->getValue() . PHP_EOL;
 * }
 * ```
 *
 * ## Propriedades e Métodos Importantes:
 *
 * - **tokenStream:** Propriedade que contém o gerador de tokens (stream de tokens).
 * - **ignorePattern():** Método abstrato para definir a expressão regular de caracteres a serem ignorados.
 * - **buildTokenStream():** Método interno que constrói o gerador de tokens.
 * - **getTokenRuleFromMethods():** Método interno que obtém as regras de tokens a partir dos métodos da classe.
 * - **factoryIteratorsFromInput():** Método interno que cria iteradores para as regras de tokens aplicadas a uma linha de código.
 * - **throwInvalidCharacterException():** Método interno que lança uma exceção ao encontrar caracteres inválidos.
 *
 * ## Outros Métodos:
 *
 * - **current():** Retorna o token atual.
 * - **next():** Move o cursor para o próximo token.
 * - **key():** Retorna a chave do token atual (número de tokens processados).
 * - **rewind():** Redefine o lexer para o primeiro token.
 * - **valid():** Verifica se o fluxo de tokens terminou.
 * - **value():** Retorna a string atual processada.
 * - **position():** Retorna a posição do token atual no arquivo.
 * - **column():** Retorna a posição do token atual na linha.
 * - **lineNumber():** Retorna o número da linha do token atual.
 * - **line():** Retorna a linha atual sendo processada.
 */
abstract class AbstractLexer implements \Iterator
{
    use LoadTokenRulePatternsTrait;
    use BuildAndProcessTokenIteratorsTrait;

    public function __construct(
        \Iterator $streamIterator,
        string $filename,
        array $patterns = []
    ) {
        $this->initTokenPatterns($patterns);
        $this->initTokenStream($streamIterator, $filename);
        $this->tokenStream = $this->buildTokenStream();
    }

    /** return regex from caracteres to be ignored */
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
