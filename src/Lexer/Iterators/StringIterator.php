<?php

declare(strict_types=1);

namespace Joaobarreto255\PhpCompBuilder\Lexer\Iterators;

class StringIterator implements \Iterator
{
    private int $position = 0;
    private int $size;
  
    public function __construct(
        readonly public string $string,
        readonly protected bool $reverse = false,
    ) {
        $this->size = strlen($this->string);
        
        if ($this->reverse) {
            $this->position = $this->size - 1;
        }
    }

    public function getSize(): int
    {
        return $this->size;
    }
  
    public function rewind(): void
    {
        $this->position = !$this->reverse
            ? 0
            : $this->size - 1;
    }
  
    public function current(): ?string
    {
        if (0 > $this->position || $this->position >= $this->getSize()) {
            return null;
        }

        return $this->string[$this->position];
    }
  
    public function key(): int
    {
        return $this->position;
    }
  
    public function next(): void 
    {
        if ($this->reverse) {
            $this->position--;

            return;
        }
        $this->position++;
    }

    public function previous(): void
    {
        if (0 !== $this->position && !$this->reverse) {
            $this->position++;
        }

        if (1 - $this->size > $this->position && $this->reverse) {
            $this->position--;
        }
    }
  
    public function valid(): bool
    {
        return 0 <= $this->position 
            && $this->position < $this->size;
    }
}