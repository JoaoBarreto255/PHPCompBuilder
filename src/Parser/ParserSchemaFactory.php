<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Parser;

use JB255\PHPCompBuilder\Parser\SchemaContainers\NonterminalSchema;
use JB255\PHPCompBuilder\Parser\SchemaContainers\ProductionSchema;

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
        $unprocessedSymbols = [$startSymbolClass];
        while ($unprocessedSymbols) {
            $classname = array_pop($unprocessedSymbols);
            foreach (self::getProductionsFromSymbolClass($classname) as $production) {
                if (!isset($this->schema[$classname])) {
                    $this->schema[$classname] = [];
                }

                $this->schema[$classname][] = $production;
                foreach ($production->getNonTerminals() as $nonTerminal) {
                    if (isset($this->schema[$nonTerminal->name])) {
                        continue;
                    }

                    $unprocessedSymbols[] = $nonTerminal->name;
                }
            }
        }
    }

    public function getSchema(): array
    {
        return $this->schema;
    }

    public static function getProductionsFromSymbolClass(string $classname)
    {
        $header = new NonterminalSchema($classname);

        $reflection = new \ReflectionClass($classname);
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ('__construct' === $method->name) {
                throw new \LogicException(sprintf('Constructor cannot be used as grammar production! Class: %s', $classname));
            }

            if ($method->isAbstract()) {
                continue;
            }
            try {
                yield new ProductionSchema($header, $method->name);
            } catch (\LogicException $e) {
                continue;
            }
        }
    }
}
