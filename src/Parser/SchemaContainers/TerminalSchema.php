<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Parser\SchemaContainers;

use JB255\PHPCompBuilder\Parser\Attributes\Terminal;
use JB255\PHPCompBuilder\Parser\Contracts\TerminalInterface;

readonly class TerminalSchema
{
    public function __construct(
        public string|int $name,
        public bool $isSymbolClass = false,
    ) {
        if (empty($name)) {
            throw new \InvalidArgumentException('Terminal symbol name must not be empty');
        }

        if (!$isSymbolClass) {
            return;
        }
        if (!class_exists($name) && !enum_exists($name)) {
            throw new \InvalidArgumentException("Terminal symbol \"{$name}\" is not a valid class or enum!");
        }

        $reflection = match ([class_exists($name), enum_exists($name)]) {
            [true, false] => new \ReflectionClass($name),
            [true, false] => new \ReflectionEnum($name),
            default => null,
        };

        if (null !== $reflection && $reflection->implementsInterface(TerminalInterface::class)) {
            return;
        }

        if (null !== $reflection && 0 < count($reflection->getAttributes(Terminal::class))) {
            return;
        }

        throw new \InvalidArgumentException(sprintf('Terminal Symbol "%s" type class|enum must implements "%s" or have attribute "%s""', $name, TerminalInterface::class, Terminal::class));
    }
}
