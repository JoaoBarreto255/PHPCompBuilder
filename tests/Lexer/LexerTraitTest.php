<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Tests\Lexer;

use JB255\PHPCompBuilder\Lexer\LexerTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use JB255\PHPCompBuilder\Lexer\Pattern\TokenRulePattern;


#[TokenRulePattern('varName', '/[a-z](\w|\d|\_)*/')] 
#[TokenRulePattern('for','for', reserved:true)]
#[TokenRulePattern('in','in', reserved:true)]
#[TokenRulePattern('func','[A-Z][A-Za-z0-9_]*')]
#[TokenRulePattern('num', '\d+')]
#[CoversClass(LexerTrait::class)]
class LexerTraitTest extends TestCase
{
    use LexerTrait;

    public function ignorePattern(): string
    {
        return '/\s+/';
    }

    public function testBuildTokenStream()
    {
        $this->initLexer(new \ArrayIterator([
            "abc for i23 inoske 234 in\n\r",
            "   \t\n",
            "   \t Foo For\n",
        ]), 'test.php');

        $tokenStream = $this->buildTokenStream();
        $this->assertTrue($tokenStream->valid());
        $token = $tokenStream->current();
        $this->assertIsArray($token);
        $this->assertCount(5, $token);
        $this->assertSame(['token_name' => 'varName', 'value' => 'abc', 'pos' => 0, 'lineno' => 0, 'col' => 0], $token);
        $tokenStream->next();

        $this->assertTrue($tokenStream->valid());
        $token = $tokenStream->current();
        $this->assertIsArray($token);
        $this->assertCount(5, $token);
        $this->assertSame(['token_name' => 'for', 'value' => 'for', 'pos' => 4, 'lineno' => 0, 'col' => 4], $token);
        $tokenStream->next();

        $this->assertTrue($tokenStream->valid());
        $token = $tokenStream->current();
        $this->assertIsArray($token);
        $this->assertCount(5, $token);
        $this->assertSame(['token_name' => 'varName', 'value' => 'i23', 'pos' => 8, 'lineno' => 0, 'col' => 8], $token);
        $tokenStream->next();

        $this->assertTrue($tokenStream->valid());
        $token = $tokenStream->current();
        $this->assertIsArray($token);
        $this->assertCount(5, $token);
        $this->assertSame(['token_name' => 'varName', 'value' => 'inoske', 'pos' => 12, 'lineno' => 0, 'col' => 12], $token);
        $tokenStream->next();

        $this->assertTrue($tokenStream->valid());
        $token = $tokenStream->current();
        $this->assertIsArray($token);
        $this->assertCount(5, $token);
        $this->assertSame(['token_name' => 'num', 'value' => '234', 'pos' => 19, 'lineno' => 0, 'col' => 19], $token);
        $tokenStream->next();

        $this->assertTrue($tokenStream->valid());
        $token = $tokenStream->current();
        $this->assertIsArray($token);
        $this->assertCount(5, $token);
        $this->assertSame(['token_name' => 'in', 'value' => 'in', 'pos' => 23, 'lineno' => 0, 'col' => 23], $token);
        $tokenStream->next();

        $this->assertTrue($tokenStream->valid());
        $token = $tokenStream->current();
        $this->assertIsArray($token);
        $this->assertCount(5, $token);
        $this->assertSame(['token_name' => 'func', 'value' => 'Foo', 'pos' => 37, 'lineno' => 2, 'col' => 5], $token);
        $tokenStream->next();

        $this->assertTrue($tokenStream->valid());
        $token = $tokenStream->current();
        $this->assertIsArray($token);
        $this->assertCount(5, $token);
        $this->assertSame(['token_name' => 'func', 'value' => 'For', 'pos' => 41, 'lineno' => 2, 'col' => 9], $token);
        $tokenStream->next();
    }
}
