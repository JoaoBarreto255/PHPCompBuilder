<?php

// require 'vendor/autoload.php';

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        '@PhpCsFixer' => true,
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        // // 'strict_param' => true,
        // 'max_line_length' => [
        //     'max' => 79,
        //     'ignore_comments' => true,
        // ],
        'no_trailing_whitespace' => true,
        'phpdoc_align' => true,
    ])->setFinder($finder);