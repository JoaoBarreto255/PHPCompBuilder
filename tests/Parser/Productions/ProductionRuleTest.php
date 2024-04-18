<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Tests\Parser\Productions;

use JB255\PHPCompBuilder\Lexer\TokenInterface;
use JB255\PHPCompBuilder\Parser\Productions\ProductionRule;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\SymbolInterface;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\ClassTerminal;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\NonTerminal;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\Terminal;
use JB255\PHPCompBuilder\Tests\ReflectionTrait;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

class ProductionRuleTest extends TestCase
{
    use ReflectionTrait;

    public function testBuildBodyPattern(): void
    {
        $reflection = new \ReflectionClass(ProductionRule::class);

        $reflectionMethod = $reflection->getMethod('buildBodyPattern');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invoke(null);
        $this->assertSame(
            "/^(((?<term>'([[:punct:]]+|\w+)')|(?<nterm>[[:alpha:]][\w_]*)|(?<cterm>'[[:alpha:]](\w|\_)*(\\\[[:alpha:]](\w|\_)*)*'))\s*)*$/"
            , $result);
    }

    #[TestDox('test processMatchesType method')]
    #[DataProviderExternal(ProductionRuleDataProvider::class, 'processMatchesTypeDataProvider')]
    public function testProcessMatchesType(array $matches, string $classname): void
    {
        $mock = $this->getMockBuilder(ProductionRule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $method = $this->makeAccessibleMethod(ProductionRule::class, 'processMatchesType');
        $result = $method->invoke($mock, $matches, $classname);
        foreach ($result as $key => $match) {
            $this->assertIsArray($match);
            $this->assertCount(2, $match);
            [$symbol, $pos] = $match;
            $this->assertIsInt($pos);
            $this->assertSame($matches[$key][1], $pos);
            $this->assertNotNull($symbol);
            $this->assertInstanceOf(SymbolInterface::class, $symbol);
            $this->assertInstanceOf($classname, $symbol);
        }
    }

    #[DataProviderExternal(ProductionRuleDataProvider::class, 'processMatchesDataProvider')]
    public function testProcessMatches(array $matches, int $count,  array $expected_array_str): void
    {
        $mock = $this->getMockBuilder(ProductionRule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $method = $this->makeAccessibleMethod(ProductionRule::class, 'processMatches');

        $result = $method->invoke($mock, $matches);
        $this->assertIsArray($result);
        $this->assertCount($count, $result);

        foreach ($expected_array_str as $expected) {
            $symbol = current($result);
            $this->assertNotNull($symbol);
            $this->assertInstanceOf(SymbolInterface::class, $symbol);
            $this->assertSame($expected, $symbol->value);
            next($result);
        }
    }
}