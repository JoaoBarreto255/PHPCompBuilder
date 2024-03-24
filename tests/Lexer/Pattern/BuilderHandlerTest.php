<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Tests\Lexer\Pattern;

use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\BuilderHandler;
use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol\UniqueSymbol;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(BuilderHandler::class)]
class BuilderHandlerTest extends TestCase
{
    #[TestDox('Test process single symble ...')]
    public function testProcessSingleSymbol()
    {
        $handler = new BuilderHandler('foo');

        $handler->processSingleSymbol();
        $symbols = $this->assertSymbols($handler, 1);
        $this->assertInstanceOf(UniqueSymbol::class, $symbols[0]);
        $this->assertSame('f', $symbols[0]->value);

        $handler->processSingleSymbol();
        $symbols = $this->assertSymbols($handler, 2);
        $this->assertInstanceOf(UniqueSymbol::class, $symbols[1]);
        $this->assertSame('o', $symbols[1]->value);

        $handler->processSingleSymbol();
        $symbols = $this->assertSymbols($handler, 3);
        $this->assertInstanceOf(UniqueSymbol::class, $symbols[2]);
        $this->assertSame('o', $symbols[2]->value);
    }

    public function assertSymbols(BuilderHandler $handler, int $count): array
    {
        $class = new \ReflectionClass($handler);
        $symbolsAttr = $class->getProperty('symbols');
        $symbolsAttr->setAccessible(true);

        $this->assertCount($count, $symbols = $symbolsAttr->getValue($handler));

        return $symbols;
    }
}
