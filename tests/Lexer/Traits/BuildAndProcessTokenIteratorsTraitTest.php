<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Tests\Lexer\Traits;

use JB255\PHPCompBuilder\Lexer\Pattern\TokenRuleIterator;
use JB255\PHPCompBuilder\Lexer\Pattern\TokenRulePattern;
use JB255\PHPCompBuilder\Lexer\Pattern\TokenState;
use JB255\PHPCompBuilder\Lexer\Traits\BuildAndProcessTokenIteratorsTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
#[CoversClass(BuildAndProcessTokenIteratorsTrait::class)]
class BuildAndProcessTokenIteratorsTraitTest extends TestCase
{
    use BuildAndProcessTokenIteratorsTrait;
    protected array $patterns = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->patterns = [
            new TokenRulePattern('varName', '/[a-z](\w|\d|\_)*/'),
            new TokenRulePattern('for', 'for', reserved: true),
            new TokenRulePattern('in', 'in', reserved: true),
            new TokenRulePattern('func', '[A-Z][A-Za-z0-9_]*'),
            new TokenRulePattern('num', '\d+'),
            new TokenRulePattern('__ignoreToken', '/\s+/'),
        ];

        $this->resetColumn()->resetPosition()->setLineno(0)->setLine('');
    }

    public function testFactoryIteratorsFromLine()
    {
        $this->setLine('for i2a in Range 10');
        $this->factoryIteratorsFromLine();
        $result = $this->iterators;
        $this->assertIsArray($result);
        $this->assertCount(6, $result);
        foreach ($result as $key => $iterator) {
            $msg = "fail at iterator row ({$key})";
            $this->assertInstanceOf(TokenRuleIterator::class, $iterator, $msg);
        }
    }

    public function testPeekRightToken(): void
    {
        $this->setLine('for forager in Foragers a1 12');
        $this->factoryIteratorsFromLine();

        $this->assertInstanceOf(TokenState::class, $state = $this->peekRightToken());
        $this->assertInstanceOf(TokenRulePattern::class, $state->tokenRule);
        $this->assertSame('for', $state->tokenRule->tokenName);
        $this->assertSame('for', $state->value);
        $this->assertSame(3, $state->len);
        $this->increaseColumn(3)->increasePosition(3);

        $this->assertInstanceOf(TokenState::class, $state = $this->peekRightToken());
        $this->assertInstanceOf(TokenRulePattern::class, $state->tokenRule);
        $this->assertSame('__ignoreToken', $state->tokenRule->tokenName);
        $this->assertSame(' ', $state->value);
        $this->assertSame(1, $state->len);
        $this->increaseColumn(1)->increasePosition(1);

        $this->assertInstanceOf(TokenState::class, $state = $this->peekRightToken());
        $this->assertInstanceOf(TokenRulePattern::class, $state->tokenRule);
        $this->assertSame('varName', $state->tokenRule->tokenName);
        $this->assertSame('forager', $state->value);
        $this->assertSame(7, $state->len);
        $this->increaseColumn(8)->increasePosition(8);

        $this->assertInstanceOf(TokenState::class, $state = $this->peekRightToken());
        $this->assertInstanceOf(TokenRulePattern::class, $state->tokenRule);
        $this->assertSame('in', $state->tokenRule->tokenName);
        $this->assertSame('in', $state->value);
        $this->assertSame(2, $state->len);
        $this->increaseColumn(3)->increasePosition(3);

        $this->assertInstanceOf(TokenState::class, $state = $this->peekRightToken());
        $this->assertInstanceOf(TokenRulePattern::class, $state->tokenRule);
        $this->assertSame('func', $state->tokenRule->tokenName);
        $this->assertSame('Foragers', $state->value);
        $this->assertSame(8, $state->len);
        $this->increaseColumn(9)->increasePosition(9);

        $this->assertInstanceOf(TokenState::class, $state = $this->peekRightToken());
        $this->assertInstanceOf(TokenRulePattern::class, $state->tokenRule);
        $this->assertSame('varName', $state->tokenRule->tokenName);
        $this->assertSame('a1', $state->value);
        $this->assertSame(2, $state->len);
        $this->increaseColumn(3)->increasePosition(3);

        $this->assertInstanceOf(TokenState::class, $state = $this->peekRightToken());
        $this->assertInstanceOf(TokenRulePattern::class, $state->tokenRule);
        $this->assertSame('num', $state->tokenRule->tokenName);
        $this->assertSame('12', $state->value);
        $this->assertSame(2, $state->len);
        $this->increaseColumn(2)->increasePosition(2);

        $this->assertNull($this->peekRightToken());
    }
}
