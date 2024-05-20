<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Tests\Parser\SchemaContainers;

use JB255\PHPCompBuilder\Parser\Attributes\Terminal;
use JB255\PHPCompBuilder\Parser\Contracts\TerminalInterface;
use JB255\PHPCompBuilder\Parser\SchemaContainers\TerminalSchema;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TerminalSchema::class)]
class TerminalSchemaTest extends TestCase
{
    #[DataProvider('dataProvider')]
    public function testFailOnCreateTerminalSchema(string $input, bool $isClass, string $exceptionType)
    {
        $this->expectException($exceptionType);
        new TerminalSchema($input, $isClass);
    }

    public static function dataProvider(): array
    {
        return [
            ['', false, \InvalidArgumentException::class],
            ['', true, \InvalidArgumentException::class],
            ['foo', true, \InvalidArgumentException::class],
            [get_class(new class() {}), true, \InvalidArgumentException::class],
        ];
    }

    public function testSuccessOnCreateNonterminalSchema()
    {
        new Terminal('foo');
        new TerminalSchema(get_class(new class() implements TerminalInterface {}), true);
        new TerminalSchema(TerminalUsingAttributeSample::class, true);
        $this->assertTrue(true);
    }
}
