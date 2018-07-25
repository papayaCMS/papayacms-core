<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

/**
 * @method PapayaDatabaseConditionGroup logicalAnd()
 * @method PapayaDatabaseConditionGroup logicalOr()
 * @method PapayaDatabaseConditionGroup logicalNot()
 * @method PapayaDatabaseConditionGroup isEqual(string $field, mixed $value)
 * @method PapayaDatabaseConditionGroup isNotEqual(string $field, mixed $value)
 * @method PapayaDatabaseConditionGroup isNull(string $field, mixed $value)
 * @method PapayaDatabaseConditionGroup isGreaterThan(string $field, mixed $value)
 * @method PapayaDatabaseConditionGroup isGreaterThanOrEqual(string $field, mixed $value)
 * @method PapayaDatabaseConditionGroup isLessThan(string $field, mixed $value)
 * @method PapayaDatabaseConditionGroup isLessThanOrEqual(string $field, mixed $value)
 * @method PapayaDatabaseConditionGroup contains(string $field, mixed $value)
 * @method PapayaDatabaseConditionGroup like(string $field, mixed $value)
 * @method PapayaDatabaseConditionGroup match(string $field, mixed $value)
 * @method PapayaDatabaseConditionGroup matchBoolean(string $field, mixed $value)
 * @method PapayaDatabaseConditionGroup matchContains(string $field, mixed $value)
 */
class PapayaDatabaseConditionGroup
  extends \PapayaDatabaseConditionElement
  implements \IteratorAggregate, \Countable {

  private $_conditions = array();
  private $_databaseAccess = NULL;
  private $_mapping = NULL;

  private $_classes = array(
    'isequal' => array(\PapayaDatabaseConditionElement::class, '='),
    'isnotequal' => array(\PapayaDatabaseConditionElement::class, '!='),
    'isnull' => array(\PapayaDatabaseConditionElement::class, 'ISNULL'),
    'isgreaterthan' => array(\PapayaDatabaseConditionElement::class, '>'),
    'isgreaterthanorequal' => array(\PapayaDatabaseConditionElement::class, '>='),
    'islessthan' => array(\PapayaDatabaseConditionElement::class, '<'),
    'islessthanorequal' => array(\PapayaDatabaseConditionElement::class, '<=')
  );

  /**
   * @param \PapayaDatabaseConditionGroup|\PapayaDatabaseAccess|\PapayaDatabaseInterfaceAccess $parent
   * @param \PapayaDatabaseInterfaceMapping $mapping
   * @param string $operator
   * @throws \InvalidArgumentException
   */
  public function __construct(
    $parent, \PapayaDatabaseInterfaceMapping $mapping = NULL, $operator = 'AND'
  ) {
    if ($parent instanceof \PapayaDatabaseConditionGroup) {
      parent::__construct($parent, NULL, NULL, $operator);
    } elseif ($parent instanceof \PapayaDatabaseInterfaceAccess) {
      $this->_databaseAccess = $parent->getDatabaseAccess();
    } elseif ($parent instanceof \PapayaDatabaseAccess) {
      $this->_databaseAccess = $parent;
    } else {
      throw new \InvalidArgumentException(
        sprintf('Invalid parent class %s in %s', get_class($parent), __METHOD__)
      );
    }
    $this->_mapping = $mapping;
    $this->_operator = $operator;
  }

  public function end() {
    return $this->getParent();
  }

  public function getDatabaseAccess() {
    if (isset($this->_databaseAccess)) {
      return $this->_databaseAccess;
    }
    return parent::getDatabaseAccess();
  }

  public function getMapping() {
    if (isset($this->_mapping)) {
      return $this->_mapping;
    }
    return parent::getMapping();
  }

  public function __call($methodName, $arguments) {
    $name = strtolower($methodName);
    switch ($name) {
    case 'logicaland' :
      $this->_conditions[] = $condition = new \PapayaDatabaseConditionGroup($this, NULL, 'AND');
      return $condition;
    case 'logicalor' :
      $this->_conditions[] = $condition = new \PapayaDatabaseConditionGroup($this, NULL, 'OR');
      return $condition;
    case 'logicalnot' :
      $this->_conditions[] = $condition = new \PapayaDatabaseConditionGroup($this, NULL, 'NOT');
      return $condition;
    case 'contains' :
      list($field, $value) = $arguments;
      $this->_conditions[] = $condition = new \PapayaDatabaseConditionContains($this, $field, $value);
      return $condition;
    case 'like' :
      list($field, $value) = $arguments;
      $this->_conditions[] = $condition = new \PapayaDatabaseConditionLike($this, $field, $value);
      return $condition;
    case 'match' :
      list($fields, $value) = $arguments;
      $this->_conditions[] = $condition = new \PapayaDatabaseConditionFulltextMatch($this, $fields, $value);
      return $condition;
    case 'matchboolean' :
      list($fields, $value) = $arguments;
      $this->_conditions[] = $condition = new \PapayaDatabaseConditionFulltextBoolean($this, $fields, $value);
      return $condition;
    case 'matchcontains' :
      list($fields, $value) = $arguments;
      $this->_conditions[] = $condition = new \PapayaDatabaseConditionFulltextContains($this, $fields, $value);
      return $condition;
    default :
      if (isset($this->_classes[$name])) {
        list($field, $value) = $arguments;
        list($className, $operator) = $this->_classes[$name];
        $this->_conditions[] = new $className($this, $field, $value, $operator);
        return $this;
      }
    }
    throw new \BadMethodCallException(
      sprintf('Invalid condition create method %s::%s().', get_class($this), $methodName)
    );
  }

  public function getIterator() {
    return new \ArrayIterator($this->_conditions);
  }

  public function count() {
    return count($this->_conditions);
  }

  public function getSql($silent = FALSE) {
    switch ($this->_operator) {
    case 'OR' :
      $concatinator = ' OR ';
      break;
    case 'NOT' :
    case 'AND' :
    default:
      $concatinator = ' AND ';
    }
    $result = '';
    /** @var \PapayaDatabaseConditionElement $condition  */
    foreach ($this as $condition) {
      if ($sql = $condition->getSql($silent)) {
        $result .= $concatinator.$sql;
      }
    }
    $result = substr($result, strlen($concatinator));
    if (empty($result)) {
      return '';
    } elseif ($this->_operator == 'NOT') {
      return 'NOT('.$result.')';
    } else {
      return '('.$result.')';
    }
  }
}
