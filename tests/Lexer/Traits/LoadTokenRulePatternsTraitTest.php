<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Tests\Lexer\Traits;

use JB255\PHPCompBuilder\Lexer\Pattern\TokenRulePattern;
use JB255\PHPCompBuilder\Lexer\Traits\LoadTokenRulePatternsTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
#[TokenRulePattern('varName', '/[a-z](\w|\d|\_)*/')]
#[TokenRulePattern('for', 'for', reserved: true)]
#[TokenRulePattern('in', 'in', reserved: true)]
#[TokenRulePattern('func', '[A-Z][A-Za-z0-9_]*')]
#[TokenRulePattern('num', '\d+')]
#[CoversClass(LoadTokenRulePatternsTrait::class)]
class LoadTokenRulePatternsTraitTest extends TestCase
{
    use LoadTokenRulePatternsTrait;

    public function testGetTokenRuleFromClass()
    {
        $result = $this->getTokenRuleFromClass();
        $this->assertIsArray($result);
        $this->assertCount(6, $result);

        $refinedResult = [];
        foreach ($result as $key => $tokenRule) {
            $this->assertNotNull($tokenRule, 'tokenRule must not be empty! (key: '.$key.')');
            $this->assertInstanceOf(TokenRulePattern::class, $tokenRule);
            $refinedResult[] = [
                $tokenRule->tokenName,
                $tokenRule->pattern,
                $tokenRule->reserved,
            ];
        }

        $this->assertSame([
            ['varName', '/[a-z](\w|\d|\_)*/', false],
            ['for', '/for/', true],
            ['in', '/in/', true],
            ['func', '/[A-Z][A-Za-z0-9_]*/', false],
            ['num', '/\d+/', false],
            ['__ignoreToken', '/\s+/', false],
        ], $refinedResult);
    }

    public function ignorePattern(): string
    {
        return '/\s+/';
    }
}
