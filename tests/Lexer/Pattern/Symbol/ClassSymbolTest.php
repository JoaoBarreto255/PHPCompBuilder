<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Tests\Lexer\Pattern\Symbol;


use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol\ClassSymbol;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;


#[CoversClass(ClassSymbol::class)]
class ClassSymbolTest extends TestCase
{
    public function testMakeIntervalCharSet()
    {
        $result = ClassSymbol::makeIntervalCharSet(10);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertSame([0,1,2,3,4,5,6,7,8,9,10], $result);

        $result = ClassSymbol::makeIntervalCharSet(10, 5);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertSame([5,6,7,8,9,10], $result);
    }
}