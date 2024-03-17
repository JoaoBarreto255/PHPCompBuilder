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

    private int $classStart = 0;
    private int $bracketsStack = 0;
    private int $braceStart = 0;

    protected StringIterator $iterator;

    private array $symbols = [];

    public function __construct(
        public readonly string $pattern
    ) {
        $this->iterator = new StringIterator($this->pattern);
    }

    public function processPattern()
    {
        // for \{symbols}
        foreach ($this->iterator as $key => $char) {
            $code = ord($char);
            if (static::BAR === $code) {
                $forceSymbol = true;
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

            if ($this->processClassPattern()) {
                continue;
            }

            if ($this->processBracePattern()) {
                continue;
            }

            if ($this->processGroupPattern()) {
                continue;
            }
        }

    }

    public function processClassPattern(): bool
    {
        $code = $this->iterator->currentCode();
        $key = $this->iterator->key();

        if (static::CODE_DOT === $code &&  0 === $this->classStart) {
            $this->symbols[] = ClassSymbol::newFrom(substr($this->pattern, $this->classStart, $key));

            return true;
        }

        if (static::CODE_OPEN_CLASS === $code && 0 < $this->classStart) {
            $this->classStart = $key + 1;

            return true;
        }

        if (static::CODE_OPEN_CLASS === $code) {
            throw new LogicException("Unexpected \"[\" at pattern position: $key", $key);
        }

        if ($this->classStart
            && static::CODE_CLOSE_CLASS === $code
            && $segment = substr(
                $this->pattern,
                $this->classStart,
                $key - $this->classStart,
            )
        ) {
            $this->symbols[] = ClassSymbol::newFrom($segment);
            $this->classStart = 0;

            return true;
        }

        if (static::CODE_CLOSE_CLASS === $code) {
            throw new LogicException("Unexpected \"]\" at pattern position: $key", $key);
        }

        return  0 < $this->classStart;
    }

    public function processBracePattern(): bool
    {
        $code = $this->iterator->currentCode();
        $key = $this->iterator->key();

        if (static::CODE_OPEN_BRACE === $code && 0 < $this->braceStart) {
            $this->braceStart = $key + 1;
    
            return true;
        }

        if (static::CODE_OPEN_BRACE === $code) {
            throw new LogicException("Unexpected \"{\" at pattern position: $key", $key);
        }

        if (
            $this->braceStart
            && static::CODE_CLOSE_BRACE === $code
            && ($segment = substr(
                $this->pattern,
                $this->braceStart,
                $key - $this->braceStart,
            ))
            && $args = explode(',', $segment)
        ) {
            $args = array_map(fn($i) => (int) $i, $args);
            if (!count($this->symbols)) {
                throw new LogicException("Unexpected \"$char\" repeat operation", $key);
            }
            $symbol = array_pop($this->symbols);
            if (1 === count($args)) {
                $this->symbols[] = $symbol->symbolWillRepeatNTimes($args[0]);

                return true;
            }

            $this->symbols[] = $symbol->symbolWillRepeatFromNToMTimes($args[0], $args[1]);

            return true;
        }

        if (static::CODE_CLOSE_BRACE === $code) {
            throw new LogicException("Unexpected \"}\" at pattern position: $key", $key);
        }

        return 0 < $this->braceStart;
    }

    public function processGroupPattern(): bool
    {

    }
}