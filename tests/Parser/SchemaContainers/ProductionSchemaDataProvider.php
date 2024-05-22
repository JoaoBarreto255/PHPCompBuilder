<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Tests\Parser\SchemaContainers;

use JB255\PHPCompBuilder\Parser\Attributes\Production;
use JB255\PHPCompBuilder\Parser\Attributes\Terminal;
use JB255\PHPCompBuilder\Parser\Contracts\NonTerminalInterface;
use JB255\PHPCompBuilder\Parser\SchemaContainers\Exceptions\InvalidSymbolParameterException;
use JB255\PHPCompBuilder\Parser\SchemaContainers\NonterminalSchema;
use JB255\PHPCompBuilder\Parser\SchemaContainers\ProductionSchema;
use JB255\PHPCompBuilder\Parser\SchemaContainers\TerminalSchema;

class ProductionSchemaDataProvider
{
    public static function failOnCreateProductionSchemaOfInvalidMethodDataProvider(): array
    {
        $header = new class() implements NonTerminalInterface {
            public function invalidMethod($foo) { }
            public function productionInvalidParam(int $foo) { }
            public function ruleInvalidParam(
                $foo, string $bar, string $baz, float $buzz
            ) { }
            #[Production]
            public function invalidClassParam(Terminal $foo) {}
        };
        $header = new NonterminalSchema($header::class);

        return [
            'method_without_property_or_valid_name_pattern' => [
                $header, 'invalidMethod', \LogicException::class
            ],
            'method_with_invalid_builtin_arg' => [
                $header, 'productionInvalidParam', InvalidSymbolParameterException::class
            ],
            'method_with_invalid_builtin_arg_2' => [
                $header, 'ruleInvalidParam', InvalidSymbolParameterException::class
            ],
            'method_with_invalid_class_arg' => [
                $header, 'invalidClassParam', InvalidSymbolParameterException::class
            ],
        ];
    }

    public static function failOnProcessMultiTypesFields(): array
    {
        $header = new class() implements NonTerminalInterface {
            public function ruleSuccess(string $foo) { }
            public function failMultipleUnion(int|float|bool $test) {}
            public function failMultipleIntersection(\ArrayAccess&\Iterator $test) {}
        };
        $header = new NonterminalSchema($header::class);
        $productionSchema = new ProductionSchema($header, 'ruleSuccess');

        $method1 = new \ReflectionMethod($header->name, 'failMultipleUnion');
        $method2 = new \ReflectionMethod($header->name, 'failMultipleIntersection');

        return [
            [$productionSchema, current($method1->getParameters()), InvalidSymbolParameterException::class],
            [$productionSchema, current($method2->getParameters()), InvalidSymbolParameterException::class],
        ];
    }

    public static function createDataProvider(): array
    {
        $header = new class() implements NonTerminalInterface {
            public function ruleFoo($foo, NonterminalUsingAttributeSample $bar) {}
            public function ruleBar($foo, NonterminalUsingAttributeSample|\ArrayObject $bar) {}
            public function ruleBaz(string $foo, NonterminalUsingAttributeSample|\ArrayObject $bar) {}
            public function ruleBuzz(
                string $foo, NonterminalUsingAttributeSample|\ArrayObject $bar,
                TerminalUsingAttributeSample&\ArrayAccess $buzz
            ) { }
        };
        $header = new NonterminalSchema($header::class);

        return [
            [$header, 'ruleFoo', TerminalSchema::class, NonterminalSchema::class],
            [$header, 'ruleBar', TerminalSchema::class, NonterminalSchema::class],
            [$header, 'ruleBaz', TerminalSchema::class, NonterminalSchema::class],
            [$header, 'ruleBuzz', TerminalSchema::class, NonterminalSchema::class, TerminalSchema::class],
        ];
    }
}