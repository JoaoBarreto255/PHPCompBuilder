<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Tests\Lexer\Traits;

use JB255\PHPCompBuilder\Lexer\Pattern\TokenRuleIterator;
use JB255\PHPCompBuilder\Lexer\Pattern\TokenRulePattern;
use JB255\PHPCompBuilder\Lexer\Traits\BuildAndProcessTokenIteratorsTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BuildAndProcessTokenIteratorsTrait::class)]
class BuildAndProcessTokenIteratorsTraitTest extends TestCase
{
    use BuildAndProcessTokenIteratorsTrait;
    protected array $patterns = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->patterns = [
            new TokenRulePattern('varName', '/[a-z](\w|\d|\_)*/'),
            new TokenRulePattern('for','for', reserved:true),
            new TokenRulePattern('in','in', reserved:true),
            new TokenRulePattern('func','[A-Z][A-Za-z0-9_]*'),
            new TokenRulePattern('num', '\d+'),
            new TokenRulePattern('__ignoreToken', '/\s+/'),
        ];
    }

    public function testFactoryIteratorsFromLine()
    {
        $this->setLine('for i2a in Range 10');
        $this->factoryIteratorsFromLine();
        $result = $this->iterators;
        $this->assertIsArray($result);
        $this->assertCount(6, $result);
        foreach ($result as $key => $iterator) {
            $msg = "fail at iterator row ({$key})";
            $this->assertInstanceOf(TokenRuleIterator::class, $iterator, $msg);
        }
    }
}
