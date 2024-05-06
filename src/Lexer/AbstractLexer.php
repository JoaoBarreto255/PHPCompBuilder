<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Lexer;

use JB255\PHPCompBuilder\Lexer\Pattern\TokenRuleIterator;
use JB255\PHPCompBuilder\Lexer\Pattern\TokenRulePattern;
use JB255\PHPCompBuilder\Lexer\Pattern\TokenState;

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
    private readonly array $patterns;
    private string $input = '';
    private string $val = '';
    private int $pos = 0;
    private int $lineno = 0;
    private int $col = 0;
    protected ?\Generator $tokenStream = null;

    protected static array $lexerRules = [];

    /**
     * @var JB255\PHPCompBuilder\Lexer\Pattern\TokenRuleIterator[]
     */
    private array $iterators = [];

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
            while ($iterator->valid() && $iterator->key() < $this->col) {
                $iterator->next();
            }

            if (!$iterator->valid()) {
                unset($this->iterators[$key]);
                continue;
            }

            // avoid process token not in current position.
            if ($iterator->key() > $this->col) {
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
            $this->input = $line;
            $this->lineno = $lineno;
            $this->iterators = $this->factoryIteratorsFromInput();

            $this->col = 0;
            while (true) {
                if ($tokenData = $this->peekRightToken()) {
                    $this->val = $tokenData->value;

                    if ('__ignoreToken' !== $tokenData->tokenRule->tokenName 
                        && $result = $tokenData->tokenRule->createToken(
                            $this->val, $this->pos, $this->lineno, $this->col
                        )
                    ) {
                        yield $result;
                    }

                    $this->val = '';
                    $this->pos += $tokenData->len;
                    $this->col += $tokenData->len;
                    continue;
                }

                if (empty($iterators)) {
                    break;
                }

                $this->throwInvalidCharacterException();
            }

            if (strlen($this->line()) < $this->col) {
                $this->throwInvalidCharacterException();
            }
        }
    }

    protected function throwInvalidCharacterException(): void
    {
        throw new InvalidCharacterException($this->filename, $this->input[$this->col], $this->pos, $this->lineno, $this->col);
    }

    /**
     * build for current line TokenRuleIterator.
     * @return \JB255\PHPCompBuilder\Lexer\Pattern\TokenRuleIterator[]
     */
    private function factoryIteratorsFromInput(): array
    {
        return array_map(
            fn(TokenRulePattern $trp) => new TokenRuleIterator(
                $this->input, $trp
            ), $this->patterns
        );
    }

    protected function ignorePatternAction()
    {
        return null;
    }

    /**
     * Returns current string processed.
     */
    public function value(): string
    {
        return $this->val;
    }

    /**
     * Current token position in file.
     */
    public function position(): int
    {
        return $this->pos;
    }

    /**
     * Position in line where current token is.
     */
    public function column(): int
    {
        return $this->col;
    }

    /**
     * Number from current token.
     */
    public function lineNumber(): int
    {
        return $this->lineno;
    }

    public function line(): string
    {
        return $this->input;
    }

    /**
     * Build for lexer list of rules to apply to each line.
     *
     * @return \JB255\PHPCompBuilder\Lexer\Pattern\TokenRulePattern[] - methods with pattern to be processed
     */
    private function getTokenRuleFromClass(): array
    {
        if (isset(static::$lexerRules[static::class])) {
            return static::$lexerRules[static::class];
        }

        $reflection = new \ReflectionClass($this);
        if (empty($attributes = $reflection->getAttributes(TokenRulePattern::class))) {
            throw new \LogicException("Missing token attributes on lexer");
        }

        $attributes = array_map(fn(\ReflectionAttribute $attr) => $attr->newInstance(), $attributes);
        $attributes[] = new TokenRulePattern(
            '__ignoreToken', $this->ignorePattern()
        );

        $attributesNames = array_map(fn(TokenRulePattern $trp) => $trp->tokenName, $attributes);

        static::$lexerRules[static::class] = array_combine($attributesNames, $attributes);

        return $attributes;
    }
}
