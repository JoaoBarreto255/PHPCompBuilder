<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Tests\Lexer\Pattern;

use JB255\PHPCompBuilder\Lexer\Pattern\TokenRuleIterator;
use JB255\PHPCompBuilder\Lexer\Pattern\TokenRulePattern;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
#[CoversClass(TokenRuleIterator::class)]
class TokenRuleIteratorTest extends TestCase
{
    #[TestDox('Test iterate over matches')]
    #[DataProvider('dataProvider')]
    public function testIterateMatches(TokenRulePattern $pattern, string $input, array $expected)
    {
        $iterator = new TokenRuleIterator($input, $pattern);

        $this->assertCount(count($expected), $iterator->getArrayCopy());
        foreach ($expected as [$offset, $value]) {
            $this->assertTrue($iterator->valid());
            $this->assertNotEmpty($iterator->current());
            $this->assertIsString($iterator->current());
            $this->assertSame($value, $iterator->current());
            $this->assertSame($offset, $iterator->key());
            $iterator->next();
        }
    }

    public static function dataProvider(): array
    {
        return [
            [new TokenRulePattern('a', 'foo'), 'foobarbaz', [[0, 'foo']]],
            [new TokenRulePattern('a', 'foo'), 'barfoobaz', [[3, 'foo']]],
            [new TokenRulePattern('a', 'foo'), 'barbazfoo', [[6, 'foo']]],
            [new TokenRulePattern('a', 'zig'), 'barbazfoo', []],
            [new TokenRulePattern('a', 'zig'), 'zigzaggzigzag', [
                [0, 'zig'],
                [7, 'zig'],
            ]],
            [new TokenRulePattern('a', '/z.g/'), 'zigzaggzigzag', [
                [0, 'zig'],
                [3, 'zag'],
                [7, 'zig'],
                [10, 'zag'],
            ]],
            [new TokenRulePattern('a', '/z.g/i'), 'zigzaggzigzag', [
                [0, 'zig'],
                [3, 'zag'],
                [7, 'zig'],
                [10, 'zag'],
            ]],
            [new TokenRulePattern('a', '/(?<name>(bar)(biz)(boo))/'), 'barbizboooobzibrabbarbizboo', [
                [0, 'barbizboo'],
                [18, 'barbizboo'],
            ]],
        ];
    }
}
