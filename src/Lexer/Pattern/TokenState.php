<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Lexer\Pattern;

readonly class TokenState
{
    public int $len;

    public function __construct(
        public TokenRulePattern $tokenRule,
        public string $value,
    ) {
        $this->len = strlen($value);
    }

    /**
     * Compares current state with another.
     * case current is bigger than other: 1.
     * case current is smaller than other: -1.
     * case current is as big as other and is reserved: 1.
     * case current is as big as other and isn't reserved and other is: -1.
     * Else is always 0.
     *
     * @return int between -1 and 1
     */
    public function compare(self $other): int
    {
        $diff = $this->len - $other->len;

        return match ([$diff, $this->isReserved()]) {
            [0, true] => 1,
            [0, false] => -(int) $other->isReserved(),
            default => $diff / abs($diff),
        };
    }

    public function isReserved(): bool
    {
        return $this->tokenRule->reserved;
    }

    public function getTokenFactory(): TokenFactory
    {
        return $this->tokenRule->tokenFactory;
    }
}
