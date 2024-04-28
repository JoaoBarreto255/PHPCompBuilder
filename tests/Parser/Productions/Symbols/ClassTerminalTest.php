<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Tests\Parser\Productions\Symbols;

use JB255\PHPCompBuilder\Parser\Productions\Symbols\AbstractSymbol;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\ClassTerminal;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\SymbolInterface;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\Terminal;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
#[CoversClass(ClassTerminal::class)]
class ClassTerminalTest extends TestCase
{
    #[DataProvider('successProvider')]
    public function testTerminal(string $value): void
    {
        $term = new ClassTerminal($value);

        $this->assertInstanceOf(SymbolInterface::class, $term);
        $this->assertInstanceOf(AbstractSymbol::class, $term);

        $this->assertSame("'{$value}'", (string) $term);
    }

    #[DataProvider('failProvider')]
    public function testTerminalFail(string $value): void
    {
        $this->expectException(\LogicException::class);
        // $this->expectExceptionMessage(sprintf('Symbol "%s" not match with pattern: r"/^\'[[:alpha:]]\w*(\\&[[:alpha:]]\w*)*\'$/"', $value));

        new ClassTerminal($value);
    }

    public static function successProvider(): array
    {
        $className = Terminal::class;

        return [["'{$className}'"]];
    }

    public static function failProvider(): array
    {
        return [
            ['1F', \LogicException::class],
            ['_Foo', \LogicException::class],
            ['123', \LogicException::class],
            ['!=', \LogicException::class],
            ['Foo', \LogicException::class],
            ['Barr', \LogicException::class],
            ['Barr', \LogicException::class],
            ["'-bar'", \LogicException::class],
            // Pattern valid but class does not exist.
            ["'Foo\\Bar\\Baz'", \InvalidArgumentException::class],
        ];
    }
}
