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
namespace Papaya\UI\Dialog\Database;

use Papaya\UI;

/**
 * A dialog that can add/edit a record to a database table
 *
 * @deprecated
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Record extends UI\Dialog {
  /**
   * @var int
   */
  const ACTION_NONE = 0;

  /**
   * @var int
   */
  const ACTION_INSERT = 1;

  /**
   * @var int
   */
  const ACTION_UPDATE = 2;

  /**
   * Dialog form method
   *
   * @var null|int
   */
  protected $_method = UI\Dialog::METHOD_MIXED;

  /**
   * Internal database access object variable
   *
   * @var \Papaya\Database\Access $_databaseAccessObject
   */
  private $_databaseAccessObject;

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
   * The keys are the fields, the elements define the type. You can provide \Papaya\Filter objects
   * or types. Types are provided as strings like 'integer' {@link http://www.php.net/settype}.
   *
   * @var array
   */
  protected $_columns = [];

  /**
   * Callback to check permissions
   *
   * @var callable|null $_callbackPermissions
   */
  protected $_callbackPermissions;

  /**
   * Was the database action an insert or update.
   *
   * @var int
   */
  protected $_databaseAction = 0;

  /**
   * Will the database action be an insert or update.
   *
   * @var int
   */
  protected $_databaseActionNext = 1;

  /**
   * Initialize dialog object and define database table mapping.
   *
   * You still need to use {@see \Papaya\UI\Dialog::fields} to define the dialog interface.
   *
   * @param string $table
   * @param string $identifierColumn
   * @param array $columns
   */
  public function __construct($table, $identifierColumn, array $columns) {
    parent::__construct(NULL);
    $this->_table = $table;
    $this->_identifierColumn = $identifierColumn;
    $this->_columns = $columns;
  }

  public function getTableName(): string {
    return $this->_table;
  }

  public function getIdentifierColumn(): string {
    return $this->_identifierColumn;
  }

  public function getColumns(): array {
    return $this->_columns;
  }

  /**
   * Execute dialog an trigger database action if necessary
   *
   * @return bool
   */
  public function execute() {
    $identifier = $this->_getIdentifierValue($this->_identifierColumn);
    $this->hiddenFields()->set($this->_identifierColumn, $identifier);
    if (parent::execute()) {
      try {
        if (empty($identifier)) {
          /* @noinspection PhpDeprecationInspection */
          if (
            $this->checkRecordPermission(self::ACTION_INSERT) &&
            ($newId = $this->_insert())
          ) {
            $this->hiddenFields()->set($this->_identifierColumn, $newId);
            /* @noinspection PhpDeprecationInspection */
            $this->_databaseAction = self::ACTION_INSERT;
            /* @noinspection PhpDeprecationInspection */
            $this->_databaseActionNext = self::ACTION_UPDATE;
            return TRUE;
          }
        } else {
          /* @noinspection PhpDeprecationInspection */
          $this->_databaseActionNext = self::ACTION_UPDATE;
          $filter = [$this->_identifierColumn => $identifier];
          /* @noinspection PhpDeprecationInspection */
          if ($this->checkRecordPermission(self::ACTION_UPDATE, $filter)) {
            /* @noinspection PhpDeprecationInspection */
            $this->_databaseAction = self::ACTION_UPDATE;
            return $this->_update($identifier);
          }
        }
      } catch (\Papaya\Filter\Exception $e) {
      }
    } elseif (!empty($identifier) &&
      ($data = $this->_load($identifier))) {
      $this->hiddenFields()->set($this->_identifierColumn, $identifier);
      /* @noinspection PhpDeprecationInspection */
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
   *
   * @return mixed|string
   */
  private function _getIdentifierValue($column) {
    if (
      isset($this->_columns[$column]) &&
      $this->_columns[$column] instanceof \Papaya\Filter
    ) {
      return $this->parameters()->get(
        $column, NULL, $this->_columns[$column]
      );
    }
    return (string)$this->parameters()->get($column);
  }

  /**
   * Get the database action that was executed.
   *
   * @param int
   *
   * @return int
   */
  public function getDatabaseAction() {
    return $this->_databaseAction;
  }

  /**
   * Get the database action that will be executed if the dialog is submitted.
   *
   * @param int
   *
   * @return int
   */
  public function getDatabaseActionNext() {
    return $this->_databaseActionNext;
  }

  /**
   * The permission callback will be called to check if the current user is allowed to
   * change the record.
   *
   * @param callable $callback
   */
  public function setPermissionCallback($callback) {
    $this->_callbackPermissions = $callback;
  }

  public function getPermissionCallback(): ?callable {
    return $this->_callbackPermissions;
  }

  /**
   * Use a callback to ceck if the current user is allowed to insert/update the record
   *
   * @param string $action
   * @param array $filter
   *
   * @return bool|mixed
   */
  public function checkRecordPermission($action, array $filter = []) {
    if (NULL !== $this->_callbackPermissions) {
      return \call_user_func($this->_callbackPermissions, $action, $this->_table, $filter);
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
   * Get the database access object, create a default object if necessary.
   *
   * @return \Papaya\Database\Access
   */
  public function getDatabaseAccess() {
    if (NULL === $this->_databaseAccessObject) {
      $this->_databaseAccessObject = new \Papaya\Database\Access();
      $this->_databaseAccessObject->papaya($this->papaya());
    }
    return $this->_databaseAccessObject;
  }

  /**
   * @param $identifier
   *
   * @return false|array
   */
  protected function _load($identifier) {
    return $this->getDatabaseAccess()->loadRecord(
      $this->_table, \array_keys($this->_columns), [$this->_identifierColumn => $identifier]
    );
  }

  /**
   * Insert data as new record into the database table.
   *
   * @return string|int New identifier
   * @throws \Papaya\Filter\Exception
   */
  protected function _insert() {
    return $this->getDatabaseAccess()->insertRecord(
      $this->_table, $this->_identifierColumn, $this->_compileRecord()
    );
  }

  /**
   * Update record data in database table.
   *
   * @param string|int $identifier
   *
   * @return bool
   * @throws \Papaya\Filter\Exception
   */
  protected function _update($identifier) {
    $this->getDatabaseAccess()->updateRecord(
      $this->_table, $this->_compileRecord(), [$this->_identifierColumn => $identifier]
    );
    return TRUE;
  }

  /**
   * Compile dialog data into record data for the sql queries.
   *
   * @see \Papaya\UI\Dialog\Database\Record::_insert
   * @see \Papaya\UI\Dialog\Database\Record::_update
   * @throws \Papaya\Filter\Exception
   */
  private function _compileRecord() {
    $data = [];
    foreach ($this->_columns as $field => $filter) {
      if ($field !== $this->_identifierColumn) {
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
