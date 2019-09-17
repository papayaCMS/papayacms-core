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
namespace Papaya\Database\Records;

use Papaya\Application;
use Papaya\Database;
use Papaya\Utility;

abstract class Unbuffered
  implements Application\Access, Database\Accessible, \IteratorAggregate, \Countable {
  use Application\Access\Aggregation;

  /**
   * Stored database access object
   *
   * @var Database\Access
   */
  private $_databaseAccessObject;

  /**
   * The database result of the last loading query.
   *
   * @var Database\Result
   */
  private $_databaseResult;

  /**
   * Mapping object
   *
   * @var Database\Interfaces\Mapping
   */
  private $_mapping;

  /**
   * Order object
   *
   * @var Database\Interfaces\Order
   */
  private $_orderBy;

  /**
   * An array of property to field mappings.
   *
   * @var array(string=>string)
   */
  protected $_fields = [];

  /**
   * An array of order by properties and directions.
   *
   * @var array(string=>integer)|NULL
   */
  protected $_orderByProperties;

  /**
   * An array of order by fields and directions
   */
  protected $_orderByFields;

  /**
   * Table name for the default loading logic.
   *
   * @var string
   */
  protected $_tableName = '';

  /**
   * Add table prefix from global configuration
   *
   * @var bool
   */
  protected $_useTablePrefix = TRUE;

  /**
   * @var string
   */
  protected $_itemClass;

  /**
   * Load records from the defined table. This method can be overloaded to define an own sql.
   *
   * @param mixed $filter If it is an scalar the value will be used for the id property.
   * @param int|null $limit
   * @param int|null $offset
   *
   * @return bool
   */
  public function load($filter = NULL, $limit = NULL, $offset = NULL) {
    $fields = \implode(', ', $this->mapping()->getFields());
    $sql = "SELECT $fields FROM %s";
    $sql .= Utility\Text::escapeForPrintf(
      $this->_compileCondition($filter).$this->_compileOrderBy()
    );
    $parameters = [
      $this->getDatabaseAccess()->getTableName($this->_tableName, $this->_useTablePrefix)
    ];
    return $this->_loadSql($sql, $parameters, $limit, $offset);
  }

  /**
   * Execute the sql query and store the result object
   *
   * @param string|Database\Statement $sql
   * @param array $parameters
   * @param int|null $limit
   * @param int|null $offset
   *
   * @return bool
   */
  protected function _loadSql($sql, $parameters, $limit = NULL, $offset = NULL) {
    $this->_databaseResult = NULL;
    $databaseAccess = $this->getDatabaseAccess();
    $databaseResult = $databaseAccess->queryFmt($sql, $parameters, $limit, $offset);
    if ($databaseResult instanceof Database\Result) {
      $this->_databaseResult = $databaseResult;
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Create a filter condition object attached to this database accesss and mapping
   *
   * @return Database\Condition\Root
   */
  public function createFilter() {
    return new Database\Condition\Root($this, $this->mapping());
  }

  /**
   * Compile a sql condition specified by the filter. Prefix it, if it is not empty.
   *
   * @param mixed $filter
   * @param string $prefix
   *
   * @return string
   */
  protected function _compileCondition($filter, $prefix = ' WHERE ') {
    if (NULL !== $filter) {
      if ($filter instanceof Database\Condition\Element) {
        $condition = $filter->getSql();
        return empty($condition) ? '' : $prefix.$condition;
      }
      if (!\is_array($filter)) {
        $filter = ['id' => $filter];
      }
      $generator = new Database\Condition\Generator($this, $this->mapping());
      $condition = $generator->fromArray($filter)->getSql(TRUE);
      return empty($condition) ? '' : $prefix.$condition;
    }
    return '';
  }

  /**
   * Convert the order by clause defined by the orderBy() return value into an sql string.
   *
   * @return string
   */
  protected function _compileOrderBy() {
    $result = '';
    if ($orderBy = $this->orderBy()) {
      $result = (string)$orderBy;
    }
    return empty($result) ? '' : ' ORDER BY '.$result;
  }

  /**
   * Getter/Setter for the mapping subobject. This is used to convert the property values into
   * a database record and back.
   *
   * @param Database\Interfaces\Mapping $mapping
   *
   * @return Database\Interfaces\Mapping
   */
  public function mapping(Database\Interfaces\Mapping $mapping = NULL) {
    if (NULL !== $mapping) {
      $this->_mapping = $mapping;
    } elseif (NULL === $this->_mapping) {
      $this->_mapping = $this->_createMapping();
    }
    return $this->_mapping;
  }

  /**
   * Create a standard mapping object for the property $_fields.
   *
   * @return Database\Record\Mapping
   */
  protected function _createMapping() {
    return new Database\Record\Mapping($this->_fields);
  }

  /**
   * Getter/Setter for the order subobject. This is used to define a order by clause for the
   * select statement. It is possible that the method return FALSE, indicating that
   * here should be no order by clause.
   *
   * @param Database\Interfaces\Order $orderBy
   *
   * @return Database\Interfaces\Order|false
   */
  public function orderBy(Database\Interfaces\Order $orderBy = NULL) {
    if (NULL !== $orderBy) {
      $this->_orderBy = $orderBy;
    } elseif (NULL === $this->_orderBy) {
      $this->_orderBy = $this->_createOrderBy();
    }
    return $this->_orderBy;
  }

  /**
   * Create a standard order object using the property $_orderByFields. If the property is empty
   * the method will return FALSE.
   *
   * @return Database\Interfaces\Order|false
   */
  protected function _createOrderBy() {
    if (empty($this->_orderByProperties) && empty($this->_orderByFields)) {
      return FALSE;
    }
    $result = new Database\Record\Order\Group();
    if (!empty($this->_orderByProperties)) {
      $result->add(
        new Database\Record\Order\By\Properties($this->_orderByProperties, $this->mapping())
      );
    }
    if (!empty($this->_orderByFields)) {
      $result->add(
        new Database\Record\Order\By\Fields($this->_orderByFields)
      );
    }
    return $result;
  }

  /**
   * Return the current count of records in the internal buffer
   *
   * @return int
   */
  public function count() {
    if ($databaseResult = $this->databaseResult()) {
      return $databaseResult->count();
    }
    return 0;
  }

  /**
   * Fetch the absolute count from the last database result. If the result was limited, this
   * number can be different from the record count.
   *
   * @return int
   */
  public function absCount() {
    if ($databaseResult = $this->databaseResult()) {
      return $databaseResult->absCount();
    }
    return $this->count();
  }

  /**
   * Return loaded records as array
   *
   * @return array
   */
  public function toArray() {
    return \iterator_to_array($this);
  }

  /**
   * IteratorAggregate interface, return and iterator for the database result
   *
   * @return \Iterator
   */
  public function getIterator() {
    return $this->getResultIterator();
  }

  /**
   * Iterator for the curent database result, includes mapping callback
   *
   * @return \Iterator
   */
  protected function getResultIterator() {
    if (!($this->databaseResult() instanceof Database\Result)) {
      return new \EmptyIterator();
    }
    $iterator = new Database\Result\Iterator($this->databaseResult());
    $mapping = $this->mapping();
    $iterator->setMapping(
      $mapping instanceof Database\Record\Mapping\Cache
        ? $mapping
        : new Database\Record\Mapping\Cache($mapping)
    );
    return $iterator;
  }

  /**
   * Getter/Setter for the current database result object
   *
   * @param Database\Result $databaseResult
   *
   * @return null|Database\Result
   */
  public function databaseResult(Database\Result $databaseResult = NULL) {
    if (NULL !== $databaseResult) {
      $this->_databaseResult = $databaseResult;
    }
    return $this->_databaseResult;
  }

  /**
   * Set database access object
   *
   * @param Database\Access $databaseAccessObject
   */
  public function setDatabaseAccess(Database\Access $databaseAccessObject) {
    $this->_databaseAccessObject = $databaseAccessObject;
  }

  /**
   * Get database access object
   *
   * @return Database\Access
   */
  public function getDatabaseAccess() {
    if (NULL === $this->_databaseAccessObject) {
      $this->_databaseAccessObject = $this->papaya()->database->createDatabaseAccess();
    }
    return $this->_databaseAccessObject;
  }

  /**
   * Protected method to create an item class, you can overload this method or just set
   * the $_itemClass property.
   *
   * @return \Papaya\Database\Record
   *
   * @throws \LogicException
   */
  protected function _createItem() {
    if (NULL !== $this->_itemClass) {
      $class = $this->_itemClass;
      return new $class();
    }
    throw new \LogicException('No item class for records defined');
  }

  /**
   * Get a record item object. If the filter is not empty 'load()' will be called on
   * the $item object.
   *
   * @param null $filter
   *
   * @return \Papaya\Database\Record
   */
  public function getItem($filter = NULL) {
    $item = $this->_createItem();
    if (!empty($filter)) {
      $item->load($filter);
    }
    return $item;
  }
}
