<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Tests\Parser\Productions;

use JB255\PHPCompBuilder\Parser\Productions\ProductionRule;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\ClassTerminal;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\NonTerminal;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\Terminal;
use JB255\PHPCompBuilder\Tests\ReflectionTrait;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

class ProductionRuleTest extends TestCase
{
    use ReflectionTrait;

    public function testCreateProductionRule()
    {
        $production = new ProductionRule(new NonTerminal('foo'), "expr 'if' boolexpr 'else' expr");
        $this->assertCount(5, $production->rule);

        $production = new ProductionRule(new NonTerminal('foo'), "'id' '(' args ')'");
        $this->assertCount(4, $production->rule);

        $production = new ProductionRule(new NonTerminal('foo'), sprintf("'%s' '=' '%s'", NonTerminal::class, NonTerminal::class));
        $this->assertCount(3, $production->rule);
        $this->assertInstanceOf(ClassTerminal::class, $production->rule[0]);
        $this->assertInstanceOf(Terminal::class, $production->rule[1]);
        $this->assertInstanceOf(ClassTerminal::class, $production->rule[2]);
    }
}