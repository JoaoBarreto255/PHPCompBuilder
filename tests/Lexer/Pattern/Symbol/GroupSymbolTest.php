<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Tests\Lexer\Pattern\Symbol;

use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol\GroupSymbol;
use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol\UniqueSymbol;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(GroupSymbol::class)]
class GroupSymbolTest extends TestCase
{
    #[TestDox('Test static fail in try to create new non GroupSymbol')]
    #[DataProvider('dataProvider')]
    public function testNew(array $pattern, string $message)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);
        GroupSymbol::newFrom($pattern);
    }

    public static function dataProvider(): array
    {
        return [
            [[], "Empty group symbol!"],
        ];
    }

    public function testAppend()
    {
        $group = GroupSymbol::newFrom([UniqueSymbol::newFrom('a'), UniqueSymbol::newFrom('b')]);
        $this->assertCount(2, $group->value);
        $this->assertSame('a', $group->value[0]->value);
        $this->assertSame('b', $group->value[1]->value);

        $group2 = $group->append(UniqueSymbol::newFrom('c'));

        $this->assertCount(3, $group2->value);
        $this->assertSame('a', $group2->value[0]->value);
        $this->assertSame('b', $group2->value[1]->value);
        $this->assertSame('c', $group2->value[2]->value);
    }
}
