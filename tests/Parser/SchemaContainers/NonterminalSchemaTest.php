<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Tests\Parser\SchemaContainers;

use JB255\PHPCompBuilder\Parser\Attributes\Nonterminal;
use JB255\PHPCompBuilder\Parser\Contracts\NonTerminalInterface;
use JB255\PHPCompBuilder\Parser\SchemaContainers\NonterminalSchema;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(NonterminalSchema::class)]
class NonterminalSchemaTest extends TestCase
{
    #[DataProvider('dataProvider')]
    public function testFailOnCreateNonterminalSchema(string $input, string $exceptionType)
    {
        $this->expectException($exceptionType);
        new NonterminalSchema($input);
    }

    public static function dataProvider(): array
    {
        return [
            ['', \InvalidArgumentException::class],
            ['foo', \InvalidArgumentException::class],
            [get_class(new class() {}), \InvalidArgumentException::class],
        ];
    }

    public function testSuccessOnCreateNonterminalSchema()
    {
        new NonterminalSchema(get_class(new class() implements NonTerminalInterface {}));
        new NonterminalSchema(NonterminalUsingAttributeSample::class);
        $this->assertTrue(true);
    }
}
