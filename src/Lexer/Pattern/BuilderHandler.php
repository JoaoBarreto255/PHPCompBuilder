<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer\Pattern;

use LogicException;

use Joaobarreto255\PhpCompBuilder\Lexer\Iterators\StringIterator;
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
    const CODE_DOT = 46

    private const REPEAT_MODS_MAP_CHECKS = [
        42 => 'symbolMaybeExistOrRepeat', // ASCII code for asterisk (*) /action
        43 => 'symbolMaybeRepeat',
        63 => 'symbolMayNotHappen',
    ];

    protected StringIterator $iterator;

    private array $symbols = [];

    public function __construct(
        public readonly string $pattern
    ) {
        $this->iterator = new StringIterator($this->pattern);
    }

    public function processPattern()
    {
        while ($this->iterator->valid()) {
            $key = $this->iterator->key();
            $char = $this->iterator->current();
            $code = $this->iterator->currentCode();

            if (static::BAR === $code) {
                $this->iterator->next();
                $char = $this->iterator->current() ?? '';
                $this->symbols[] = UniqueSymbol::newFrom($char);
                $this->iterator->next()
                continue;
            }

            if ($forceSymbol) {
                $forceSymbol = false;
                $this->symbols[] = UniqueSymbol::newFrom($char);
                continue;
            }

            if ($funcName = static::REPEAT_MODS_MAP_CHECKS[$code] ?? false) {
                if (!count($this->symbols)) {
                    throw new LogicException("Unexpected \"$char\" repeat operation", $key);
                }
                $symbol = array_pop($this->symbols);
                $this->symbols[] = $symbol->{$funcName}();
                unset($symbol);
        
                continue;
            }

            $this->processClassPattern();

            $this->processBracePattern();

            if ($this->processGroupPattern()) {
                continue;
            }
        }

    }

    public function processClassPattern(): void
    {
        if (static::CODE_DOT === $this->iterator->currentCode()) { 
            $this->symbols[] = ClassSymbol::newFrom($this->iterator->current());
            $this->iterator->next();
        }

        if (static::CODE_CLOSE_CLASS === ($code = $this->iterator->currentCode())) {
            throw new LogicException("Unexpected \"]\" at pattern position: $key", $key);
        }

        if (static::CODE_OPEN_CLASS !== $code) { return null; }

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
                    return;
                
                default:
                    # do nothing
                    break;
            }

            $len++;
            $this->iterator->next();
        }

        throw new LogicException("Unexpected end of pattern! Expecting \"]\"", 1);
    }

    public function processBracePattern(): void
    {
        $code = $this->iterator->currentCode();
        $key = $this->iterator->key();

        if (static::CODE_CLOSE_BRACE === $code) { 
            throw new LogicException(sprintf('Founded "]" with no previous "[" at position %d!', $key), $key);
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
                    $numbers[count($num)] .= $this->iterator->current();

                default:
                    throw new LogicException("Invalid repeatition symbol: ". $this->iterator->current(), 1);
            }
            $this->iterator->next();
        }

        
        throw new LogicException('Unexpected end of pattern! Waiting for "}"', 1);
    }

    public function processGroupPattern(): bool
    {

    }
}