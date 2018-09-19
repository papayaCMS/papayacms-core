<?php
$header = <<<'EOF'
papaya CMS

@copyright 2000-2018 by papayaCMS project - All rights reserved.
@link http://www.papaya-cms.com/
@license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2

You can redistribute and/or modify this script under the terms of the GNU General Public
License (GPL) version 2, provided that the copyright and license notes, including these
lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE.
EOF;

return PhpCsFixer\Config::create()
  ->setRiskyAllowed(true)
  ->setIndent('  ')
  ->setRules(
    [
      'array_indentation' => TRUE,
      'array_syntax' => ['syntax' => 'short'],
      'binary_operator_spaces' => [
        'align_double_arrow' => FALSE,
        'align_equals' => FALSE
      ],
      'blank_line_after_namespace' => TRUE,
      'blank_line_after_opening_tag' => FALSE,
      'blank_line_before_return' => FALSE,
      'blank_line_before_statement' => FALSE,
      'braces' => [
        'position_after_anonymous_constructs' => 'same',
        'position_after_control_structures' => 'same',
        'position_after_functions_and_oop_constructs' => 'same'
      ],
      'cast_spaces' => FALSE,
      'concat_space' => ['spacing' => 'none'],
      'class_attributes_separation' => TRUE,
      'class_definition' => FALSE,
      'dir_constant' => TRUE,
      'elseif' => TRUE,
      'encoding' => TRUE,
      'escape_implicit_backslashes' => TRUE,
      'full_opening_tag' => TRUE,
      'function_declaration' => [
        'closure_function_spacing' => 'none'
      ],
      'function_to_constant' => TRUE,
      'function_typehint_space' => TRUE,
      'general_phpdoc_annotation_remove' => ['author','version'],
      'hash_to_slash_comment' => TRUE,
      'header_comment' => ['header' => $header, 'separate' => 'none'],
      'indentation_type' => FALSE,
      'line_ending' => TRUE,
      'linebreak_after_opening_tag' => TRUE,
      'logical_operators' => TRUE,
      'lowercase_constants' => FALSE,
      'lowercase_keywords' => TRUE,
      'lowercase_cast' => TRUE,
      'lowercase_static_reference' => TRUE,
      'magic_constant_casing' => TRUE,
      'method_argument_space' => TRUE,
      'method_chaining_indentation' => TRUE,
      'method_separation' => TRUE,
      'modernize_types_casting' => TRUE,
      'native_function_casing' => TRUE,
      'native_function_invocation' => TRUE,
      'new_with_braces' => TRUE,
      'no_alias_functions' => TRUE,
      'no_alternative_syntax' => TRUE,
      'no_blank_lines_after_class_opening' => FALSE,
      'no_blank_lines_after_phpdoc' => TRUE,
      'no_break_comment' => FALSE,
      'no_closing_tag' => TRUE,
      'no_empty_comment' => TRUE,
      'no_empty_phpdoc' => TRUE,
      'no_empty_statement' => TRUE,
      'no_extra_consecutive_blank_lines' => TRUE,
      'no_leading_import_slash' => TRUE,
      'no_leading_namespace_whitespace' => TRUE,
      'no_null_property_initialization' => TRUE,
      'no_short_bool_cast' => TRUE,
      'no_short_echo_tag' => TRUE,
      'no_singleline_whitespace_before_semicolons' => TRUE,
      'no_spaces_after_function_name' => TRUE,
      'no_spaces_around_offset' => ['inside', 'outside'],
      'no_spaces_inside_parenthesis' => TRUE,
      'no_trailing_comma_in_list_call' => TRUE,
      'no_trailing_comma_in_singleline_array' => TRUE,
      'no_trailing_whitespace' => TRUE,
      'no_trailing_whitespace_in_comment' => TRUE,
      'no_unused_imports' => TRUE,
      'no_whitespace_in_blank_line' => TRUE,
      'ordered_imports' => TRUE,
      'phpdoc_align' => [
        'align' => 'left'
      ],
      'phpdoc_indent' => TRUE,
      'phpdoc_no_access' => TRUE,
      'phpdoc_no_empty_return' => TRUE,
      'phpdoc_no_package' => FALSE,
      'phpdoc_scalar' => TRUE,
      'phpdoc_separation' => FALSE,
      'phpdoc_to_comment' => TRUE,
      'phpdoc_trim' => TRUE,
      'phpdoc_types' => TRUE,
      'phpdoc_var_without_name' => FALSE,
      'self_accessor' => TRUE,
      'short_scalar_cast' => TRUE,
      'simplified_null_return' => FALSE,
      'single_blank_line_at_eof' => TRUE,
      'single_import_per_statement' => TRUE,
      'single_line_after_imports' => TRUE,
      'single_quote' => TRUE,
      'ternary_operator_spaces' => TRUE,
      'trim_array_spaces' => TRUE,
      'unary_operator_spaces' => TRUE,
      'visibility_required' => TRUE,
      'whitespace_after_comma_in_array' => TRUE,
      'yoda_style' => TRUE
    ]
  )
  ->setFinder(
    PhpCsFixer\Finder::create()
    ->files()
    ->in(__DIR__ . '/src/system/Papaya')
    ->name('*.php')
  );
