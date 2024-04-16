<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Parser\Attributes;

use JB255\PHPCompBuilder\Lexer\TokenInterface;

/**
 * Registry tokens used in parsers.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
readonly class TokenAlias
{
    public ?string $alias;

    public function __construct(
        public string|TokenInterface $token,
        ?string $alias = null,
    ) {
        if (is_string($token) && empty($token)) {
            throw new \LogicException('Parser Error. String Token must be not empty', 1);
        }

        if ($alias) {
            $this->alias = $alias;

            return;
        }

        $this->alias = is_string($token) ? $token : $token::class;
    }
}
