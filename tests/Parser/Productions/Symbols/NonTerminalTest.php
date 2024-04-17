<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Tests\Parser\Productions\Symbols;

use JB255\PHPCompBuilder\Parser\Productions\Symbols\AbstractSymbol;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\SymbolInterface;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\NonTerminal;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(NonTerminal::class)]
class NonTerminalTest extends TestCase
{
    #[DataProvider('successProvider')]
    public function testNonTerminal(string $value): void
    {
        $nTerm = new NonTerminal($value);

        $this->assertInstanceOf(SymbolInterface::class, $nTerm);
        $this->assertInstanceOf(AbstractSymbol::class, $nTerm);

        $this->assertSame($value, (string) $nTerm);
    }

    #[DataProvider('failProvider')]
    public function testNonTerminalFail(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Symbol "%s" not match with pattern: r"/^[[:alpha:]][\w_]*$/"', $value));

        new NonTerminal($value);
    }

    public static function successProvider(): array
    {
        return [
            ['Foo'],
            ['foo'],
            ['foo_'],
            ['fo_o'],
            ['f_ooooo'],
            ['fo12'],
            ['f123'],
            ['F'],
        ];
    }

    public static function failProvider(): array
    {
        return [
            ['1F'],
            ['_Foo'],
            ['123'],
            ['!='],
        ];
    }
}
