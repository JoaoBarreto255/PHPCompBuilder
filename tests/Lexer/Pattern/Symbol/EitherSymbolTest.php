<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Tests\Lexer\Pattern\Symbol;

use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol\ClassSymbol;
use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol\EitherSymbol;
use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol\GroupSymbol;
use Joaobarreto255\PhpCompBuilder\Lexer\Pattern\Symbol\UniqueSymbol;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
#[CoversClass(EitherSymbol::class)]
class EitherSymbolTest extends TestCase
{
    #[TestDox('Test newFrom from Either symbols')]
    public function testNewFrom()
    {
        $a = UniqueSymbol::newFrom('a');
        $b = UniqueSymbol::newFrom('b');
        $numbers = ClassSymbol::newFrom('0-9');
        $hexaAlfa = ClassSymbol::newFrom('A-F');

        $this->assertInstanceOf(ClassSymbol::class, EitherSymbol::newFrom($a, $b));
        $this->assertSame('ab', EitherSymbol::newFrom($a, $b)->value);
        $this->assertInstanceOf(ClassSymbol::class, EitherSymbol::newFrom($a, $numbers));
        $this->assertSame('0123456789a', EitherSymbol::newFrom($a, $numbers)->value);
        $this->assertInstanceOf(ClassSymbol::class, EitherSymbol::newFrom($b, $numbers));
        $this->assertSame('0123456789b', EitherSymbol::newFrom($b, $numbers)->value);
        $this->assertInstanceOf(ClassSymbol::class, EitherSymbol::newFrom($hexaAlfa, $numbers));
        $this->assertSame('0123456789ABCDEF', EitherSymbol::newFrom($hexaAlfa, $numbers)->value);

        // arity != 1
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($a->symbolMaybeExistOrRepeat(), $b));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($a->symbolMaybeRepeat(), $b));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($a->symbolMayNotHappen(), $b));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($a->symbolWillRepeatNTimes(2), $b));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($a->symbolWillRepeatFromNToMTimes(2, 3), $b));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($a, $b->symbolMaybeExistOrRepeat()));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($a, $b->symbolMaybeRepeat()));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($a, $b->symbolMayNotHappen()));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($a, $b->symbolWillRepeatNTimes(2)));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($a, $b->symbolWillRepeatFromNToMTimes(2, 3)));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($a, $numbers->symbolMaybeExistOrRepeat()));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($a, $numbers->symbolMaybeRepeat()));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($a, $numbers->symbolMayNotHappen()));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($a, $numbers->symbolWillRepeatNTimes(2)));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($a, $numbers->symbolWillRepeatFromNToMTimes(2, 3)));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($hexaAlfa, $numbers->symbolMaybeExistOrRepeat()));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($hexaAlfa->symbolMaybeExistOrRepeat(), $numbers));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($hexaAlfa, $numbers->symbolMaybeRepeat()));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($hexaAlfa->symbolMaybeRepeat(), $numbers));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($hexaAlfa, $numbers->symbolMayNotHappen()));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($hexaAlfa->symbolMayNotHappen(), $numbers));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($hexaAlfa, $numbers->symbolWillRepeatNTimes(2)));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($hexaAlfa->symbolWillRepeatNTimes(2), $numbers));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($hexaAlfa, $numbers->symbolWillRepeatFromNToMTimes(2, 3)));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($hexaAlfa->symbolWillRepeatFromNToMTimes(2, 3), $numbers));

        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom(GroupSymbol::newFrom([$a, $b]), $numbers));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($numbers, GroupSymbol::newFrom([$a, $b])));
        $this->assertInstanceOf(EitherSymbol::class, EitherSymbol::newFrom($numbers, EitherSymbol::newFrom($a, $b->symbolMaybeRepeat())));
    }
}
