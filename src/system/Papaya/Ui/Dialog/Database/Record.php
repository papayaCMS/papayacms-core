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
* A dialog that can add/edit a record to a database table
*
* @deprecated
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogDatabaseRecord extends \PapayaUiDialog {

  /**
  * @var integer
  */
  const ACTION_NONE = 0;
  /**
  * @var integer
  */
  const ACTION_INSERT = 1;

  /**
  * @var integer
  */
  const ACTION_UPDATE = 2;

  /**
  * Dialog form method
  * @var NULL|integer
  */
  protected $_method = \PapayaUiDialog::METHOD_MIXED;

  /**
  * Internal database access object variable
  *
  * @var \Papaya\Database\Access $_databaseAccessObject
  */
  private $_databaseAccessObject = NULL;

  /**
  * Database table
  *
  * @var string
  */
  protected $_table = '';

  /**
  * Identifier/primary key field
  *
  * @var string
  */
  protected $_identifierColumn = '';

  /**
  * Column definition.
  *
  * The keys are the fields, the elements define the type. You can provide \Papaya\PapayaFilter objects
  * or types. Types are provided as strings like 'integer' {@link http://www.php.net/settype}.
  *
  * @var array
  */
  protected $_columns = array();

  /**
  * Callback to check permissions
  *
  * @var Closure|Callback $_callbackPermissions
  */
  protected $_callbackPermissions = NULL;

  /**
  * Was the database action an insert or update.
  *
  * @var integer
  */
  protected $_databaseAction = 0;

  /**
  * Will the database action be an insert or update.
  *
  * @var integer
  */
  protected $_databaseActionNext = 1;

  /**
   * Initalize dialog object and define database table mapping.
   *
   * You still need to use {@see \PapayaUiDialog::fields} to define the dialog interface.
   *
   * @param string $table
   * @param string $identifierColumn
   * @param array $columns
   */
  public function __construct($table, $identifierColumn, array $columns) {
    $this->_table = $table;
    $this->_identifierColumn = $identifierColumn;
    $this->_columns = $columns;
  }

  /**
  * Execute dialog an trigger database action if nessesary
  *
  * @return boolean
  */
  public function execute() {
    $identifier = $this->_getIdentifierValue($this->_identifierColumn);
    $this->hiddenFields()->set($this->_identifierColumn, $identifier);
    if (parent::execute()) {
      try {
        if (empty($identifier)) {
          /** @noinspection PhpDeprecationInspection */
          if ($this->checkRecordPermission(self::ACTION_INSERT)) {
            if ($newId = $this->_insert()) {
              $this->hiddenFields()->set($this->_identifierColumn, $newId);
              /** @noinspection PhpDeprecationInspection */
              $this->_databaseAction = self::ACTION_INSERT;
              /** @noinspection PhpDeprecationInspection */
              $this->_databaseActionNext = self::ACTION_UPDATE;
              return TRUE;
            }
          }
        } else {
          /** @noinspection PhpDeprecationInspection */
          $this->_databaseActionNext = self::ACTION_UPDATE;
          $filter = array($this->_identifierColumn => $identifier);
          /** @noinspection PhpDeprecationInspection */
          if ($this->checkRecordPermission(self::ACTION_UPDATE, $filter)) {
            /** @noinspection PhpDeprecationInspection */
            $this->_databaseAction = self::ACTION_UPDATE;
            return $this->_update($identifier);
          }
        }
      } catch (\Papaya\Filter\Exception $e) {
      }
    } elseif (!empty($identifier) &&
              ($data = $this->_load($identifier))) {
      $this->hiddenFields()->set($this->_identifierColumn, $identifier);
      /** @noinspection PhpDeprecationInspection */
      $this->_databaseActionNext = self::ACTION_UPDATE;
      foreach ($data as $field => $value) {
        $this->data()->set($field, $value);
      }
    }
    return FALSE;
  }

  /**
   * Get the identifier value, filter it if a filter was provided.
   *
   * @param string $column
   * @return mixed|string
   */
  private function _getIdentifierValue($column) {
    if (isset($this->_columns[$column]) &&
        $this->_columns[$column] instanceof \Papaya\Filter) {
      return $this->parameters()->get(
        $column, NULL, $this->_columns[$column]
      );
    } else {
      return (string)$this->parameters()->get($column);
    }
  }

  /**
   * Get the database action that was executed.
   *
   * @param integer
   * @return int
   */
  public function getDatabaseAction() {
    return $this->_databaseAction;
  }

  /**
   * Get the database action that will be executed if the dialog is submitted.
   *
   * @param integer
   * @return int
   */
  public function getDatabaseActionNext() {
    return $this->_databaseActionNext;
  }

  /**
  * The permission callback will be called to check if the current user is allowed to
  * change the record.
  *
  * @param \Closure|\Callback $callback
  */
  public function setPermissionCallback($callback) {
    $this->_callbackPermissions = $callback;
  }

  /**
   * Use a callback to ceck if the current user is allowed to insert/update the record
   *
   * @param string $action
   * @param array $filter
   * @return bool|mixed
   */
  public function checkRecordPermission($action, array $filter = array()) {
    if (isset($this->_callbackPermissions)) {
      return call_user_func($this->_callbackPermissions, $action, $this->_table, $filter);
    }
    return TRUE;
  }

  /**
  * Set the database access object.
  *
  * @param \Papaya\Database\Access $databaseAccess
  */
  public function setDatabaseAccess(\Papaya\Database\Access $databaseAccess) {
    $this->_databaseAccessObject = $databaseAccess;
  }

  /**
  * Get the database access object, create a default object if nessesary.
  *
  * @return \Papaya\Database\Access
  */
  public function getDatabaseAccess() {
    if (is_null($this->_databaseAccessObject)) {
      $this->_databaseAccessObject = new \Papaya\Database\Access($this);
      $this->_databaseAccessObject->papaya($this->papaya());
    }
    return $this->_databaseAccessObject;
  }

  /**
   * @param $identifier
   * @return FALSE|array
   */
  protected function _load($identifier) {
    return $this->getDatabaseAccess()->loadRecord(
      $this->_table, array_keys($this->_columns), array($this->_identifierColumn => $identifier)
    );
  }

  /**
  * Insert data as new record into the database table.
  *
  * @return string|integer New identifier
  */
  protected function _insert() {
    return $this->getDatabaseAccess()->insertRecord(
      $this->_table, $this->_identifierColumn, $this->_compileRecord()
    );
  }

  /**
   * Update record data in database table.
   *
   * @param string|integer $identifier
   * @return bool
   */
  protected function _update($identifier) {
    $this->getDatabaseAccess()->updateRecord(
      $this->_table, $this->_compileRecord(), array($this->_identifierColumn => $identifier)
    );
    return TRUE;
  }

  /**
  * Compile dialog data into record data foir the sql queries.
  *
  * @see \PapayaUiDialogDatabaseRecord::_insert
  * @see \PapayaUiDialogDatabaseRecord::_update
  */
  private function _compileRecord() {
    $data = array();
    foreach ($this->_columns as $field => $filter) {
      if ($field != $this->_identifierColumn) {
        $value = $this->data()->get($field);
        if ($filter instanceof \Papaya\Filter) {
          if ($filter->validate($value)) {
            $data[$field] = $filter->filter($value);
          }
        } else {
          $data[$field] = (string)$value;
        }
      }
    }
    return $data;
  }
}
