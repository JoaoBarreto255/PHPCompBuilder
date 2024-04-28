<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Parser;

use JB255\PHPCompBuilder\Parser\Attributes\Production;
use JB255\PHPCompBuilder\Parser\Attributes\TokenAlias;

abstract class AbstractParserSchema
{
    private array $schema = [];

    /**
     * @throws \LogicException - When there's no Token alias
     */
    final protected function searchTokenAlias(): void
    {
        $reflection = new \ReflectionClass($this);
        if (empty($reflectionAttrs = $reflection->getAttributes(TokenAlias::class))) {
            throw new \LogicException('All used tokens in lexer must have a token alias', 503);
        }

        $aliases = [];
        foreach ($reflectionAttrs as $reflectionAttr) {
            $tokenAlias = $reflectionAttr->newInstance();
            $aliases[] = $tokenAlias;
        }

        $this->schema['token_aliases'] = $aliases;
    }

    /**
     * @throws \LogicException - when there's no current productions
     */
    final protected function searchProductionMethods(): void
    {
        $reflectionClass = new \ReflectionClass($this);
        $reflectionMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);

        $productions = [];
        foreach ($reflectionMethods as $reflectionMethod) {
            if (empty($reflectionAttr = current($reflectionMethod->getAttributes(Production::class)))) {
                continue;
            }

            $productions[$reflectionMethod->getName()] = $reflectionAttr->newInstance();
        }

        if (empty($productions)) {
            throw new \LogicException('No productions or public methods with productions added!', 1);
        }

        $this->schema['productions'] = $productions;
    }

    /**
     * get class schema.
     */
    final public function getSchema(): array
    {
        $this->searchTokenAlias();
        $this->searchProductionMethods();

        // fake deep clone schema.
        $productions = $this->schema['productions'];
        $aliases = $this->schema['token_aliases'];

        return [
            'productions' => $productions,
            'token_aliases' => $aliases,
        ];
    }
}
