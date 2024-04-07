<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Tests\Lexer;

use Joaobarreto255\PhpCompBuilder\Lexer\AbstractLexer;
use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\TokenRuleIterator;
use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\TokenRulePattern;
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
        return new class(new \ArrayIterator($iterator), 'teste.php') extends AbstractLexer {
            public function makeReturn(string $method): array
            {
                return [
                    $this->value(),
                    $this->position(),
                    $this->lineNumber(),
                    $this->column(),
                    $this->line(),
                    $method,
                ];
            }

            #[TokenRulePattern('/[a-z](\w|\d|\_)*/')]
            public function varName(): array
            {
                return $this->makeReturn(__FUNCTION__);
            }

            #[TokenRulePattern('for', true)]
            #[TokenRulePattern('in', true)]
            public function reserved(): array
            {
                return $this->makeReturn(__FUNCTION__);
            }

            #[TokenRulePattern('[A-Z][A-Za-z0-9_]*')]
            public function func(): array
            {
                return $this->makeReturn(__FUNCTION__);
            }

            #[TokenRulePattern('\d+')]
            public function num(): array
            {
                return $this->makeReturn(__FUNCTION__);
            }

            public function ignorePattern(): string
            {
                return '/\s+/';
            }
        };
    }

    protected function exposeHiddenMethod(string $methodName, AbstractLexer $lexer): \Closure
    {
        $reflection = new \ReflectionClass($lexer);
        $reflMethod = $reflection->getMethod($methodName);
        $reflMethod->setAccessible(true);

        return $reflMethod->getClosure($lexer);
    }

    public function testGetTokenRuleFromMethods()
    {
        $lexer = $this->buildSampleLexer();
        $method = $this->exposeHiddenMethod('getTokenRuleFromMethods', $lexer);

        $result = $method();
        $this->assertIsArray($result);
        $this->assertCount(6, $result);

        $refinedResult = [];
        foreach ($result as $key => $arr) {
            $this->assertIsString($arr['method'], 'method must be string! (key: '.$key.')');
            $this->assertTrue(!empty($arr['method']), 'method must not be empty! (key: '.$key.')');
            $this->assertNotNull($arr['tokenRule'], 'tokenRule must not be empty! (key: '.$key.')');
            $this->assertInstanceOf(TokenRulePattern::class, $arr['tokenRule']);
            $refinedResult[] = [
                $arr['method'],
                $arr['tokenRule']->pattern,
                $arr['tokenRule']->reserved,
            ];
        }

        $this->assertSame([
            ['varName', '/[a-z](\w|\d|\_)*/', false],
            ['reserved', '/for/', true],
            ['reserved', '/in/', true],
            ['func', '/[A-Z][A-Za-z0-9_]*/', false],
            ['num', '/\d+/', false],
            ['ignorePatternAction', '/\s+/', false],
        ], $refinedResult);
    }

    public function testFactoryIteratorsFromInput()
    {
        $lexer = $this->buildSampleLexer(['for i2a in Range 10']);
        $method = $this->exposeHiddenMethod('factoryIteratorsFromInput', $lexer);

        $result = $method();

        $this->assertIsArray($result);
        $this->assertCount(6, $result);
        foreach ($result as $key => $itArr) {
            $msg = "fail at iterator row ({$key})";
            $this->assertIsArray($itArr, $msg);
            $this->assertCount(2, $itArr, $msg);
            $this->assertArrayHasKey('method', $itArr, $msg);
            $this->assertIsString($itArr['method'], $msg);
            $this->assertNotEmpty($itArr['method'], $msg);
            $this->assertArrayHasKey('iterator', $itArr, $msg);
            $this->assertInstanceOf(TokenRuleIterator::class, $itArr['iterator']);
        }
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
        $this->assertCount(6, $token);
        $this->assertSame(['abc', 0, 0, 0, "abc for i23 inoske 234 in\n\r", 'varName'], $token);
        $tokenStream->next();

        $this->assertTrue($tokenStream->valid());
        $token = $tokenStream->current();
        $this->assertIsArray($token);
        $this->assertCount(6, $token);
        $this->assertSame(['for', 4, 0, 4, "abc for i23 inoske 234 in\n\r", 'reserved'], $token);
        $tokenStream->next();

        $this->assertTrue($tokenStream->valid());
        $token = $tokenStream->current();
        $this->assertIsArray($token);
        $this->assertCount(6, $token);
        $this->assertSame(['i23', 8, 0, 8, "abc for i23 inoske 234 in\n\r", 'varName'], $token);
        $tokenStream->next();

        $this->assertTrue($tokenStream->valid());
        $token = $tokenStream->current();
        $this->assertIsArray($token);
        $this->assertCount(6, $token);
        $this->assertSame(['inoske', 12, 0, 12, "abc for i23 inoske 234 in\n\r", 'varName'], $token);
        $tokenStream->next();

        $this->assertTrue($tokenStream->valid());
        $token = $tokenStream->current();
        $this->assertIsArray($token);
        $this->assertCount(6, $token);
        $this->assertSame(['234', 19, 0, 19, "abc for i23 inoske 234 in\n\r", 'num'], $token);
        $tokenStream->next();

        $this->assertTrue($tokenStream->valid());
        $token = $tokenStream->current();
        $this->assertIsArray($token);
        $this->assertCount(6, $token);
        $this->assertSame(['in', 23, 0, 23, "abc for i23 inoske 234 in\n\r", 'reserved'], $token);
        $tokenStream->next();

        $this->assertTrue($tokenStream->valid());
        $token = $tokenStream->current();
        $this->assertIsArray($token);
        $this->assertCount(6, $token);
        $this->assertSame(['Foo', 37, 2, 5, "   \t Foo For\n", 'func'], $token);
        $tokenStream->next();

        $this->assertTrue($tokenStream->valid());
        $token = $tokenStream->current();
        $this->assertIsArray($token);
        $this->assertCount(6, $token);
        $this->assertSame(['For', 41, 2, 9, "   \t Foo For\n", 'func'], $token);
        $tokenStream->next();
    }
}
