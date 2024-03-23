<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Tests\Lexer\Pattern\Symbol;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
#[CoversClass(SymbolAbstract::class)]
class SymbolTraitTest extends TestCase
{
    #[TestDox('Test static method createSymbol')]
    public function testCreateSymbol()
    {
        $symbol = MockedSymbol::createSymbol('foo');
        $this->assertSame('foo', $symbol->value);
        $this->assertSame(1, $symbol->start);
        $this->assertSame(1, $symbol->end);

        $symbol = MockedSymbol::createSymbol('foo', starRepeat: true);
        $this->assertSame(0, $symbol->start);
        $this->assertFalse($symbol->end);

        $symbol = MockedSymbol::createSymbol('foo', plusRepeat: true);
        $this->assertSame(1, $symbol->start);
        $this->assertFalse($symbol->end);

        $symbol = MockedSymbol::createSymbol('foo', maybeExist: true);
        $this->assertSame(0, $symbol->start);
        $this->assertSame(1, $symbol->end);

        $symbol = MockedSymbol::createSymbol('foo', end: 10);
        $this->assertSame(1, $symbol->start);
        $this->assertSame(10, $symbol->end);

        $symbol = MockedSymbol::createSymbol('foo', start: 10);
        $this->assertSame(10, $symbol->start);
        $this->assertSame(10, $symbol->end);

        $symbol = MockedSymbol::createSymbol('foo', start: 10, end: 12);
        $this->assertSame(10, $symbol->start);
        $this->assertSame(12, $symbol->end);
    }

    #[DataProvider('createSymbolDataProvider')]
    #[TestDox('Fail on create symbol. expected message. error_message: "$expected"')]
    public function testFailCreateSymbol(string|array $value, int $start, int|false $end, string $expected)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expected);
        MockedSymbol::createSymbol($value, start: $start, end: $end);
    }

    public static function createSymbolDataProvider(): array
    {
        return [
            ['', 1, 10, 'Property "value" must be not empty'],
            [[], 1, 10, 'Property "value" must be not empty'],
            ['abc', -1, 10, 'Property "start" must be not negative!'],
            [['a'], -1, 10, 'Property "start" must be not negative!'],
        ];
    }

    #[TestDox('test method symbolMaybeExistOrRepeat for zero or more repeatitions (*)')]
    public function testSymbolMaybeExistOrRepeat()
    {
        $original = MockedSymbol::createSymbol('foo');
        $this->assertSame('foo', $original->value);
        $this->assertSame(1, $original->start);
        $this->assertSame(1, $original->end);

        $new = $original->symbolMaybeExistOrRepeat();
        $this->assertNotSame($original, $new);
        $this->assertSame('foo', $new->value);
        $this->assertSame(0, $new->start);
        $this->assertFalse($new->end);
    }

    #[TestDox('test method symbolMaybeRepeat for one or more repeatitions (+)')]
    public function testSymbolMaybeRepeat()
    {
        $original = MockedSymbol::createSymbol('foo');
        $this->assertSame('foo', $original->value);
        $this->assertSame(1, $original->start);
        $this->assertSame(1, $original->end);

        $new = $original->symbolMaybeRepeat();
        $this->assertNotSame($original, $new);
        $this->assertSame('foo', $new->value);
        $this->assertSame(1, $new->start);
        $this->assertFalse($new->end);
    }

    #[TestDox('test method symbolMayNotHappen for one or more repeatitions (+)')]
    public function testSymbolMayNotHappen()
    {
        $original = MockedSymbol::createSymbol('foo');
        $this->assertSame('foo', $original->value);
        $this->assertSame(1, $original->start);
        $this->assertSame(1, $original->end);

        $new = $original->symbolMayNotHappen();
        $this->assertNotSame($original, $new);
        $this->assertSame('foo', $new->value);
        $this->assertSame(0, $new->start);
        $this->assertSame(1, $new->end);
    }

    #[DataProvider('symbolWillRepeatNTimesDataProvider')]
    #[TestDox('test method symbolWillRepeatNTimes for one or more repeatitions "{$n}"')]
    public function testSymbolWillRepeatNTimes(int $n)
    {
        $original = MockedSymbol::createSymbol('foo');
        $this->assertSame('foo', $original->value);
        $this->assertSame(1, $original->start);
        $this->assertSame(1, $original->end);

        $new = $original->symbolWillRepeatNTimes($n);
        $this->assertNotSame($original, $new);
        $this->assertSame('foo', $new->value);
        $this->assertSame($n, $new->start);
        $this->assertSame($n, $new->end);
    }

    public static function symbolWillRepeatNTimesDataProvider(): array
    {
        return array_map(fn (int $i) => [$i], range(0, 10));
    }

    #[DataProvider('symbolWillRepeatFromNToMTimesDataProvider')]
    #[TestDox('test method symbolWillRepeatFromNToMTimes for n to m repeatitions "Range{$n,$m}"')]
    public function testSymbolWillRepeatFromNToMTimes(int $n, int $m)
    {
        $original = MockedSymbol::createSymbol('foo');
        $this->assertSame('foo', $original->value);
        $this->assertSame(1, $original->start);
        $this->assertSame(1, $original->end);

        $new = $original->symbolWillRepeatFromNToMTimes($n, $m);
        $this->assertNotSame($original, $new);
        $this->assertSame('foo', $new->value);
        $this->assertSame($n, $new->start);
        $this->assertSame($m, $new->end);
    }

    public static function symbolWillRepeatFromNToMTimesDataProvider(): array
    {
        $result = [];
        for ($i = 0; $i < 11; ++$i) {
            for ($j = $i; $j < 11; ++$j) {
                $result["DataPoint({$i},{$j})"] = [$i, $j];
            }
        }

        return $result;
    }
}
