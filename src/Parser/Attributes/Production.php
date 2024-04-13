<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Parser\Attributes;

use JB255\PHPCompBuilder\Parser\Productions\ProductionRule;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\NonTerminal;

/**
 * Attribute container builder for production.
 *
 * Ex.:
 * ```glc
 * EXPR: EXPR '+' EXPR
 *     | EXPR '-' EXPR
 *     | PROD_EXPR
 * ```
 * ```php
 * class Parser extends AbstractParser
 * {
 *      #[Production('EXPR',
 *          "EXPR '+' EXPR",
 *          "EXPR '-' EXPR",
 *          "PROD_EXPR"
 *      )]
 *      public function mathOps()
 *      {
 *      }
 * }
 * ```
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
readonly class Production
{
    public NonTerminal $header;

    /**
     * @var ProductionRule[]
     */
    public array $rules;

    public function __construct(
        string $header,
        string ...$rules,
    ) {
        $this->header = new NonTerminal($header);

        $stack = [];
        foreach ($rules as $ruleStr) {
            $stack[] = new ProductionRule($this->header, $ruleStr);
        }

        $this->rules = $stack;
    }
}
