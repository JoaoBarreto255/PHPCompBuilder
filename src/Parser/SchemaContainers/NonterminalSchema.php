<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Parser\SchemaContainers;

use JB255\PHPCompBuilder\Parser\Attributes\Nonterminal;
use JB255\PHPCompBuilder\Parser\Contracts\NonTerminalInterface;

/**
 * Container for nonterminal symbols.
 *
 * @property string $name Class used to map non terminal symbol.
 */
readonly class NonterminalSchema
{
    public function __construct(
        public string $name,
    ) {
        if (empty($name)) {
            throw new \InvalidArgumentException('Nonterminal symbol name must not be empty');
        }

        if (!class_exists($name)) {
            throw new \InvalidArgumentException("Nonterminal symbol \"{$name}\" is not a valid class");
        }

        $reflection = new \ReflectionClass($name);
        if (!$reflection->implementsInterface(NonTerminalInterface::class)
            && 0 === count($reflection->getAttributes(Nonterminal::class))
        ) {
            throw new \InvalidArgumentException(sprintf('NonTerminal Symbol "%s" type class must implements "%s" or have attribute "%s""', $name, NonTerminalInterface::class, Nonterminal::class));
        }
    }
}
