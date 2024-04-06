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
            public function varNames(): array
            {
                return $this->makeReturn(__METHOD__);
            }

            #[TokenRulePattern('for', true)]
            #[TokenRulePattern('in', true)]
            public function reseverd(): array
            {
                return $this->makeReturn(__METHOD__);
            }

            #[TokenRulePattern('[A-Z][A-Za-z0-9_]')]
            public function func(): array
            {
                return $this->makeReturn(__METHOD__);
            }

            #[TokenRulePattern('\d+')]
            public function num(): array
            {
                return $this->makeReturn(__METHOD__);
            }

            public function ignorePattern(): string
            {
                return '/\s+/';
            }
        };
    }

    public function testGetTokenRuleFromMethods()
    {
        $lexer = $this->buildSampleLexer();

        $reflection = new \ReflectionClass($lexer);
        $reflMethod = $reflection->getMethod('getTokenRuleFromMethods');
        $reflMethod->setAccessible(true);
        $method = $reflMethod->getClosure($lexer);
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
            ['varNames', '/[a-z](\w|\d|\_)*/', false],
            ['reseverd', '/for/', true],
            ['reseverd', '/in/', true],
            ['func', '/[A-Z][A-Za-z0-9_]/', false],
            ['num', '/\d+/', false],
            ['ignorePatternAction', '/\s+/', false],
        ], $refinedResult);
    }

    public function testFactoryIteratorsFromInput()
    {
        $lexer = $this->buildSampleLexer(['for i2a in Range 10']);

        $reflection = new \ReflectionClass($lexer);
        $reflMethod = $reflection->getMethod('factoryIteratorsFromInput');
        $reflMethod->setAccessible(true);
        $method = $reflMethod->getClosure($lexer);
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
}
