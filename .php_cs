<?php

$header = <<<'EOF'
	This file is part of Code Ai.

	@version 1.0.0
	@author @小小只^v^ <littlezov@qq.com>  littlezov@qq.com
	@contact  littlezov@qq.com
	@link     https://github.com/littlezo
	@document https://github.com/littlezo/wiki
	@license  https://github.com/littlezo/MozillaPublicLicense/blob/main/LICENSE

	EOF;

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        '@PSR12' => true,
        // '@PHP54Migration'       => true,
        // '@PHP56Migration'       => true,
        // '@PHP56Migration:risky' => true,
        // '@PHP70Migration'       => true,
        // '@PHP70Migration:risky' => true,
        // '@PHP71Migration'       => true,
        // '@PHP71Migration:risky' => true,
        // '@PHP73Migration'       => true,
        // '@PHP74Migration'       => true,
        // '@PHP74Migration:risky' => true,
        // '@PHP80Migration'       => true,
        // '@PHP80Migration:risky' => true,
        // '@PSR12:risky'          => true,
        '@Symfony' => true,
        // '@Symfony:risky'        => true,
        '@DoctrineAnnotation' => true,
        '@PhpCsFixer' => true,
        // '@PhpCsFixer:risky'     => true,
        'header_comment' => [
            'commentType' => 'PHPDoc',
            'header' => $header,
            'separate' => 'none',
            'location' => 'after_declare_strict',
        ],
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'binary_operator_spaces' => [
            'align_double_arrow' => false,
            'align_equals' => false,
        ],
        'list_syntax' => [
            'syntax' => 'short',
        ],
        'concat_space' => [
            'spacing' => 'one',
        ],
        'blank_line_before_statement' => [
            'statements' => [
                'declare',
            ],
        ],
        'general_phpdoc_annotation_remove' => [
            'annotations' => [
                'date',
            ],
        ],
        'ordered_imports' => [
            'imports_order' => [
                'class', 'function', 'const',
            ],
            'sort_algorithm' => 'alpha',
        ],
        'single_line_comment_style' => [
            'comment_types' => [
            ],
        ],
        'yoda_style' => [
            'always_move_variable' => false,
            'equal' => false,
            'identical' => false,
        ],
        'phpdoc_align' => [
            'align' => 'left',
        ],
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'no_multi_line',
        ],
        'no_trailing_comma_in_singleline_array' => true,
        'trailing_comma_in_multiline_array' => false,
        'no_trailing_comma_in_list_call' => true,
        'constant_case' => [
            'case' => 'lower',
        ],
        'class_attributes_separation' => true,
        'combine_consecutive_unsets' => true,
        'declare_strict_types' => true,
        'linebreak_after_opening_tag' => true,
        'lowercase_static_reference' => true,
        'no_useless_else' => true,
        'no_unused_imports' => true,
        'not_operator_with_successor_space' => true,
        'not_operator_with_space' => false,
        'ordered_class_elements' => true,
        'php_unit_strict' => false,
        'phpdoc_separation' => false,
        'single_quote' => true,
        'standardize_not_equals' => true,
        'multiline_comment_opening_closing' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->files()
            ->name('*.php')
            ->exclude('public')
            ->exclude('runtime')
            ->exclude('vendor')
            ->in(__DIR__)
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
    )
    // ->setIndent("\t")
    // ->setLineEnding("\n")
    ->setUsingCache(false);
