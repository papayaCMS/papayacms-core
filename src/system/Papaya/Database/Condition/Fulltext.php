<?php

abstract class PapayaDatabaseConditionFulltext {

  private $_parent = NULL;
  protected $_fields = '';
  protected $_searchFor = '';

  public function __construct(
    PapayaDatabaseConditionGroup $parent, $fields = '', $searchFor
  ) {
    $this->_parent = $parent;
    $this->_fields = is_array($fields) ? $fields : [$fields];
    $this->_searchFor = $searchFor;
    if (isset($operator)) {
      $this->_operator = $operator;
    }
  }

  /**
   * @param PapayaParserSearchString $tokens
   * @param array|Traversable $fields
   * @return mixed
   */
  abstract protected function getFullTextCondition(PapayaParserSearchString $tokens, array $fields);

  public function getDatabaseAccess() {
    return $this->getParent()->getDatabaseAccess();
  }

  public function getMapping() {
    return ($parent = $this->getParent()) ? $this->getParent()->getMapping() : NULL;
  }

  public function getParent() {
    return $this->_parent;
  }

  public function getSql($silent = FALSE) {
    try {
      $tokens = new PapayaParserSearchString($this->_searchFor);
      return $this->getFullTextCondition($tokens, array_map([$this, 'mapFieldName'], $this->_fields));
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

  private function mapFieldName($name) {
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
