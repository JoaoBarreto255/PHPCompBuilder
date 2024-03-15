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
    const CODE_OPEN_SET = 91;  // ASCII code for left square bracket ([)
    const CODE_CLOSE_SET = 93; // ASCII code for right square bracket (])
    const CODE_OPEN_GROUP = 40; // ASCII code for left parenthesis (()
    const CODE_CLOSE_GROUP = 41; // ASCII code for right parenthesis ())
    const CODE_OPEN_BRACE = 123; // ASCII code for left curly brace ({)
    const CODE_CLOSE_BRACE = 125; // ASCII code for right curly brace (})
    const CODE_COMMA = 44;  // ASCII code for comma (,)

    private const REPEAT_MODS_CHECKS = [
        ord('*')
    ];

    private ?string $setValue = '';
    private int $bracketsStack = 0;
    private bool $insideBrace = false;

    private array $symbols = [];

    public function __construct(
        public readonly string $pattern
    ) {
    }

    public function processPattern()
    {
        // for \{symbols}
        foreach ($this->pattern as $key => $char) {
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

            if (static::CODE_STAR === $code && $symbol = array_pop($this->symbols)) {
                $this->symbols[] = $symbol->symbolMaybeExistOrRepeat()

                continue;
            }

            if (static::CODE_STAR === $char) {
                throw new \LogicException("Missing symbol to add * repeat operation", 1);
            }


        }

    }

    public function processRepeaterModifiers(int $expectedCode, int $charCode): bool
    {

    }


}