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
namespace Papaya\Database;

use Papaya\Database\Condition\Condition;
use Papaya\Utility;

/**
 * Papaya Database List, represents a list of records fetched from the database.
 *
 * @package Papaya-Library
 * @subpackage Database
 */
abstract class Records
  extends Records\Unbuffered
  implements \ArrayAccess {
  /**
   * internal storage for the record da after mapping.
   *
   * @var array
   */
  protected $_records = [];

  /**
   * An array of properties, used to compile the identifier
   *
   * @var array(string)
   */
  protected $_identifierProperties = [];

  /**
   * The parts of an identifier a joined using the given separator string
   *
   * @var string
   */
  protected $_identifierSeparator = '|';

  /**
   * Load records from the defined table. This method can be overloaded to define an own sql.
   *
   * @param mixed $filter If it is a scalar the value will be used for the id property.
   * @param int|null $limit
   * @param int|null $offset
   *
   * @return bool
   */
  public function load($filter = [], $limit = NULL, $offset = NULL) {
    $fields = \implode(', ', $this->mapping()->getFields());
    $sql = "SELECT $fields FROM %s";
    $sql .= Utility\Text::escapeForPrintf(
      $this->_compileCondition($filter).$this->_compileOrderBy()
    );
    $parameters = [
      $this->getDatabaseAccess()->getTableName($this->_tableName, $this->_useTablePrefix)
    ];
    return $this->_loadRecords($sql, $parameters, $limit, $offset, $this->_identifierProperties);
  }

  /**
   * @param array|bool $filterOrAll delete records defined by the filter or all if it is set to true
   *
   * @return bool
   */
  public function truncate($filterOrAll = FALSE) {
    $databaseAccess = $this->getDatabaseAccess();
    if ($filterOrAll instanceof Condition) {
      $statement = $databaseAccess->prepare(
        'DELETE FROM :table WHERE '.$filterOrAll->getSQL()
      );
      $statement->addTableName('table', $this->_tableName);
      return FALSE !== $statement->execute();
    }
    if (\is_array($filterOrAll) && !empty($filterOrAll)) {
      return (
        FALSE !== $databaseAccess->deleteRecord(
          $databaseAccess->getTableName($this->_tableName),
          $this->mapping()->mapPropertiesToFields($filterOrAll, FALSE)
        )
      );
    }
    if (\is_bool($filterOrAll) && $filterOrAll) {
      return (
        FALSE !== $databaseAccess->emptyTable(
          $databaseAccess->getTableName($this->_tableName)
        )
      );
    }
    return FALSE;
  }

  /**
   * @param \Traversable|[] $data
   *
   * @return bool
   */
  public function insert($data) {
    Utility\Constraints::assertArrayOrTraversable($data);
    $databaseAccess = $this->getDatabaseAccess();
    $records = [];
    foreach ($data as $values) {
      $records[] = $this->mapping()->mapPropertiesToFields($values, FALSE);
    }
    return $databaseAccess->insertRecords($databaseAccess->getTableName($this->_tableName), $records);
  }

  /**
   * A protected method that does the actual loading. The separation allows to overload load, to
   * create and own logic that defines the sql and parameters.
   *
   * @param string $sql
   * @param array $parameters
   * @param int|null $limit
   * @param int|null $offset
   * @param array $idProperties if set the defined fields are used to create the keys for the
   *                            records array. If it is an empty array the records array will be a list.
   *
   * @return bool
   */
  protected function _loadRecords($sql, array $parameters, $limit, $offset, $idProperties = []) {
    $this->reset();
    if ($this->_loadSql($sql, $parameters, $limit, $offset)) {
      foreach ($this->getResultIterator() as $values) {
        $identifier = $this->getIdentifier($values, $idProperties);
        if (NULL !== $identifier) {
          $this->_records[$identifier] = $values;
        } else {
          $this->_records[] = $values;
        }
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Reset the object to "unloaded" status
   */
  public function reset() {
    $this->_records = [];
  }

  /**
   * Return the current count of records in the internal buffer
   *
   * @return int
   */
  public function count(): int {
    return \count($this->_records);
  }

  /**
   * Return loaded records as array
   *
   * @return array
   */
  public function toArray() {
    return $this->_records;
  }

  /**
   * Get an iterator for the loaded records.
   *
   * @return \Iterator
   */
  public function getIterator(): \Traversable {
    return empty($this->_records) ? new \EmptyIterator() : new \ArrayIterator($this->_records);
  }

  /**
   * return true if an record with the given offset/identifier exists
   *
   * @param mixed $offset
   *
   * @return bool
   */
  public function offsetExists($offset): bool {
    return isset($this->_records[$this->getIdentifier($offset)]);
  }

  /**
   * return the record data of the result row.
   *
   * @param mixed $offset
   *
   * @return array|null
   */
  #[\ReturnTypeWillChange]
  public function offsetGet($offset) {
    $identifier = $this->getIdentifier($offset);
    return isset($this->_records[$identifier]) ? $this->_records[$identifier] : NULL;
  }

  /**
   * This is an encapsulation of the database result, you can not change it.
   *
   * @param mixed $offset
   * @param mixed $value
   */
  public function offsetSet($offset, $value): void {
    Utility\Constraints::assertArray($value);
    $identifier = $this->getIdentifier($offset);
    $record = [];
    foreach ($this->mapping()->getProperties() as $property) {
      if (isset($value[$property])) {
        $record[$property] = $value[$property];
      } else {
        $record[$property] = NULL;
      }
    }
    if (NULL !== $identifier) {
      $this->_records[$identifier] = $record;
    } else {
      $this->_records[] = $record;
    }
  }

  /**
   * This is an encapsulation of the database result, you can not change it.
   *
   * @param mixed $offset
   */
  public function offsetUnset($offset): void {
    $identifier = $this->getIdentifier($offset);
    if (isset($this->_records[$identifier])) {
      unset($this->_records[$identifier]);
    }
  }

  /**
   * Compiles different kind of values into an string identifier. If the filter is given
   * only the properties defined in the filter (corresponding to keys in the values array) are
   * used. If the $filter argument is an empty array the method returns NULL.
   *
   * If the $filter argument is NULL, all values in the $values argument are used.
   *
   * @param mixed $values
   * @param mixed $filter
   *
   * @throws \UnexpectedValueException
   *
   * @return string|null
   */
  protected function getIdentifier($values, $filter = NULL) {
    if (NULL !== $filter) {
      if (!\is_array($filter)) {
        $filter = [$filter];
      }
      if (empty($filter)) {
        return NULL;
      }
      $identifier = [];
      foreach ($filter as $property) {
        if (isset($values[$property])) {
          $identifier[] = $values[$property];
        } else {
          throw new \UnexpectedValueException(
            \sprintf(
              'The property "%s" was not found, but is needed to create the identifier.',
              $property
            )
          );
        }
      }
      return \implode($this->_identifierSeparator, $identifier);
    }
    if (\is_array($values)) {
      return \implode($this->_identifierSeparator, $values);
    }
    return NULL !== $values ? (string)$values : NULL;
  }
}
