<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Tests\Lexer;

use JB255\PHPCompBuilder\Lexer\AbstractLexer;
use JB255\PHPCompBuilder\Lexer\Pattern\TokenRulePattern;


#[TokenRulePattern('varName', '/[a-z](\w|\d|\_)*/')] 
#[TokenRulePattern('for','for', true)]
#[TokenRulePattern('in','in', true)]
#[TokenRulePattern('func','[A-Z][A-Za-z0-9_]*')]
#[TokenRulePattern('num', '\d+')]
class FakeLexer extends AbstractLexer
{
    public function ignorePattern(): string
    {
        return '/\s+/';
    }
}