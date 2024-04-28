<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Tests\Parser\Productions\Symbols;

use JB255\PHPCompBuilder\Parser\Productions\Symbols\AbstractSymbol;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\SymbolInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
#[CoversClass(AbstractSymbol::class)]
class AbstractSymbolTest extends TestCase
{
    public function testSymbolNoImplementsSymbolInterfaceError(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches(
            '/ must implement interface "JB255\\\PHPCompBuilder\\\Parser\\\Productions\\\Symbols\\\SymbolInterface"/'
        );

        new class('foo') extends AbstractSymbol { };
    }

    public function testSymbolHasEmptyConstant(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches('/ have invalid SYMBOL_PATTERN \(""\)\. Redefine yours/');

        new class('foo') extends AbstractSymbol implements SymbolInterface {
            public function __toString(): string
            {
                return 'foo';
            }
        };
    }

    public function testSymbolHasInvalidMatch(): void
    {
        $this->markTestSkipped('Throwing warning!');
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches('/ have invalid SYMBOL_PATTERN \("\/foo\)\/"\)\. Redefine yours/');

        new class('foo') extends AbstractSymbol implements SymbolInterface {
            public const SYMBOL_PATTERN = '/foo)/';

            public function __toString(): string
            {
                return 'foo';
            }
        };
    }

    #[DataProvider('dataProvider')]
    public function testCreateSymbolFailWhenValueIsNotValid(string $value, string $exception, string $errorMessage): void
    {
        $this->expectException($exception);
        $this->expectExceptionMessage($errorMessage);

        new class($value) extends AbstractSymbol implements SymbolInterface {
            public const SYMBOL_PATTERN = '/foo/';

            public function __toString(): string
            {
                return 'foo';
            }

            protected function extraValueValidation(string $value): void
            {
                if ('foo' !== $value) {
                    throw new \Exception('Bar');
                }
            }
        };
    }

    public static function dataProvider(): array
    {
        return [
            ['bar', \InvalidArgumentException::class, 'Symbol "bar" not match with pattern: r"/foo/"'],
            ['foofoo', \Exception::class, 'Bar'],
        ];
    }
}
