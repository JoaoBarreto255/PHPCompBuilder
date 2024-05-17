<?php

namespace JB255\PHPCompBuilder\Parser\SchemaContainers;

use JB255\PHPCompBuilder\Parser\Attributes\Production;
use JB255\PHPCompBuilder\Parser\SchemaContainers\Exceptions\InvalidSymbolParameterException;

/**
 * Container for production constructor.
 *
 * @property NonterminalSchema                    $header      Nonterminal in left side in rule, which is replaced by symbol rules.
 * @property (NonterminalSchema|TerminalSchema)[] $bodySymbols List of symbols used to replace Nonterminal header.
 */
readonly class ProductionSchema
{
    public array $symbols;

    public function __construct(
        public NonterminalSchema $header,
        public string $methodName,
    ) {
        $method = new \ReflectionMethod($header->name, $methodName);

        if (!$method->getAttributes(Production::class) && 0 === preg_match('/^(production)|(rule).+/', $methodName)) {
            throw new \LogicException('Invalid production method', 404);
        }

        $symbols = [];

        foreach ($method->getParameters() as $param) {
            $type = $param->getType();

            // is terminal symbol.
            if (!$param->hasType() || $type->isBuiltin() && 'string' === $type->getName()) {
                $symbols[] = new TerminalSchema($param->getName());
                continue;
            }

            if ($type->isBuiltin()) {
                throw $this->buildInvalidSymbolException($param);
            }

            if ($type instanceof \ReflectionUnionType
                || $type instanceof \ReflectionIntersectionType
            ) {
                $symbols[] = $this->processMultiTypesFields($param);
            }

            $symbols[] = new NonterminalSchema($type->getName());
        }

        $this->symbols = $symbols;
    }

    protected function processMultiTypesFields(
        \ReflectionParameter $param
    ): TerminalSchema|NonterminalSchema {
        $composed = $param->getType();
        $notBuiltin = array_filter($composed->getTypes(), fn ($st) => !$st->isBuiltin());
        $builtin = array_filter($composed->getTypes(), fn ($st) => $st->isBuiltin());
        $hasStringType = in_array('string', $builtin, true);
        if (empty($notBuiltin) && !$hasStringType) {
            throw $this->buildInvalidSymbolException($param);
        }

        foreach ($notBuiltin as $subtype) {
            try {
                return new NonterminalSchema($subtype);
            } catch (\InvalidArgumentException $e) {
                continue;
            }
        }

        if ($hasStringType) {
            return new TerminalSchema($param->getName());
        }

        throw $this->buildInvalidSymbolException($param);
    }

    protected function buildInvalidSymbolException(
        \ReflectionParameter $param
    ): InvalidSymbolParameterException {
        return new InvalidSymbolParameterException(
            $this->header->name, $param->getName(), $this->methodName
        );
    }

    public function getNonTerminals(): array
    {
        return array_filter(
            $this->symbols, fn ($symbol) => $symbol instanceof NonterminalSchema
        );
    }

    public function getTerminals(): array
    {
        return array_filter(
            $this->symbols, fn ($symbol) => $symbol instanceof TerminalSchema
        );
    }
}
