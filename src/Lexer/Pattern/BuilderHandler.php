<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer\Pattern;

use \LogicException;

use Joaobarreto255\PhpCompBuilder\Iterators\StringIterator;
use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol\GroupSequenceSymbol;
use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol\SetUnionSymbol;
use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol\UniqueSymbol;

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
                    throw new \LogicException("Unexpected \"$char\" repeat operation"), $key);
                }
                $symbol = array_pop($this->symbols);
                $this->symbols[] = $symbol->{$funcName}();
                unset($symbol);
        
                continue;
            }

            if (static::CODE_OPEN_CLASS === $code && 0 !== $this->classStart) {
                $this->classStart = $key + 1;
                continue;
            }

            if (static::CODE_OPEN_CLASS === $code) {
                throw new \LogicException("Unexpected \"[\" at pattern position: $key", $key);
            }

            if ($this->classStart && static::CODE_CLOSE_CLASS === $code) {
                $this->symbols[] = ClassSymbol::newFrom(substr($this->pattern, $this->classStart, $key))
                $this->classStart = 0;

                continue;
            }

            if (static::CODE_CLOSE_CLASS === $code) {
                throw new \LogicException("Unexpected \"]\" at pattern position: $key", $key);
            }

            if ($this->classStart) {
                continue;
            }

            if (static::CODE_OPEN_BRACE === $code && 0 !== $this->classStart) {
                $this->classStart = $key + 1;
                continue;
            }

            if (static::CODE_OPEN_BRACE === $code) {
                throw new \LogicException("Unexpected \"{\" at pattern position: $key", $key);
            }

            if ($this->classStart && static::CODE_CLOSE_BRACE === $code) {
                $this->

                continue;
            }

            if (static::CODE_CLOSE_BRACE === $code) {
                throw new \LogicException("Unexpected \"}\" at pattern position: $key", $key);
            }

            if ($this->classStart) {
                continue;
            }
        }

    }

    public function processBraceExpr()
    {

    }
}