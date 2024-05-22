<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Tests\Parser\SchemaContainers;


use JB255\PHPCompBuilder\Parser\SchemaContainers\NonterminalSchema;
use JB255\PHPCompBuilder\Parser\SchemaContainers\ProductionSchema;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProductionSchema::class)]
class ProductionSchemaTest extends TestCase
{
    #[DataProviderExternal(
        ProductionSchemaDataProvider::class,
        'failOnCreateProductionSchemaOfInvalidMethodDataProvider'
    )]
    public function testFailOnCreateProductionSchemaOfInvalidMethod(NonterminalSchema $header, string $method, string $exceptionType)
    {
        $this->expectException($exceptionType);
        new ProductionSchema($header, $method);
    }

    #[DataProviderExternal(
        ProductionSchemaDataProvider::class, 'failOnProcessMultiTypesFields'
    )]
    public function testFailOnProcessMultiTypesFields(ProductionSchema $schema, \ReflectionParameter $param, string $exceptionType)
    {
        $this->expectException($exceptionType);
        $reflection = new \ReflectionMethod($schema, 'processMultiTypesFields');
        $reflection->setAccessible(true);
        $reflection->invoke($schema, $param);
    }

    #[DataProviderExternal(ProductionSchemaDataProvider::class, 'createDataProvider')]
    public function testCreate(NonterminalSchema $header, string $method, string ...$expected)
    {
        $schema = new ProductionSchema($header, $method);
        $this->assertCount(count($expected), $schema->symbols);
        $result = array_map(fn($s, $e) => [$s::class, $e], $schema->symbols, $expected);
        foreach ($result as [$symbolClass, $classname]) {
            $this->assertSame($classname, $symbolClass);
        }
    }
}
