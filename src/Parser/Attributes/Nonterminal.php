<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Parser\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Nonterminal
{
}