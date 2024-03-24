<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer\Pattern;

use LogicException;

use Joaobarreto255\PhpCompBuilder\Lexer\Iterators\StringIterator;
use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Exception\UnexpectedEndOfInputException;
use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Exception\UnexpectedSymbolException;
use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol\ClassSymbol;
use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol\GroupSymbol;
use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol\UniqueSymbol;
use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol\EitherSymbol;

class BuilderHandler
{
    const CODE_BAR = 92;   // ASCII code for backslash (\)
    const CODE_STAR = 42;   // ASCII code for asterisk (*)
    const CODE_PLUS = 43;   // ASCII code for plus sign (+)
    const CODE_MAYBE = 63;  // ASCII code for question mark (?)
    const CODE_OPEN_CLASS = 91;  // ASCII code for left square bracket ([)
    const CODE_CLOSE_CLASS = 93; // ASCII code for right square bracket (])
    const CODE_OPEN_GROUP = 40; // ASCII code for left parenthesis (()
    const CODE_CLOSE_GROUP = 41; // ASCII code for right parenthesis ())
    const CODE_OPEN_BRACE = 123; // ASCII code for left curly brace ({)
    const CODE_CLOSE_BRACE = 125; // ASCII code for right curly brace (})
    const CODE_COMMA = 44;  // ASCII code for comma (,)
    const CODE_DOT = 46; // ASCII code for (.)

    private const REPEAT_MODS_MAP_CHECKS = [
        42 => 'symbolMaybeExistOrRepeat', // ASCII code for asterisk (*) /action
        43 => 'symbolMaybeRepeat',
        63 => 'symbolMayNotHappen',
    ];

    protected StringIterator $iterator;

    private array $symbols = [];

    private array $groupStacks = [];

    public function __construct(
        public readonly string $pattern
    ) {
        $this->iterator = new StringIterator($pattern);
    }

    public function processPattern()
    {
        while ($this->iterator->valid()) {
            $this->processAfterBarSymbol();

            $this->processClassPattern();

            $this->processRepeatSymbol();

            $this->processBracePattern();

            $this->processGroupPattern();

            $this->processSingleSymbol();
        }

        if ($this->groupStacks) {
            $this->throwUnexpectedEndOfInputException(')');
        }
    }

    public function processAfterBarSymbol(): void
    {
        if (static::CODE_BAR !== $this->iterator->currentCode()) {
            return;
        }

        $this->iterator->next();
        if (!$this->iterator->valid()) {
            $this->throwUnexpectedEndOfInputException();
        }

        $this->processSingleSymbol();
    }

    public function processRepeatSymbol(): void
    {
        if (empty($funcName = static::REPEAT_MODS_MAP_CHECKS[$this->iterator->currentCode()] ?? false)) {
            return;
        }

        $pos = $this->iterator->key();
        $char = $this->iterator->current();
        if (0 === count($this->symbols)) {
            $this->throwUnexpectedSymbolException($char);
        }

        $symbol = array_pop($this->symbols);
        $this->symbols[] = $symbol->{$funcName}();
        $this->iterator->next();
    }

    public function processClassPattern(): void
    {
        if (static::CODE_DOT === $this->iterator->currentCode()) { 
            $this->symbols[] = ClassSymbol::newFrom($this->iterator->current());
            $this->iterator->next();
        }

        if (static::CODE_CLOSE_CLASS === ($code = $this->iterator->currentCode())) {
            $this->throwUnexpectedSymbolException(']');
        }

        if (static::CODE_OPEN_CLASS !== $code) { return; }

        $startPos = $this->iterator->key() + 1;
        $this->iterator->next();
        $len = 0;
        while ($this->iterator->valid()) {
            $pos = $this->iterator->key();
            switch ($this->iterator->currentCode()) {
                case static::CODE_BAR:
                    $this->iterator->next();
                    break;
                case static::CODE_OPEN_CLASS:
                    throw new LogicException("Unexpected \"[\" at pattern position: $pos", $pos);

                case static::CODE_CLOSE_CLASS:
                    $this->symbols[] = ClassSymbol::newFrom(substr($this->pattern, $startPos, $len));
                    $this->iterator->next();
                    return;
                
                default:
                    # do nothing
                    break;
            }

            $len++;
            $this->iterator->next();
        }

        $this->throwUnexpectedEndOfInputException(']');
    }

    public function processBracePattern(): void
    {
        $code = $this->iterator->currentCode();
        $key = $this->iterator->key();

        if (static::CODE_CLOSE_BRACE === $code) { 
            $this->throwUnexpectedSymbolException(']');
        }

        if (static::CODE_OPEN_BRACE !== $code) {
            return;
        }

        $startPosition = $key + 1;
        $this->iterator->next();
        $numbers = [''];
        while ($this->iterator->valid()) {
            $pos = $this->iterator->key();
            switch ($this->iterator->current()) {
                case '{':
                    throw new LogicException("Unexpected \"{\" at position $pos", $pos);
                
                case '}':
                    if (0 === count($numbers)) {
                        throw new LogicException("Empty set of repeatitions! position $pos", $pos);
                    }

                    if (0 === count($this->symbols)) {
                        throw new Exception("You cannot apply defined repeatition on no regex!", $startPos);
                    }

                    if (1 === count($numbers)) {
                        $lastSymbol = array_pop($this->symbols);
                        $this->symbols[] = $lastSymbol->symbolWillRepeatNTimes((int) $numbers[0]);
                        $this->iterator->next();

                        return;
                    }

                    $lastSymbol = array_pop($this->symbols);
                    $this->symbols[] = $lastSymbol->symbolWillRepeatNTimes((int) $numbers[0], (int) $numbers[0]);
                    $this->iterator->next();

                    return;

                case ',':
                    $numbers[] = '';

                case '0':
                case '1':
                case '2':
                case '3':
                case '4':
                case '5':
                case '6':
                case '7':
                case '8':
                case '9':
                    $numbers[count($num) - 1] .= $this->iterator->current();

                default:
                    throw new LogicException("Invalid repeatition symbol: ". $this->iterator->current(), $this->iterator->key());
            }
            $this->iterator->next();
        }

        
        $this->throwUnexpectedEndOfInputException('}');
    }

    public function processGroupPattern(): void
    {
        if (static::CODE_CLOSE_GROUP === ($code = $this->iterator->currentCode())
            && empty($this->groupStacks)
        ) {
            $pos = $this->iterator->key();
            $this->throwUnexpectedSymbolException(')');
        }

        if (static::CODE_CLOSE_GROUP === $code) {
            $symbol = GroupSymbol::newFrom($this->symbols);
            $this->symbols = array_pop($this->groupStacks);
            $this->symbols[] = $symbol;
            $this->iterator->next();

            return;
        }

        if (static::CODE_OPEN_GROUP !== $code) {
            return;
        }

        $this->groupStacks[] = $this->symbols;
        $this->iterator->next();
    }

    public function processSingleSymbol(): void
    {
        $this->symbols[] = UniqueSymbol::newFrom($this->iterator->current());
        $this->iterator->next();
    }

    public function throwUnexpectedSymbolException(string $symbol): void
    {
        throw new UnexpectedSymbolException($this->pattern, $symbol, $this->iterator->key());
    }

    public function throwUnexpectedEndOfInputException(string $symbol = ''): void
    {
        throw new throwUnexpectedEndOfInputException($this->pattern, $symbol);
    }
}