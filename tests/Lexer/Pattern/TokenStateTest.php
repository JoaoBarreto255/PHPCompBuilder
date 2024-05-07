<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Tests\Lexer\Pattern;

use JB255\PHPCompBuilder\Lexer\Pattern\TokenRulePattern;
use JB255\PHPCompBuilder\Lexer\Pattern\TokenState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
#[CoversClass(TokenState::class)]
class TokenStateTest extends TestCase
{
    #[TestDox('Test iterate over matches')]
    #[DataProvider('dataProvider')]
    public function testTokenLen(TokenState $state, int $len)
    {
        $this->assertSame($len, $state->len);
    }

    public static function dataProvider(): array
    {
        $rule = new TokenRulePattern('foo', '/\w+/');
        return [
            [new TokenState($rule, 'ba'), 2],
            [new TokenState($rule, 'bar'), 3],
            [new TokenState($rule, 'buzz'), 4],
            [new TokenState($rule, 'buzzz'), 5],
        ];
    }

    public function testCompare(): void
    {
        $rule = new TokenRulePattern('foo', '/\w+/');
        $state1 = new TokenState($rule, 'baz');
        $state2 = new TokenState($rule, 'bazz');

        $this->assertSame(0, $state1->compare($state1));
        $this->assertGreaterThan(0, $state2->compare($state1));
        $this->assertLessThan(0, $state1->compare($state2));

        $state3 = new TokenState(new TokenRulePattern('bazz', 'baz', reserved: true), 'bazz');
        $this->assertGreaterThan(0, $state3->compare($state2));
        $this->assertLessThan(0, $state2->compare($state3));
    }
}
