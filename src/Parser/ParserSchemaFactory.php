<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Parser;

use JB255\PHPCompBuilder\Parser\Attributes\Nonterminal;
use JB255\PHPCompBuilder\Parser\Contracts\NonTerminalInterface;
use JB255\PHPCompBuilder\Parser\Attributes\Production;
use JB255\PHPCompBuilder\Parser\Attributes\Terminal;

/**
 * Get from start non terminal the chain of Productions to be used in parser.
 * 
 * @property string $startProduction Class name from start non terminal.
 */
final class ParserSchemaFactory
{
    private array $schema = [];

    public function __construct(
        public readonly string $startSymbolClass
    ) {
        if (!class_exists($startSymbolClass)) {
            throw new \LogicException("Argument is not a defined class");
        }

        $reflection = new \ReflectionClass($startSymbolClass);
        if (!$reflection->getAttributes(Production::class)
            && !$reflection->implementsInterface(NonTerminalInterface::class)
        ) {
            throw new \LogicException(sprintf(
                '"%s" does not implements interface "%s" or has attribute "%s"',
                $startSymbolClass,
                NonTerminalInterface::class,
                Production::class
            ));
        }
        
        # TODO: build Schema here.
    }
}
