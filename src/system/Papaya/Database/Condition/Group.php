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
namespace Papaya\Database\Condition;

use Papaya\Database;

/**
 * @method self logicalAnd()
 * @method self logicalOr()
 * @method self logicalNot()
 * @method $this isEqual(string $field, mixed $value)
 * @method $this isNotEqual(string $field, mixed $value)
 * @method $this isNull(string $field, mixed $value)
 * @method $this isGreaterThan(string $field, mixed $value)
 * @method $this isGreaterThanOrEqual(string $field, mixed $value)
 * @method $this isLessThan(string $field, mixed $value)
 * @method $this isLessThanOrEqual(string $field, mixed $value)
 * @method $this contains(string $field, mixed $value)
 * @method $this like(string $field, mixed $value)
 * @method $this match(string $field, mixed $value)
 * @method $this matchBoolean(string $field, mixed $value)
 * @method $this matchContains(string $field, mixed $value)
 */
class Group
  extends Element
  implements \IteratorAggregate, \Countable {
  private $_conditions = [];

  /**
   * @var Database\Access
   */
  private $_databaseAccess;

  private $_mapping;

  private $_classes = [
    'isequal' => [Element::class, '='],
    'isnotequal' => [Element::class, '!='],
    'isnull' => [Element::class, 'ISNULL'],
    'isgreaterthan' => [Element::class, '>'],
    'isgreaterthanorequal' => [Element::class, '>='],
    'islessthan' => [Element::class, '<'],
    'islessthanorequal' => [Element::class, '<=']
  ];

  /**
   * @param self|Database\Access|Database\Interfaces\Access $parent
   * @param Database\Interfaces\Mapping $mapping
   * @param string $operator
   *
   * @throws \InvalidArgumentException
   */
  public function __construct(
    $parent, Database\Interfaces\Mapping $mapping = NULL, $operator = 'AND'
  ) {
    if ($parent instanceof self) {
      parent::__construct($parent, NULL, NULL, $operator);
    } elseif ($parent instanceof Database\Interfaces\Access) {
      $this->_databaseAccess = $parent->getDatabaseAccess();
    } elseif ($parent instanceof Database\Access) {
      $this->_databaseAccess = $parent;
    } else {
      throw new \InvalidArgumentException(
        \sprintf('Invalid parent class %s in %s', \get_class($parent), __METHOD__)
      );
    }
    $this->_mapping = $mapping;
    $this->_operator = $operator;
  }

  public function end() {
    return $this->getParent();
  }

  /**
   * @return Database\Access
   */
  public function getDatabaseAccess() {
    if (NULL !== $this->_databaseAccess) {
      return $this->_databaseAccess;
    }
    return parent::getDatabaseAccess();
  }

  /**
   * @return null|Database\Interfaces\Mapping
   */
  public function getMapping() {
    if (NULL !== $this->_mapping) {
      return $this->_mapping;
    }
    return parent::getMapping();
  }

  /**
   * @param string $methodName
   * @param array $arguments
   * @return $this|self
   */
  public function __call($methodName, $arguments) {
    $name = \strtolower($methodName);
    switch ($name) {
      case 'logicaland' :
        $this->_conditions[] = $condition = new self($this, NULL, 'AND');
        return $condition;
      case 'logicalor' :
        $this->_conditions[] = $condition = new self($this, NULL, 'OR');
        return $condition;
      case 'logicalnot' :
        $this->_conditions[] = $condition = new self($this, NULL, 'NOT');
        return $condition;
      case 'contains' :
        $this->_conditions[] = $condition = new Contains($this, ...$arguments);
        return $this;
      case 'like' :
        $this->_conditions[] = $condition = new Like($this, ...$arguments);
        return $this;
      case 'match' :
        $this->_conditions[] = $condition = new Fulltext\Match($this, ...$arguments);
        return $this;
      case 'matchboolean' :
        $this->_conditions[] = $condition = new Fulltext\Boolean($this, ...$arguments);
        return $this;
      case 'matchcontains' :
        $this->_conditions[] = $condition = new Fulltext\Contains($this, ...$arguments);
        return $this;
      default :
        if (isset($this->_classes[$name])) {
          list($field, $value) = $arguments;
          list($className, $operator) = $this->_classes[$name];
          $this->_conditions[] = new $className($this, $field, $value, $operator);
          return $this;
        }
    }
    throw new \BadMethodCallException(
      \sprintf('Invalid condition create method %s::%s().', \get_class($this), $methodName)
    );
  }

  /**
   * @return \Traversable
   */
  public function getIterator() {
    return new \ArrayIterator($this->_conditions);
  }

  /**
   * @return int
   */
  public function count() {
    return \count($this->_conditions);
  }

  /**
   * @param bool $silent
   * @return string
   */
  public function getSql($silent = FALSE) {
    switch ($this->_operator) {
      case 'OR' :
        $glue = ' OR ';
      break;
      case 'NOT' :
      case 'AND' :
      default:
        $glue = ' AND ';
    }
    $result = '';
    /** @var Element $condition */
    foreach ($this as $condition) {
      if ($sql = $condition->getSql($silent)) {
        $result .= $glue.$sql;
      }
    }
    $result = \substr($result, \strlen($glue));
    if (empty($result)) {
      return '';
    }
    if ('NOT' === $this->_operator) {
      return 'NOT('.$result.')';
    }
    return '('.$result.')';
  }
}
