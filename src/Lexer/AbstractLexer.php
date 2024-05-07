<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Lexer;

use JB255\PHPCompBuilder\Lexer\Pattern\TokenState;
use JB255\PHPCompBuilder\Lexer\Traits\FactoryIteratorsFromLineTrait;
use JB255\PHPCompBuilder\Lexer\Traits\GetTokenRulesFromClassTrait;

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
    use GetTokenRulesFromClassTrait;
    use FactoryIteratorsFromLineTrait;

    private readonly array $patterns;
    protected ?\Generator $tokenStream = null;

    public function __construct(
        readonly private \Iterator $streamIterator,
        readonly public string $filename,
        array $patterns = []
    ) {
        $this->patterns = $patterns ?: $this->getTokenRuleFromClass();
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

    private function buildTokenStream(): \Generator
    {
        foreach ($this->streamIterator as $lineno => $line) {
            $this->setLine($line)
                ->setLineno($lineno)
                ->factoryIteratorsFromLine()
                ->resetColumn();

            while (true) {
                if ($tokenData = $this->peekRightToken()) {
                    $this->setValue($tokenData->value);

                    if ('__ignoreToken' !== $tokenData->tokenRule->tokenName 
                        && $result = $tokenData->tokenRule->createToken(
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
