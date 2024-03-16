<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Tests\Lexer\Iterators;

use Joaobarreto255\PhpCompBuilder\Lexer\Iterators\StringIterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

class StringIteratorTest extends TestCase
{
    #[DataProvider('getSizeDataProvider')]
    #[TestDox('Test GetSize of "$string" to be $expected.')]
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

    public function testCurrentAndNext()
    {
        $it = new StringIterator("abc");
        $this->assertEquals('a', $it->current());
        $it->next();
        $this->assertEquals('b', $it->current());
        $it->next();
        $this->assertEquals('c', $it->current());
        $it->next();
        $this->assertNull($it->current());

        $it = new StringIterator("abc", true);
        $this->assertEquals('c', $it->current());
        $it->next();
        $this->assertEquals('b', $it->current());
        $it->next();
        $this->assertEquals('a', $it->current());
        $it->next();
        $this->assertNull($it->current());

    }

}