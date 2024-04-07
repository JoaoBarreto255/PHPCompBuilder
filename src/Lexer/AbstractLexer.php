<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer;

use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\TokenRuleIterator;
use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\TokenRulePattern;

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

    public function __construct(
        readonly private \Iterator $streamIterator,
        readonly public string $filename
    ) {
        $this->patterns = $this->getTokenRuleFromMethods();
        $this->tokenStream = $this->buildTokenStream();
    }

    /** return regex from caracteres to be ignored */
    abstract public function ignorePattern(): string;

    /**
     * returns current token.
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

    private function peekRightToken(array &$iterators): ?\stdClass
    {
        $result = null;
        foreach ($iterators as $key => $iteratorData) {
            $func = $iteratorData['method'];
            $iterator = $iteratorData['iterator'];

            // exlude any invalid tokens between another big one.
            while ($iterator->valid() && $iterator->key() < $this->col) {
                $iterator->next();
            }

            if (!$iterator->valid()) {
                unset($iterators[$key]);
                continue;
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
            if (null === $result || $new->len > $result->len) {
                $result = $new;
                continue;
            }
            if (null !== $result && $new->len < $result->len) {
                continue;
            }
            if ($iterator->tokenRulePattern->reserved) {
                $result = $new;
            }
        }

        return $result;
    }

    private function buildTokenStream(): \Generator
    {
        foreach ($this->streamIterator as $lineno => $line) {
            $this->input = $line;
            $this->lineno = $lineno;
            $iterators = $this->factoryIteratorsFromInput();

            $this->col = 0;
            while (true) {
                if ($tokenData = $this->peekRightToken($iterators)) {
                    $this->val = $tokenData->value;
                    $method = $tokenData->func;

                    if ($result = $this->{$method}()) {
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
     */
    private function factoryIteratorsFromInput(): array
    {
        $iterators = $this->patterns;
        foreach ($iterators as $key => $pattern) {
            $pattern['iterator'] = new TokenRuleIterator($this->input, $pattern['tokenRule']);
            unset($pattern['tokenRule']);
            $iterators[$key] = $pattern;
        }

        return $iterators;
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
     * @return array[] - methods with pattern to be processed
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
                    'tokenRule' => $rule->newInstance(),
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
