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

use Papaya\BaseObject;

/**
 * Papaya Database Record, superclass for easy database record encapsulation.
 *
 * @package Papaya-Library
 * @subpackage Database
 */
abstract class Record
  extends BaseObject\Item
  implements Interfaces\Record {
  /**
   * An array of property to field mappings.
   *
   * @var array(string=>string)
   */
  protected $_fields = [];

  /**
   * The table name for the default implementations
   *
   * @var string
   */
  protected $_tableName = '';

  /**
   * The table alias for selected field mappings, if set only fields with this
   * alias will be included in insert/update queries
   *
   * @var string|bool
   */
  protected $_tableAlias = FALSE;

  /**
   * Subobject for the database key handling
   *
   * @var Interfaces\Key
   */
  private $_key;

  /**
   * Subobject for the database field mapping
   *
   * @var Interfaces\Key
   */
  private $_mapping;

  /**
   * Stored database access object
   *
   * @var Access
   */
  private $_databaseAccessObject;

  /**
   * @var bool $_isLoaded loading indicator
   */
  private $_isLoaded = FALSE;

  /**
   * @var Record\Callbacks
   */
  private $_callbacks;

  /**
   * Create object and define properties
   */
  public function __construct() {
    parent::__construct(\array_keys($this->_fields));
  }

  /**
   * Clone key and mapping subjects, too.
   */
  public function __clone() {
    if (NULL !== $this->_key) {
      $this->_key = clone $this->_key;
    }
    if (NULL !== $this->_mapping) {
      $this->_mapping = clone $this->_mapping;
    }
  }

  /**
   * Load record data from specified database table. If the provided value is not an array it will
   * be used like array('id' => $filter).
   *
   * @param mixed $filter
   *
   * @return bool
   */
  public function load($filter) {
    $condition = \Papaya\Utility\Text::escapeForPrintf($this->_compileCondition($filter));
    $fields = \implode(
      ', ',
      $this->mapping()->getFields()
    );
    $sql = "SELECT $fields FROM %s $condition";
    $parameters = [
      $this->getDatabaseAccess()->getTableName($this->_tableName)
    ];
    return $this->_loadRecord($sql, $parameters);
  }

  /**
   * Compile filter into sql condition string
   *
   * @param $filter
   * @param string $prefix
   *
   * @return string
   */
  protected function _compileCondition($filter, $prefix = 'WHERE') {
    if ($filter instanceof Condition\Element) {
      $condition = $filter->getSql();
    } else {
      if (!\is_array($filter)) {
        $filter = ['id' => $filter];
      }
      $generator = new Condition\Generator($this, $this->mapping());
      $condition = (string)$generator->fromArray($filter);
    }
    return empty($condition) ? '' : $prefix.' '.$condition;
  }

  /**
   * Create a filter condition object attached to this database accesss and mapping
   *
   * @return Condition\Root
   */
  public function createFilter() {
    return new Condition\Root($this, $this->mapping());
  }

  /**
   * Allow to read the loading status
   *
   * @return bool
   */
  public function isLoaded() {
    return $this->_isLoaded;
  }

  /**
   * Save record to database
   *
   * @return bool|Interfaces\Key
   */
  public function save() {
    if ($this->key()->exists()) {
      return $this->_updateRecord();
    }
    return $this->_insertRecord();
  }

  /**
   * Delte record from database table
   *
   * @return bool
   */
  public function delete() {
    if (!$this->callbacks()->onBeforeDelete($this)) {
      return FALSE;
    }
    if ($filter = $this->key()->getFilter()) {
      $result = FALSE !== $this->getDatabaseAccess()->deleteRecord(
          $this->getDatabaseAccess()->getTableName($this->_tableName),
          $this->mapping()->mapPropertiesToFields($filter, $this->_tableAlias)
        );
      if ($result) {
        $this->callbacks()->onAfterDelete($this);
      }
      return $result;
    }
    return FALSE;
  }

  /**
   * Internal method to load record data using sql and paramters.
   *
   * @param string $sql
   * @param array $parameters
   *
   * @return bool
   */
  protected function _loadRecord($sql, array $parameters = NULL) {
    if (
      ($queryResult = $this->getDatabaseAccess()->queryFmt($sql, $parameters)) &&
      ($row = $queryResult->fetchRow(Result::FETCH_ASSOC))
    ) {
      $this->assign($this->mapping()->mapFieldsToProperties($row));
      $this->key()->assign($this->toArray());
      return $this->_isLoaded = TRUE;
    }
    return $this->_isLoaded = FALSE;
  }

  /**
   * Internal method to update database record.
   *
   * @return bool
   */
  protected function _updateRecord() {
    if (!$this->callbacks()->onBeforeUpdate($this)) {
      return FALSE;
    }
    $result = FALSE !== $this
      ->getDatabaseAccess()
      ->updateRecord(
          $this->getDatabaseAccess()->getTableName($this->_tableName),
          $this->mapping()->mapPropertiesToFields($this->toArray(), $this->_tableAlias),
          $this->mapping()->mapPropertiesToFields($this->key()->getFilter(), $this->_tableAlias)
        );
    if ($result) {
      $this->key()->assign($this->toArray());
      $this->callbacks()->onAfterUpdate($this);
    }
    return $result;
  }

  /**
   * Insert the record into the database table
   *
   * @return Interfaces\Key|false
   */
  protected function _insertRecord() {
    if (!$this->callbacks()->onBeforeInsert($this)) {
      return FALSE;
    }
    $record = $this->mapping()->mapPropertiesToFields($this->toArray(), $this->_tableAlias);
    $filter = $this->mapping()->mapPropertiesToFields(
      $this->key()->getFilter(Interfaces\Key::ACTION_CREATE), $this->_tableAlias
    );
    $qualities = $this->key()->getQualities();
    if ($qualities & Interfaces\Key::DATABASE_PROVIDED) {
      \reset($filter);
      $idField = \key($filter);
      if (\array_key_exists($idField, $record)) {
        unset($record[$idField]);
      }
    } else {
      $idField = NULL;
      foreach ($filter as $key => $value) {
        if (
          $qualities & Interfaces\Key::CLIENT_GENERATED ||
          !isset($record[$key])
        ) {
          $record[$key] = $value;
        }
      }
    }
    $result = $this
      ->getDatabaseAccess()
      ->insertRecord(
        $this->getDatabaseAccess()->getTableName($this->_tableName),
        $idField,
        $record
      );
    if (FALSE !== $result) {
      if (NULL !== $idField) {
        $record[$idField] = $result;
      }
      $this->assign($this->mapping()->mapFieldsToProperties($record));
      $this->key()->assign($this->toArray());
      $this->callbacks()->onAfterInsert($this);
      return $this->key();
    }
    return FALSE;
  }

  /**
   * Getter/Setter for the mapping subobject. This is used to convert the property values into
   * a database record and back.
   *
   * @param Interfaces\Mapping $mapping
   *
   * @return Interfaces\Mapping
   */
  public function mapping(Interfaces\Mapping $mapping = NULL) {
    if (NULL !== $mapping) {
      $this->_mapping = $mapping;
    } elseif (NULL === $this->_mapping) {
      $this->_mapping = $this->_createMapping();
    }
    return $this->_mapping;
  }

  /**
   * Create a standard mapping object for the property $fields.
   *
   * @return Record\Mapping
   */
  protected function _createMapping() {
    return new Record\Mapping($this->_fields);
  }

  /**
   * Getter/Setter for the key subobject. This conatins informations about the identification
   * of the record.
   *
   * @param Interfaces\Key $key
   *
   * @return Interfaces\Key
   */
  public function key(Interfaces\Key $key = NULL) {
    if (NULL !== $key) {
      $this->_key = $key;
    } elseif (NULL === $this->_key) {
      $this->_key = $this->_createKey();
    }
    return $this->_key;
  }

  /**
   * Create a standard autoincrement key object for the property "id".
   *
   * @return Record\Key\Autoincrement
   */
  protected function _createKey() {
    return new Record\Key\Autoincrement('id');
  }

  /**
   * Set database access object
   *
   * @param Access $databaseAccessObject
   */
  public function setDatabaseAccess(Access $databaseAccessObject) {
    $this->_databaseAccessObject = $databaseAccessObject;
  }

  /**
   * Get database access object
   *
   * @return Access
   */
  public function getDatabaseAccess() {
    if (NULL === $this->_databaseAccessObject) {
      $this->_databaseAccessObject = new Access($this);
      $this->_databaseAccessObject->papaya($this->papaya());
    }
    return $this->_databaseAccessObject;
  }

  /**
   * Getter/Setter for the possible callbacks, to modify the behaviour of the object
   *
   * @param Record\Callbacks $callbacks
   *
   * @return Record\Callbacks
   */
  public function callbacks(Record\Callbacks $callbacks = NULL) {
    if (NULL !== $callbacks) {
      $this->_callbacks = $callbacks;
    } elseif (NULL === $this->_callbacks) {
      $this->_callbacks = $this->_createCallbacks();
    }
    return $this->_callbacks;
  }

  /**
   * Create callbacks subobject, override to assign callbacks
   *
   * @return Record\Callbacks
   */
  protected function _createCallbacks() {
    return new Record\Callbacks();
  }
}
