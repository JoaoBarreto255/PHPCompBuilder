<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Tests;

trait ReflectionTrait
{
    protected function makeAccessibleMethod(string $class, string $method): \ReflectionMethod
    {
        $reflection = new \ReflectionClass($class);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);

        return $method;
    }
}