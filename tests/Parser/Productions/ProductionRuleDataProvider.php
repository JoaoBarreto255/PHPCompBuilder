<?php

declare(strict_types=1);

namespace JB255\PHPCompBuilder\Tests\Parser\Productions;

// use JB255\PHPCompBuilder\Parser\Productions\ProductionRule;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\SymbolInterface;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\ClassTerminal;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\NonTerminal;
use JB255\PHPCompBuilder\Parser\Productions\Symbols\Terminal;

class ProductionRuleDataProvider
{   
    public static function processMatchesTypeDataProvider(): array
    {
        // 'for' params '<-' expr 'if' bool_expr 'do'
        // 'App\Tokens\Id' '=' bool_expr
        // 'end'
        return [
            'nterm' => [[
                ['params', 1],
                ['expr', 3],
                ['bool_expr', 5],
                ['bool_expr', 9],
            ], NonTerminal::class],
            'term' => [[
                ["'for'", 0],
                ["'<-'", 2],
                ["'if'", 4],
                ["'do'", 6],
                ["'='", 8],
                ["'end'", 10],
            ], Terminal::class],
            'cterm' => [[
                ["'" . Terminal::class . "'", 7],
            ], ClassTerminal::class],
        ];
    }

    public static function processMatchesDataProvider(): array
    {
        $omatches = static::processMatchesTypeDataProvider();
        $omatches = array_map('current', $omatches);
        $o_expected_array_str = [
            "'for'", 'params', "'<-'", 'expr', "'if'", 'bool_expr',
            "'do'", sprintf("'%s'", Terminal::class), "'='", 'bool_expr', "'end'",
        ];

        
        return [
            [[], 0, []],
            [$omatches, 11, $o_expected_array_str],
            [
                array_merge($omatches, ['cterm' => []]),
                10,
                array_diff($o_expected_array_str, [ sprintf("'%s'", Terminal::class)]),
            ],
            [
                array_merge($omatches, ['nterm' => []]),
                7,
                array_diff($o_expected_array_str, ['params', 'expr', 'bool_expr']),
            ],
            [
                array_merge($omatches, ['term' => []]),
                5,
                array_diff($o_expected_array_str, ["'for'", "'<-'", "'if'", "'do'", "'='", "'end'"]),
            ],
        ];
    }
}