<?php
/**
* Papaya Database Record, superclass for easy database record encapsulation.
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Database
* @version $Id: Record.php 39721 2014-04-07 13:13:23Z weinert $
*/

/**
* Papaya Database Record, superclass for easy database record encapsulation.
*
* @package Papaya-Library
* @subpackage Database
* @version $Id: Record.php 39721 2014-04-07 13:13:23Z weinert $
*/
abstract class PapayaDatabaseRecord
  extends PapayaObjectItem
  implements PapayaDatabaseInterfaceRecord {

  /**
  * An array of property to field mappings.
  *
  * @var array(string=>string)
  */
  protected $_fields = array();

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
  * @var string|boolean
  */
  protected $_tableAlias = FALSE;

  /**
  * Subobject for the database key handling
  *
  * @var PapayaDatabaseInterfaceKey
  */
  private $_key = NULL;

  /**
  * Subobject for the database field mapping
  *
  * @var PapayaDatabaseInterfaceKey
  */
  private $_mapping = NULL;

  /**
  * Stored database access object
  * @var PapayaDatabaseAccess
  */
  private $_databaseAccessObject = NULL;

  /**
   * @var bool $_isLoaded loading indicator
   */
  private $_isLoaded = FALSE;

  /**
   * @var PapayaDatabaseRecordCallbacks
   */
  private $_callbacks = NULL;

  /**
  * Create object and define properties
  */
  public function __construct() {
    parent::__construct(array_keys($this->_fields));
  }

  /**
  * Clone key and mapping subjects, too.
  */
  public function __clone() {
    if (isset($this->_key)) {
      $this->_key = clone $this->_key;
    }
    if (isset($this->_mapping)) {
      $this->_mapping = clone $this->_mapping;
    }
  }

  /**
  * Load record data from specified database table. If the provided value is not an array it will
  * be used like array('id' => $filter).
  *
  * @param mixed $filter
  * @return boolean
  */
  public function load($filter) {
    $condition = PapayaUtilString::escapeForPrintf($this->_compileCondition($filter));
    $fields = implode(
      ', ',
      $this->mapping()->getFields()
    );
    $sql = "SELECT $fields FROM %s $condition";
    $parameters = array(
      $this->getDatabaseAccess()->getTableName($this->_tableName)
    );
    return $this->_loadRecord($sql, $parameters);
  }

  /**
   * Compile filter into sql condition string
   * @param $filter
   * @param string $prefix
   * @return string
   */
  protected function _compileCondition($filter, $prefix = 'WHERE') {
    if ($filter instanceof PapayaDatabaseConditionElement) {
      $condition = $filter->getSql();
    } else {
      if (!is_array($filter)) {
        $filter = array('id' => $filter);
      }
      $generator = new PapayaDatabaseConditionGenerator($this, $this->mapping());
      $condition = (string)$generator->fromArray($filter);
    }
    return empty($condition) ? '' : $prefix.' '.$condition;
  }

  /**
   * Create a filter condition object attached to this database accesss and mapping
   * @return PapayaDatabaseConditionRoot
   */
  public function createFilter() {
    return new PapayaDatabaseConditionRoot($this, $this->mapping());
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
  * @return mixed
  */
  public function save() {
    if ($this->key()->exists()) {
      return $this->_updateRecord();
    } else {
      return $this->_insertRecord();
    }
  }

  /**
  * Delte record from database table
  *
  * @return boolean
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
    } else {
      return FALSE;
    }
  }

  /**
  * Internal method to load record data using sql and paramters.
  *
  * @param string $sql
  * @param array $parameters
  * @return boolean
  */
  protected function _loadRecord($sql, array $parameters = NULL) {
    if ($queryResult = $this->getDatabaseAccess()->queryFmt($sql, $parameters)) {
      if ($row = $queryResult->fetchRow(PapayaDatabaseResult::FETCH_ASSOC)) {
        $this->assign($this->mapping()->mapFieldsToProperties($row));
        $this->key()->assign($this->toArray());
        return $this->_isLoaded = TRUE;
      }
    }
    return $this->_isLoaded = FALSE;
  }

  /**
  * Internal method to update database record.
  *
  * @return boolean
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
  * @return PapayaDatabaseInterfaceKey|FALSE
  */
  protected function _insertRecord() {
    if (!$this->callbacks()->onBeforeInsert($this)) {
      return FALSE;
    }
    $record = $this->mapping()->mapPropertiesToFields($this->toArray(), $this->_tableAlias);
    $filter = $this->mapping()->mapPropertiesToFields(
      $this->key()->getFilter(PapayaDatabaseInterfaceKey::ACTION_CREATE), $this->_tableAlias
    );
    $qualities = $this->key()->getQualities();
    if ($qualities & PapayaDatabaseInterfaceKey::DATABASE_PROVIDED) {
      reset($filter);
      $idField = key($filter);
      if (array_key_exists($idField, $record)) {
        unset($record[$idField]);
      }
    } else {
      $idField = NULL;
      foreach ($filter as $key => $value) {
        if (!isset($record[$key]) ||
            $qualities & PapayaDatabaseInterfaceKey::CLIENT_GENERATED) {
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
    if ($result !== FALSE) {
      if (isset($idField)) {
        $record[$idField] = $result;
      }
      $this->assign($this->mapping()->mapFieldsToProperties($record));
      $this->key()->assign($this->toArray());
      $this->callbacks()->onAfterInsert($this);
      return $this->key();
    } else {
      return FALSE;
    }
  }

  /**
   * Getter/Setter for the mapping subobject. This is used to convert the property values into
   * a database record and back.
   *
   * @param PapayaDatabaseInterfaceMapping $mapping
   * @internal param \PapayaDatabaseInterfaceMapping $key
   * @return PapayaDatabaseInterfaceMapping
   */
  public function mapping(PapayaDatabaseInterfaceMapping $mapping = NULL) {
    if (isset($mapping)) {
      $this->_mapping = $mapping;
    } elseif (is_null($this->_mapping)) {
      $this->_mapping = $this->_createMapping();
    }
    return $this->_mapping;
  }

  /**
  * Create a standard mapping object for the property $fields.
  *
  * @return PapayaDatabaseRecordMapping
  */
  protected function _createMapping() {
    return new PapayaDatabaseRecordMapping($this->_fields);
  }

  /**
  * Getter/Setter for the key subobject. This conatins informations about the identification
  * of the record.
  *
  * @param PapayaDatabaseInterfaceKey $key
  * @return PapayaDatabaseInterfaceKey
  */
  public function key(PapayaDatabaseInterfaceKey $key = NULL) {
    if (isset($key)) {
      $this->_key = $key;
    } elseif (is_null($this->_key)) {
      $this->_key = $this->_createKey();
    }
    return $this->_key;
  }

  /**
  * Create a standard autoincrement key object for the property "id".
  *
  * @return PapayaDatabaseRecordKeyAutoincrement
  */
  protected function _createKey() {
    return new PapayaDatabaseRecordKeyAutoincrement('id');
  }

  /**
   * Set database access object
   * @param PapayaDatabaseAccess $databaseAccessObject
   */
  public function setDatabaseAccess(PapayaDatabaseAccess $databaseAccessObject) {
    $this->_databaseAccessObject = $databaseAccessObject;
  }

  /**
  * Get database access object
  * @return PapayaDatabaseAccess
  */
  public function getDatabaseAccess() {
    if (!isset($this->_databaseAccessObject)) {
      $this->_databaseAccessObject = new PapayaDatabaseAccess($this);
      $this->_databaseAccessObject->papaya($this->papaya());
    }
    return $this->_databaseAccessObject;
  }

  /**
  * Getter/Setter for the possible callbacks, to modify the behaviour of the object
  *
  * @param PapayaDatabaseRecordCallbacks $callbacks
  * @return PapayaDatabaseRecordCallbacks
  */
  public function callbacks(PapayaDatabaseRecordCallbacks $callbacks = NULL) {
    if (isset($callbacks)) {
      $this->_callbacks = $callbacks;
    } elseif (is_null($this->_callbacks)) {
      $this->_callbacks = $this->_createCallbacks();
    }
    return $this->_callbacks;
  }

  /**
   * Create callbacks subobject, override to assign callbacks
   * @return PapayaDatabaseRecordCallbacks
   */
  protected function _createCallbacks() {
    return new PapayaDatabaseRecordCallbacks();
  }
}