<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Tests\Parser\Productions\Symbols;

use JB255\PHPCompBuilder\Parser\Productions\Symbols\AbstractSymbol;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\SymbolInterface;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\Terminal;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Terminal::class)]
class TerminalTest extends TestCase
{
    #[DataProvider('successProvider')]
    public function testTerminal(string $value): void
    {
        $term = new Terminal($value);

        $this->assertInstanceOf(SymbolInterface::class, $term);
        $this->assertInstanceOf(AbstractSymbol::class, $term);

        $this->assertSame("'$value'", (string) $term);
    }

    #[DataProvider('failProvider')]
    public function testTerminalFail(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Symbol "%s" not match with pattern: r"/^\'([[:punct:]]+|\w+)\'$/"', $value));

        new Terminal($value);
    }

    public static function successProvider(): array
    {
        return [
            ["'Foo'"],
            ["'foo'"],
            ["'foo_'"],
            ["'fo_o'"],
            ["'f_ooooo'"],
            ["'fo12'"],
            ["'f123'"],
            ["'F'"],
            ["'_F'"],
            ["'!='"],
            ["'=='"],
            ["'+='"],
            ["'_'"],
        ];
    }

    public static function failProvider(): array
    {
        return [
            ['1F'],
            ['_Foo'],
            ['123'],
            ['!='],
            ['Foo'],
            ['Barr'],
            ['Barr'],
            ["'-bar'"]
        ];
    }
}
