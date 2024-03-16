<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Tests\Lexer\Iterators;

use Joaobarreto255\PhpCompBuilder\Lexer\Iterators\StringIterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(StringIterator::class)]
class StringIteratorTest extends TestCase
{
    #[DataProvider('getSizeDataProvider')]
    #[TestDox('Test method getSize. expect for "$string" result be $expected.')]
    public function testGetSize(int $expected, string $input)
    {
        $it = new StringIterator($input);
        $this->assertEquals($expected, $it->getSize());
    }

    public static function getSizeDataProvider(): array
    {
        $result = [];
        for ($i = 1; $i < 26; $i++) {
            $result["data set $i"] = [$i, random_bytes($i)];
        }

        return $result;
    }

    #[DataProvider('generalDataProvider')]
    #[TestDox('Test methods current and next together')]
    public function testNextAndCurrent(StringIterator $it, array $expectedValues, array $expectedKeys, array $expectedValid)
    {
        foreach($expectedValues as $expected) {
            $this->assertSame($expected, $it->current());
            $it->next();
        }
    }

    #[DataProvider('generalDataProvider')]
    #[TestDox('Test methods key and next together')]
    public function testNextAndKey(StringIterator $it, array $expectedValues, array $expectedKeys, array $expectedValid)
    {
        foreach($expectedKeys as $expected) {
            $this->assertSame($expected, $it->key());
            $it->next();
        }
    }

    #[DataProvider('generalDataProvider')]
    #[TestDox('Test methods valid and next together')]
    public function testNextAndValid(StringIterator $it, array $expectedValues, array $expectedKeys, array $expectedValid)
    {
        foreach ($expectedValid as $expected) {
            $this->assertSame($expected, $it->valid());
            $it->next();
        }
    }

    #[DataProvider('generalDataProvider')]
    #[TestDox('Test methods Rewind and Next together')]
    public function testRewind(StringIterator $it, array $expectedValues, array $expectedKeys, array $expectedValid)
    {
        foreach($expectedValues as $expected) {
            $this->assertSame($expected, $it->current());
            $it->next();
        }

        # test rewind and current.
        $it->rewind();
        foreach($expectedValues as $expected) {
            $this->assertSame($expected, $it->current());
            $it->next();
        }

        # test rewind and key.
        $it->rewind();
        foreach($expectedKeys as $expected) {
            $this->assertSame($expected, $it->key());
            $it->next();
        }

        # test valid and rewind.
        $it->rewind();
        foreach ($expectedValid as $expected) {
            $this->assertSame($expected, $it->valid());
            $it->next();
        }

        # test all together.
        $zipped = array_map(null, $expectedValues, $expectedKeys, $expectedValid);
        $it->rewind();
        foreach($zipped as [$value, $key, $isValid]) {
            $this->assertSame($value, $it->current());
            $this->assertSame($key, $it->key());
            $this->assertSame($isValid, $it->valid());
            $it->next();
        }
    }

    #[DataProvider('generalDataProvider')]
    #[TestDox('Test methods current, next, key, rewind, previous and valid together')]
    public function testPrevious(StringIterator $it, array $expectedValues, array $expectedKeys, array $expectedValid)
    {
        $zipped = array_map(null, $expectedValues, $expectedKeys, $expectedValid);
        foreach($zipped as [$value, $key, $isValid]) {
            $this->assertSame($value, $it->current());
            $this->assertSame($key, $it->key());
            $this->assertSame($isValid, $it->valid());
            $it->next();
        }

        # test previous.
        foreach(array_reverse($zipped) as [$value, $key, $isValid]) {
            $it->previous();
            $this->assertSame($value, $it->current());
            $this->assertSame($key, $it->key());
            $this->assertSame($isValid, $it->valid());
        }
    }

    #[DataProvider('generalDataProvider')]
    #[TestDox('Testa iteração do for loop')]
    public function testCurrentInsideLoop(StringIterator $it, array $expectedValues, array $expectedKeys, array $expectedValid)
    {
        foreach ($it as $key => $value) {
            $expectedKey = current($expectedKeys);
            $this->assertSame($expectedKey, $key);
            next($expectedKeys);

            $expectedValue = current($expectedValues);
            $this->assertSame($expectedValue, $value);
            $this->assertSame($expectedValue, $it->current());
            next($expectedValues);
        }
    }

    public static function generalDataProvider(): array
    {
        return [
            "data set #1 - abc" => [
                new StringIterator('abc'),
                ["a", "b", "c", null],
                [0, 1, 2, 3],
                [true, true, true, false],
            ],
            "data set #2 - reverse abc" => [
                new StringIterator('abc', true),
                ["c", "b", "a", null],
                [2, 1, 0, -1],
                [true, true, true, false],
            ],
        ];
    }

}
