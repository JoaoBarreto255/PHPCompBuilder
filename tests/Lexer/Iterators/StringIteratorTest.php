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
    #[TestDox('Test methods current, next, key, rewind, previous together')]
    public function testGeneral(StringIterator $it, array $expectedValues, array $expectedKeys)
    {
        # test next.
        $zipped = array_map(null, $expectedValues, $expectedKeys);
        foreach($zipped as [$value, $key]) {
            $this->assertSame($value, $it->current());
            $this->assertSame($key, $it->key());
            $it->next();
        }

        // test rewind.
        $it->rewind();
        foreach($zipped as [$value, $key]) {
            $this->assertSame($value, $it->current());
            $this->assertSame($key, $it->key());
            $it->next();
        }

        // test previous.
        foreach(array_reverse($zipped) as [$value, $key]) {
            $it->previous();
            $this->assertSame($value, $it->current());
            $this->assertSame($key, $it->key());
        }
    }

    public static function generalDataProvider(): array
    {
        return [
            "data set #1 - abc" => [
                new StringIterator('abc'),
                ["a", "b", "c", null],
                [0, 1, 2, 3],
            ],
            "data set #2 - reverse abc" => [
                new StringIterator('abc', true),
                ["c", "b", "a", null],
                [2, 1, 0, -1],
            ],
        ];
    }

}