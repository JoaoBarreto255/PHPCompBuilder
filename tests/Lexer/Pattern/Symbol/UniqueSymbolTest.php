<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Tests\Lexer\Pattern\Symbol;

use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol\UniqueSymbol;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
#[CoversClass(UniqueSymbol::class)]
class UniqueSymbolTest extends TestCase
{
    #[TestDox('Test static fail in try to create new non uniqueSymbol')]
    #[DataProvider('dataProvider')]
    public function testNew(string $pattern, string $message)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);
        UniqueSymbol::newFrom($pattern);
    }

    public static function dataProvider(): array
    {
        return [
            ['', 'Unique symbol must have length 1, found 0'],
            ['aa', 'Unique symbol must have length 1, found 2'],
            ['aaa', 'Unique symbol must have length 1, found 3'],
            ['aaaaaaaaaaa', 'Unique symbol must have length 1, found 11'],
        ];
    }
}
