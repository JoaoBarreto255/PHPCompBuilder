# JB255 PHPCompBuilder

**Build compilers in PHP with ease!** (Experimental)

## Description

This package provides tools to simplify the development of compilers in PHP, with a focus on the lexical analysis stage.

## Key Features

- **AbstractLexer class**: Serves as a foundation for building custom lexers, streamlining the process.
- **TokenRulePattern attribute**: Define token patterns efficiently using regular expressions.
- **Enhanced token access**: Retrieve token information like value, position, line number, and column within lexer methods.
## Getting Started

### 1. Installation

```bash
composer require jb255/php-comp-builder
```
### 2. Creating a Lexer

Follow these steps to create a custom lexer:

- Extend the AbstractLexer class.
- Define public methods using the TokenRulePattern attribute to specify token patterns. These methods will process matching tokens.
- implement an ignorePattern() method to define patterns for whitespace or other tokens to be ignored.
- Access token information using value(), position(), lineNumber(), and column() methods within rule methods.
Example:

```php
<?php

declare(strict_types=1);

namespace App\MyLexer;

use JB255\PHPCompBuilder\Lexer\AbstractLexer;
use JB255\PHPCompBuilder\Lexer\Pattern\TokenRulePattern;

class CustomTextLexer extends AbstractLexer
{
    #[TokenRulePattern('/\w(\w\d)*/')]
    public function words(): WordToken
    {
        return new WordToken($this->value(), $this->position());
    }

    // ... other token rules

    public function ignorePattern(): string
    {
        return '/\s+/';
    }
}
```
3. Using the Lexer

- Create an instance of your lexer, passing an `\Iterator` (could be `\SplFileObject`) in the constructor.
- Iterate over the lexer to process tokens:

```php
$file = new \SplFileObject('mob-dick.txt');
$lexer = new CustomTextLexer($file, 'mob-dick.txt');

foreach ($lexer as $token) {
    // Process each token
}
```

Version: 1.0.0 (experimental)
License: MIT
Author: João Barreto
