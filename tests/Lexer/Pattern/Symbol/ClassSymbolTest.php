<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Tests\Lexer\Pattern\Symbol;

use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol\ClassSymbol;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
#[CoversClass(ClassSymbol::class)]
class ClassSymbolTest extends TestCase
{
    public function testMakeIntervalCharSet()
    {
        $result = ClassSymbol::makeIntervalCharSet(10);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertSame([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $result);

        $result = ClassSymbol::makeIntervalCharSet(10, 5);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertSame([5, 6, 7, 8, 9, 10], $result);
    }

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
}
