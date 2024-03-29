<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer;

use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\TokenRuleIterator;
use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\TokenRulePattern;

abstract class AbstractLexer
{
    public function __construct(
        readonly private \Iterator $streamIterator
    ) { }
}