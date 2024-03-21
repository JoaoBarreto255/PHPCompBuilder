<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Tests\Lexer\Pattern\Symbol;

use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol\ClassSymbol;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
#[CoversClass(ClassSymbol::class)]
class ClassSymbolTest extends TestCase
{
    #[TestDox('Test static method processClass from ClassSymbol')]
    public function testProcessClass()
    {
        $result = ClassSymbol::processClass([2,5,7]);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertSame([2,5,7], $result);

        $withRanges = [2,5,7, true, 97, 122, true, 65, 90, true, 48, 57];

        $result = ClassSymbol::processClass($withRanges);

        $expected = array_merge([2,5,7], range(97, 122), range(65, 90), range(48, 57));
        $this->assertIsArray($result);
        $this->assertCount(count($expected), $result);
        $this->assertSame($expected, $result);

        $expectedReverse = array_diff(range(0, 255), $expected);
        $result = ClassSymbol::processClass($withRanges, true);
        $this->assertIsArray($result);
        $this->assertCount(count($expectedReverse), $result);
        $this->assertSame($expectedReverse, $result);
    }

    #[DataProvider('newFromDataProvider')]
    #[TestDox('Test ClassSymbol newFrom with ($classPattern) -> ($expected)"')]
    public function testNewFrom(string $classPattern, string $expected)
    {
        $result = ClassSymbol::newFrom($classPattern);

        $this->assertSame($expected, $result->value);
    }

    public static function newFromDataProvider(): array
    {
        return [
            ['.', implode('', array_map('chr', range(0,255)))],
            ['a-z', 'abcdefghijklmnopqrstuvwxyz'],
            ['a-zA', 'Aabcdefghijklmnopqrstuvwxyz'],
            ['a-zA-E', 'ABCDEabcdefghijklmnopqrstuvwxyz'],
            ['0-9\[-\]\-', '-0123456789[\]'],
            ['\.\^\[\]', '.[]^'],
            ['^0-9', implode(
                '',
                array_map(
                    'chr',
                    array_merge(
                        range(0, 47),
                        range(58, 255),
                    )
                )
            )]
        ];
    }
}
