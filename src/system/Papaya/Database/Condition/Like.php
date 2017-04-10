<?php

class PapayaDatabaseConditionLike extends PapayaDatabaseConditionElement {

  private $_parent = NULL;
  private $_field = '';
  private $_value = '';

  public function __construct(
    PapayaDatabaseConditionGroup $parent, $field = '', $value = NULL
  ) {
    $this->_value = $value;
    parent::__construct($parent, $field, $value);
  }

  public function getSql($silent = FALSE) {
    $values = is_array($this->_value) ? $this->_value : array($this->value);
    $likeValues = [];
    $inValues = [];
    foreach ($values as $value) {
      $hasWildcards = preg_match('([*?])', $value);
      if ($hasWildcards) {
        $likeValues[] = str_replace(['%', '*', '?'], ['%%', '%', '_'], $value);
      } else {
        $inValues[] = (string)$value;
      }
    }
    try {
      $fields = $this->getField();
      if (!is_array($fields)) {
        $fields = array($fields);
      }
      $conditions = [];
      foreach ($fields as $field) {
        if (count($inValues) > 0) {
          $conditions[] = $this->getDatabaseAccess()->getSqlCondition(
            array(
              $this->mapFieldName($field, $silent) => $inValues
            ),
            NULL,
            '='
          );
        }
        if (count($likeValues) > 0) {
          $conditions[] = $this->getDatabaseAccess()->getSqlCondition(
            array(
              $this->mapFieldName($field, $silent) => $likeValues
            ),
            NULL,
            'LIKE'
          );
        }
      }
      return ' ('.implode(' OR ', $conditions).') ';
    } catch (LogicException $e) {
      if (!$silent) {
        throw $e;
      }
      return '';
    }
  }
}
