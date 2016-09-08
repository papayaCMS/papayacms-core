<?php

class PapayaDatabaseConditionContains extends PapayaDatabaseConditionElement {

  private $_parent = NULL;
  private $_field = '';
  private $_value = '';

  public function __construct(
    PapayaDatabaseConditionGroup $parent, $field, $value
  ) {
    $value = (string)$value;
    $hasWildcards = (FALSE !== strpos($value, '*')) || (FALSE !== strpos($value, '?'));
    if (!$hasWildcards) {
      $value = '*'.$value.'*';
    }
    $value = str_replace(['%', '*', '?'], ['%%', '%', '_'], $value);
    parent::__construct($parent, $field, $value, 'LIKE');
  }
}
