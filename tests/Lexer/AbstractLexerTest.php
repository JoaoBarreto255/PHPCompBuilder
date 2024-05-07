<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Tests\Lexer;

use JB255\PHPCompBuilder\Lexer\AbstractLexer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
#[CoversClass(AbstractLexer::class)]
class AbstractLexerTest extends TestCase
{
    protected function buildSampleLexer(array $iterator = []): AbstractLexer
    {   
        return new FakeLexer(new \ArrayIterator($iterator), 'test.php');
    }

    protected function exposeHiddenMethod(string $methodName, AbstractLexer $lexer): \Closure
    {
        $reflection = new \ReflectionClass($lexer);
        $reflMethod = $reflection->getMethod($methodName);
        $reflMethod->setAccessible(true);

        return $reflMethod->getClosure($lexer);
    }

    public function testBuildTokenStream()
    {
        $lexer = $this->buildSampleLexer([
            "abc for i23 inoske 234 in\n\r",
            "   \t\n",
            "   \t Foo For\n",
        ]);
        $method = $this->exposeHiddenMethod('buildTokenStream', $lexer);
        $tokenStream = $method();

        $this->assertTrue($tokenStream->valid());
        $token = $tokenStream->current();
        $this->assertIsArray($token);
        $this->assertCount(4, $token);
        $this->assertSame(['value' => 'abc', 'pos' => 0, 'lineno' => 0, 'col' => 0], $token);
        $tokenStream->next();

        $this->assertTrue($tokenStream->valid());
        $token = $tokenStream->current();
        $this->assertIsArray($token);
        $this->assertCount(4, $token);
        $this->assertSame([ 'value' => 'for', 'pos' => 4, 'lineno' => 0, 'col' => 4], $token);
        $tokenStream->next();

        $this->assertTrue($tokenStream->valid());
        $token = $tokenStream->current();
        $this->assertIsArray($token);
        $this->assertCount(4, $token);
        $this->assertSame(['value' => 'i23', 'pos' => 8, 'lineno' => 0, 'col' => 8], $token);
        $tokenStream->next();

        $this->assertTrue($tokenStream->valid());
        $token = $tokenStream->current();
        $this->assertIsArray($token);
        $this->assertCount(4, $token);
        $this->assertSame(['value' => 'inoske', 'pos' => 12, 'lineno' => 0, 'col' => 12], $token);
        $tokenStream->next();

        $this->assertTrue($tokenStream->valid());
        $token = $tokenStream->current();
        $this->assertIsArray($token);
        $this->assertCount(4, $token);
        $this->assertSame(['value' => '234', 'pos' => 19, 'lineno' => 0, 'col' => 19], $token);
        $tokenStream->next();

        $this->assertTrue($tokenStream->valid());
        $token = $tokenStream->current();
        $this->assertIsArray($token);
        $this->assertCount(4, $token);
        $this->assertSame(['value' => 'in', 'pos' => 23, 'lineno' => 0, 'col' => 23], $token);
        $tokenStream->next();

        $this->assertTrue($tokenStream->valid());
        $token = $tokenStream->current();
        $this->assertIsArray($token);
        $this->assertCount(4, $token);
        $this->assertSame(['value' => 'Foo', 'pos' => 37, 'lineno' => 2, 'col' => 5], $token);
        $tokenStream->next();

        $this->assertTrue($tokenStream->valid());
        $token = $tokenStream->current();
        $this->assertIsArray($token);
        $this->assertCount(4, $token);
        $this->assertSame(['value' => 'For', 'pos' => 41, 'lineno' => 2, 'col' => 9], $token);
        $tokenStream->next();
    }
}
