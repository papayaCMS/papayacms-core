<?php

class PapayaDatabaseConditionElement {

  private $_parent = NULL;
  private $_field = '';
  private $_value = '';

  protected $_operator = '=';

  public function __construct(
    PapayaDatabaseConditionGroup $parent, $field = '', $value = NULL, $operator = NULL
  ) {
    $this->_parent = $parent;
    $this->_field = $field;
    $this->_value = $value;
    if (isset($operator)) {
      $this->_operator = $operator;
    }
  }

  public function getDatabaseAccess() {
    return $this->getParent()->getDatabaseAccess();
  }

  public function getMapping() {
    return ($parent = $this->getParent()) ? $this->getParent()->getMapping() : NULL;
  }

  public function getParent() {
    return $this->_parent;
  }

  public function getField() {
    return $this->_field;
  }

  public function getSql($silent = FALSE) {
    try {
      return $this->getDatabaseAccess()->getSqlCondition(
        array(
          $this->mapFieldName($this->_field, $silent) => $this->_value
        ),
        NULL,
        $this->_operator
      );
    } catch (LogicException $e) {
      if (!$silent) {
        throw $e;
      }
      return '';
    }
  }

  public function __toString() {
    $result = $this->getSql(TRUE);
    return $result ? $result : '';
  }

  protected function mapFieldName($name) {
    if (empty($name)) {
      throw new LogicException(
        'Can not generate condition, provided name was empty.'
      );
    }
    if ($mapping = $this->getMapping()) {
      $field = $mapping->getField($name);
    } else {
      $field = $name;
    }
    if (empty($field)) {
      throw new LogicException(
        sprintf(
          'Can not generate condition, given name "%s" could not be mapped to a field.',
          $name
        )
      );
    }
    return $field;
  }
}
