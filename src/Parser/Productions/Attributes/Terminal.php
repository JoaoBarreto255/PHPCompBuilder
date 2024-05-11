<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Parser\Productions;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_PROPERTY)]
readonly class Terminal
{
    public function __construct(
        public int|string|null $id = null
    ) { }
}