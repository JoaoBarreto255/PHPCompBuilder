<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Parser\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
final readonly class Terminal
{
    public function __construct(
        public int|string|null $id = null
    ) {
    }
}
