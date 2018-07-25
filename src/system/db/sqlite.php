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
* basic database connection and result class
*/
require_once(dirname(__FILE__).'/base.php');

/**
* DB-abstraction layer - SQLite
*
* @package Papaya-Library
* @subpackage Database
*/
class dbcon_sqlite extends dbcon_base {

  /**
  * Connect error string
  * @var string $connectErrorString
  */
  var $connectErrorString = '';

  /**
  * Callbacks
  * @var array $callbacks
  */
  var $callbacks = array();

  /**
   * Check for sqlite database extension found
   *
   * @throws PapayaDatabaseExceptionConnect
   * @return boolean
   */
  public function extensionFound() {
    if (!extension_loaded('sqlite')) {
      throw new PapayaDatabaseExceptionConnect(
        'Extension "sqlite" not available.'
      );
    }
    return TRUE;
  }

  /**
   * Establish connection to database
   *
   * @throws PapayaDatabaseExceptionConnect
   * @return resource $this->databaseConnection connection ID
   */
  public function connect() {
    if (isset($this->databaseConnection) && is_resource($this->databaseConnection)) {
      return TRUE;
    } else {
      $error = '';
      $connection = @sqlite_open($this->databaseConfiguration->filename, 0666, $error);
      if (isset($connection) &&
          is_resource($connection)) {
        $this->databaseConnection = $connection;
        return TRUE;
      } else {
        throw new PapayaDatabaseExceptionConnect($error);
      }
    }
  }

  /**
  * close connection
  */
  public function close() {
    if (isset($this->databaseConnection) &&
        is_resource($this->databaseConnection)) {
      sqlite_close($this->databaseConnection);
    }
  }

  /**
   * Wrap query execution so we can convert the erorr to an exception
   *
   * @throws PapayaDatabaseExceptionQuery
   * @param string $sql
   * @return \SQLiteResult
   */
  public function executeQuery($sql) {
    if ($result = @sqlite_query($this->databaseConnection, $sql)) {
      return $result;
    }
    throw $this->_createQueryException($sql);
  }

  /**
   * If a query failes, trow an database exception
   *
   * @param string $sql
   * @return \PapayaDatabaseExceptionQuery
   */
  private function _createQueryException($sql) {
    $errorCode = sqlite_last_error($this->databaseConnection);
    $errorMessage = sqlite_error_string($errorCode);
    $severityMapping = array(
      // 5 - The database file is locked
      5 => PapayaDatabaseException::SEVERITY_WARNING,
      // 6 - A table in the database is locked
      6 => PapayaDatabaseException::SEVERITY_WARNING,
      // 20 - Data type mismatch
      20 => PapayaDatabaseException::SEVERITY_WARNING,
      // 100 - sqlite_step() has another row ready
      100 => PapayaDatabaseException::SEVERITY_INFO,
      // 101 - sqlite_step() has finished executing
      101 => PapayaDatabaseException::SEVERITY_INFO,
    );
    if (isset($severityMapping[$errorCode])) {
      $severity = $severityMapping[$errorCode];
    } else {
      $severity = PapayaDatabaseException::SEVERITY_ERROR;
    }
    return new PapayaDatabaseExceptionQuery(
      $errorMessage, $errorCode, $severity, $sql
    );
  }

  /**
  * String ecsaping for SQLite use
  *
  * @param mixed $value Value to escape
  * @access public
  * @return string escaped value.
  */
  function escapeString($value) {
    $value = parent::escapeString($value);
    return @sqlite_escape_string($value);
  }

  /**
  * Execute SQLite-query
  *
  * @param string $sql SQL-String with query
  * @param integer $max maximum number of returned records
  * @param integer $offset Offset
  * @param boolean $freeLastResult free last result (if here is one)
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function &query($sql, $max = NULL, $offset = NULL, $freeLastResult = TRUE) {
    if ($freeLastResult &&
        is_object($this->lastResult) &&
        is_a($this->lastResult, 'dbresult_sqlite')) {
      $this->lastResult->free();
    }
    if (isset($max) && $max > 0 && strpos(trim($sql), 'SELECT') === 0) {
      $limitSQL = (isset($offset) && $offset >= 0) ?
        ' LIMIT '.(int)$offset.','.(int)$max : ' LIMIT '.(int)$max;
    } else {
      $limitSQL = '';
    }
    $this->lastSQLQuery = $sql.$limitSQL;
    $res = $this->executeQuery($sql.$limitSQL, $this->databaseConnection);
    if ($res) {
      if (is_resource($res)) {
        $this->lastResult = new dbresult_sqlite($this, $res, $sql);
        $this->lastResult->setLimit($max, $offset);
        $this->lastResult->_absCount = -1;
        return $this->lastResult;
      } else {
        $result = @sqlite_changes($this->databaseConnection);
        return $result;
      }
    } else {
      $result = FALSE;
      return $result;
    }
  }

  /**
  * Rewrite query to get record count of a limited query and execute it.
  *
  * @param string $sql SQL string
  * @access public
  * @return integer | FALSE record count or failure
  */
  function queryRecordCount($sql) {
    if ($countSql = $this->getCountQuerySql($sql)) {
      if ($res = $this->executeQuery($countSql, $this->databaseConnection)) {
        @sqlite_fetch_string($res);
      }
    }
    return FALSE;
  }

  /**
  * Insert record into table
  *
  * @param string $table table
  * @param string $idField primary key value
  * @param array $values insert values
  * @access public
  * @return mixed FALSE or Id of new record
  */
  function insertRecord($table, $idField, $values = NULL) {
    if (isset($idField)) {
      $values[$idField] = NULL;
    }
    if (isset($values) && is_array($values) && count($values) > 0) {
      $fieldString = '';
      $valueString = '';
      foreach ($values as $field => $value) {
        if (is_bool($value)) {
          $fieldString .= $this->escapeString($field).', ';
          $valueString .= "'".($value ? '1' : '0')."', ";
        } elseif ($value !== NULL) {
          $fieldString .= $this->escapeString($field).', ';
          $valueString .= "'".$this->escapeString($value)."', ";
        }
      }
      $fieldString = substr($fieldString, 0, -2);
      $valueString = substr($valueString, 0, -2);
      $sql = 'INSERT INTO '.$this->escapeString($table).
        ' ('.$fieldString.') VALUES ('.$valueString.')';
      if ($this->query($sql, NULL, NULL, FALSE)) {
        if (isset($idField)) {
          return $this->lastInsertId($table, $idField);
        } else {
          return sqlite_changes($this->databaseConnection);
        }
      }
    }
    return FALSE;
  }
  /**
  * Fetch the last inserted id
  *
  * @param string $table
  * @param string $idField
  * @return int|string|null
  */
  public function lastInsertId($table, $idField) {
    return sqlite_last_insert_rowid($this->databaseConnection);
  }


  /**
  * Insert records
  *
  * @param string $table
  * @param array $values
  * @access public
  * @return boolean
  */
  function insertRecords($table, $values) {
    $lastFields = NULL;
    $this->lastSQLQuery = '';
    if (isset($values) && is_array($values) && count($values) > 0) {
      $this->query('BEGIN TRANSACTION;');
      foreach ($values as $data) {
        if (is_array($data) && count($data) > 0) {
          if (!$this->insertRecord($table, NULL, $data)) {
            return FALSE;
          }
        }
      }
      $this->query('END TRANSACTION;');
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Update records via filter
  *
  * @param string $table table name
  * @param array $values update values
  * @param string $filter Filter string without WHERE condition
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  * @see dbcon_base::getSQLCondition()
  */
  function updateRecord($table, $values, $filter) {
    if (isset($values) && is_array($values) && count($values) > 0) {
      $sql = FALSE;
      foreach ($values as $col => $val) {
        $fieldName = trim($col);
        if ($val === NULL) {
          $sql .= " ".$this->escapeString($fieldName)." = NULL, ";
        } elseif (is_bool($val)) {
          $sql .= " ".$this->escapeString($fieldName)." = '".($val ? '1' : '0')."', ";
        } else {
          $sql .= " ".$this->escapeString($fieldName)." = '".$this->escapeString($val)."', ";
        }
      }
      if ($sql) {
        $sql = "UPDATE $table SET ".substr($sql, 0, -2)." WHERE ".
          $this->getSQLCondition($filter);
        return $this->query($sql, NULL, NULL, FALSE);
      } else {
        $this->lastSQLQuery = 'NO DATA';
      }
    } else {
      $this->lastSQLQuery = '';
    }
    return FALSE;
  }

  /**
  * Delete records by filter
  *
  * @param string $table table name
  * @param string $filter Filter string without WHERE condition
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function deleteRecord($table, $filter) {
    $sql = "DELETE FROM $table
             WHERE ".$this->getSQLCondition($filter);
    return $this->query($sql, NULL, NULL, FALSE);
  }

  /**
  * Get table names
  *
  * @access public
  * @return array
  */
  function queryTableNames() {
    $sql = "SELECT name
              FROM sqlite_master
             WHERE type = 'table'";
    $result = array();
    if ($res = $this->query($sql)) {
      while ($row = $res->fetchRow()) {
        $result[] = $row[0];
      }
    }
    return $result;
  }

  /**
  * Parse SQLite field data
  *
  * @param array $row
  * @access private
  * @return array
  */
  function parseSQLiteFieldData($row) {
    $autoIncrement = FALSE;
    $default = NULL;
    switch ($row['type']) {
    case 'BIGSERIAL':
    case 'BIGINT':
      $type = 'integer';
      $size = 8;
      break;
    case 'SERIAL':
    case 'INT':
    case 'INTEGER':
      $type = 'integer';
      $size = 4;
      if ($row['pk'] == 1) {
        $autoIncrement = TRUE;
      }
      break;
    case 'SMALLINT':
      $type = 'integer';
      $size = 2;
      break;
    case 'TEXT':
      $type = 'string';
      $size = 65535;
      break;
    default:
      if (preg_match('~NUMERIC\((\d+,\d+)\)~', $row['type'], $regs)) {
        $type = 'float';
        $size = $regs[1];
      } elseif (preg_match('~VARCHAR\((\d+)\)~', $row['type'], $regs)) {
        $type = 'string';
        $size = (int)$regs[1];
      } elseif (preg_match('~CHAR\((\d+)\)~', $row['type'], $regs)) {
        $type = 'string';
        $size = (int)$regs[1];
      } else {
        $type = 'string';
        $size = 16777215;
      }
    }
    if ($autoIncrement) {
      $notNull = TRUE;
    } elseif (isset($row['notnull']) && $row['notnull'] != 0) {
      $notNull = TRUE;
      if ($type != 'integer' || (isset($row['dflt_value']) && $row['dflt_value'] != 0)) {
        $default = $row['dflt_value'];
      }
    } else {
      $notNull = FALSE;
    }
    $result = array(
      'name' => $row['name'],
      'type' => $type,
      'size' => $size,
      'null' => $notNull ? 'no' : 'yes',
      'autoinc' => $autoIncrement ? 'yes' : 'no',
      'default' => $default
    );

    return $result;
  }

  /**
   * get field type
   *
   * @param array $field
   * @param bool $primaryKey
   * @access private
   * @return string
   */
  function getSQLiteFieldType($field, $primaryKey = FALSE) {
    if (isset($field['autoinc']) && $field['autoinc'] == 'yes') {
      return 'INTEGER PRIMARY KEY';
    } else {
      if (isset($field['null']) && $field['null'] == 'yes') {
        $default = NULL;
        $notNullStr = '';
      } else {
        $default = '';
        $notNullStr = ' NOT NULL';
      }
      if (isset($field['default'])) {
        $default = $field['default'];
      }
      $defaultStr = '';
      if (isset($default)) {
        switch (strtolower($field['type'])) {
        case 'integer':
          $defaultStr = " DEFAULT '".(int)$default."'";
          break;
        case 'float':
          $defaultStr = " DEFAULT '".(double)$default."'";
          break;
        default:
          $defaultStr = " DEFAULT '".$this->escapeString($default)."'";
          break;
        }
      }
      switch (strtolower(trim($field['type']))) {
      case 'integer':
        $size = ($field['size'] > 0) ? (int)$field['size'] : 1;
        if ($size <= 2) {
          $result = "SMALLINT";
        } elseif ($size <= 4) {
          $result = "INTEGER";
        } else {
          $result = "BIGINT";
        }
        break;
      case 'float':
        if (FALSE !== strpos($field['size'], ',')) {
          list($before,$after) = explode(',', $field['size']);
          $before = ($before > 0) ? (int)$before : 1;
          $after = (int)$after;
          if ($after > $before) {
            $before += $after;
          }
          $result = "NUMERIC(".$before.','.$after.")";
        } else {
          $result = "NUMERIC(".(int)$field['size'].',0)';
        }
        break;
      case 'string':
      default:
        $size = ($field['size'] > 0) ? (int)$field['size'] : 1;
        if ($size <= 4) {
          $result = "CHAR(".$field['size'].")";
        } elseif ($size <= 255) {
          $result = "VARCHAR(".$field['size'].")";
        } else {
          $result = "TEXT";
        }
        break;
      }
      if ($primaryKey) {
        $result .= ' PRIMARY KEY';
      }
      return $result.$defaultStr.$notNullStr;
    }
  }

  /**
  * Query table structure
  *
  * @param string $tableName
  * @param string $tablePrefix optional, default value ''
  * @access public
  * @return array
  */
  function queryTableStructure($tableName, $tablePrefix = '') {
    $fields = array();
    $keys = array();
    if ($tablePrefix) {
      $table = $tablePrefix.'_'.$tableName;
    } else {
      $table = $tableName;
    }
    $sql = "PRAGMA table_info('".$this->escapeString($table)."');";
    if ($res = $this->query($sql)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $fields[$row['name']] = $this->parseSQLiteFieldData($row);
        if ($row['pk'] == 1) {
          if (isset($keys['PRIMARY']) && is_array($keys['PRIMARY'])) {
            $keys['PRIMARY']['fields'][] = $row['name'];
          } else {
            $keys['PRIMARY']['orgname'] = 'PRIMARY';
            $keys['PRIMARY']['name'] = 'PRIMARY';
            $keys['PRIMARY']['unique'] = 'yes';
            $keys['PRIMARY']['fields'] = array($row['name']);
            $keys['PRIMARY']['fulltext'] = 'no';
            $keys['PRIMARY']['autoinc'] = ($row['type'] == 'INTEGER')
              ? 'yes' : 'no';
          }
        }
      }
    }
    $sql = "PRAGMA index_list('".$this->escapeString($table)."')";
    if ($res = $this->query($sql)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $pattern = '(\('.preg_quote($table).' autoindex (\d+)\))';
        if (preg_match($pattern, $row['name'])) {
          continue;
        } elseif (strpos($row['name'], $table) === 0) {
          $keyName = substr($row['name'], strlen($table) + 1);
        } else {
          $keyName = $row['name'];
        }
        $keys[$keyName]['orgname'] = $row['name'];
        $keys[$keyName]['name'] = $keyName;
        $keys[$keyName]['unique'] = ($row['unique'] == '1') ? 'yes' : 'no';
        $keys[$keyName]['fields'] = array();
        $keys[$keyName]['fulltext'] = 'no';
      }
      foreach ($keys as $keyName => $keyData) {
        $sql = "PRAGMA index_info('".$this->escapeString($keyData['orgname'])."')";
        if ($res = $this->query($sql)) {
          while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $keys[$keyName]['fields'][] = $row['name'];
          }
        }
      }
    }
    return array(
      'name' => $tableName,
      'fields' => $fields,
      'keys' => $keys
    );
  }

  /**
  * Create given table
  *
  * @param string $tableData
  * @param string $tablePrefix
  * @access public
  * @return boolean
  */
  function createTable($tableData, $tablePrefix = '') {
    if (is_array($tableData['fields']) && trim($tableData['name']) != '') {
      if ($tablePrefix) {
        $table = $tablePrefix.'_'.trim($tableData['name']);
      } else {
        $table = trim($tableData['name']);
      }
      if (isset($tableData['keys']) && isset($tableData['keys']['PRIMARY']) &&
          isset($tableData['keys']['PRIMARY']['fields']) &&
          is_array($tableData['keys']['PRIMARY']['fields']) &&
          count($tableData['keys']['PRIMARY']['fields']) == 1) {
        $primaryKeyField = $tableData['keys']['PRIMARY']['fields'][0];
      } else {
        $primaryKeyField = '';
      }
      $sql = 'CREATE TABLE '.$this->escapeString(strtolower($table)).' ('.LF;
      foreach ($tableData['fields'] as $field) {
        $sql .= '  '.strtolower($field['name']).' '.
          $this->getSQLiteFieldType(
            $field,
            $field['name'] == $primaryKeyField
          ).",\n";
      }
      if (is_array($tableData['keys'])) {
        foreach ($tableData['keys'] as $keyName => $key) {
          if ($keyName == 'PRIMARY' && is_array($key['fields']) &&
              count($key['fields']) > 1) {
            $sql .= 'PRIMARY KEY ('.implode(',', $key['fields'])."),\n";
          } elseif ($keyName != 'PRIMARY' &&
                    isset($key['unique']) && $key['unique'] == 'yes') {
            $sql .= 'CONSTRAINT '.$this->escapeString(strtolower($table)).'_'.
              $keyName.' UNIQUE ';
            $sql .= '('.implode(',', $key['fields'])."),\n";
          }
        }
      }
      $sql = substr($sql, 0, -2)."\n)\n";
      if ($this->query($sql) !== FALSE) {
        foreach ($tableData['keys'] as $key) {
          $this->addIndex($table, $key);
        }
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Add Field
  *
  * @param string $table
  * @param array $fieldData
  * @access public
  * @return boolean
  */
  function addField($table, $fieldData) {
    return $this->changeField($table, $fieldData);
  }

  /**
  * Change Field
  *
  * @param string $table
  * @param array $fieldData
  * @access public
  * @return boolean
  */
  function changeField($table, $fieldData) {
    return $this->alterTable($table, 'change', $fieldData);
  }

  /**
  * Drop field
  *
  * @param string $table
  * @param string $field fieldname
  * @access public
  * @return boolean
  */
  function dropField($table, $field) {
    return $this->alterTable($table, 'drop', $field);
  }

  /**
  * Change table structure
  *
  * @param string $table
  * @param string $action
  * @param array|string $fieldData
  * @return boolean
  */
  function alterTable($table, $action, $fieldData) {
    $result = FALSE;
    // get current table definitions
    $structure = $this->queryTableStructure($table);
    // create temporary table with old table definitions
    $tmpTableName = 'tmp_'.$table;  // .'_'.time();
    $tmpTableData = array(
      'name' => $tmpTableName,
      'keys' => $structure['keys'],
      'fields' => $structure['fields'],
    );
    $this->query(sprintf("DROP TABLE IF EXISTS %s", $tmpTableName));

    $this->createTable($tmpTableData);

    // copy data from old table to temporary table (insert select)
    $sqlCopyToTmp = "INSERT INTO %s (%s) SELECT %s FROM %s";
    $paramsCopyToTmp = array(
      $tmpTableName,
      implode(',', array_keys($structure['fields'])),
      implode(',', array_keys($structure['fields'])),
      $table,
    );
    $this->query(vsprintf($sqlCopyToTmp, $paramsCopyToTmp));

    // drop old table
    $this->query(sprintf("DROP TABLE %s", $table));

    // calculate new table definitions
    $newTableData = array(
      'name' => $table,
      'keys' => $structure['keys'],
    );
    switch ($action) {
    case 'drop':
      foreach ($structure['fields'] as $fieldName => $field) {
        if ($fieldData != $fieldName) {
          $newTableData['fields'][$fieldName] = $field;
        }
      }
      unset($structure['fields'][$fieldData]);
      break;
    case 'change':
      $newTableData['fields'] = $structure['fields'];
      $newTableData['fields'][$fieldData['name']] = $fieldData;
      break;
    }

    // create new table with new table definitions
    $this->createTable($newTableData);
    // copy data from temporary table to new table (insert select)
    $sqlCopyToNew = "INSERT INTO %s (%s) SELECT %s FROM %s";
    $paramsCopyToNew = array(
      $table,
      implode(', ', array_keys($structure['fields'])),
      implode(', ', array_keys($structure['fields'])),
      $tmpTableName,
    );
    if ($this->query(vsprintf($sqlCopyToNew, $paramsCopyToNew))) {
      $result = TRUE;
    }
    // drop temporary table

    $this->query(sprintf("DROP TABLE %s", $tmpTableName));
    return $result;
  }

  /**
  * Get index info
  *
  * @param string $table
  * @param string $key
  * @access public
  * @return array $result
  */
  function getIndexInfo($table, $key) {
    $result = FALSE;
    if ($key == 'PRIMARY') {
      $sql = "PRAGMA table_info('".$this->escapeString($table)."');";
      if ($res = $this->query($sql)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          if ($row['pk'] == 1) {
            if (!is_array($result)) {
              $result['name'] = 'PRIMARY';
              $result['unique'] = 1;
              $result['fields'] = array($row['name']);
              $result['fulltext'] = 'no';
              $result['autoinc'] = ($row['type'] == 'INTEGER') ? 'yes' : 'no';
            } else {
              $result['fields'][] = $row['name'];
            }
          }
        }
      }
    } else {
      $keyName = $this->escapeString($key);
      $sql = "PRAGMA index_list('".$this->escapeString($table)."')";
      if ($res = $this->query($sql)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          if (strpos($row['name'], $table) === 0 &&
              $keyName == substr($row['name'], strlen($table) + 1)) {
            $result['orgname'] = $row['name'];
            $result['name'] = $keyName;
            $result['unique'] = ($row['unique'] == '1') ? 'yes' : 'no';
            $result['fields'] = array();
            $result['fulltext'] = 'no';
            $sql = "PRAGMA index_info('".$this->escapeString($result['orgname'])."')";
            if ($res = $this->query($sql)) {
              while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                $result['fields'][] = $row['name'];
              }
            }
          } else {
            continue;
          }
        }
      }
    }
    return $result;
  }

  /**
  * Add index
  *
  * @param string $table
  * @param array $index
  * @access public
  * @return boolean
  */
  function addIndex($table, $index) {
    return $this->changeIndex($table, $index, FALSE);
  }

  /**
  * Change Index
  *
  * @param string $table
  * @param array $index
  * @param boolean $dropCurrent optional, default value TRUE
  * @access public
  * @return boolean
  */
  function changeIndex($table, $index, $dropCurrent = TRUE) {
    $key = $this->getIndexInfo($table, $index['name']);
    if ($dropCurrent && $key) {
      $this->dropIndex($table, $index['name']);
    }
    if (isset($index['fields']) && is_array($index['fields'])) {
      $fields = '('.implode(',', $index['fields']).")";
      if ($index['name'] == 'PRIMARY') {
        $sql = "ALTER TABLE ".$this->escapeString($table)." ADD PRIMARY KEY ".$fields;
      } elseif (isset($index['unique']) && $index['unique'] == 'yes') {
        $sql = 'CREATE UNIQUE INDEX '.$this->escapeString($table).'_'.
          $this->escapeString($index['name']).' ON '.$this->escapeString($table).
            ' '.$fields;
      } else {
        $sql = 'CREATE INDEX '.$this->escapeString($table).'_'.
          $this->escapeString($index['name']).' ON '.
          $this->escapeString($table).' '.$fields;
      }
      return ($this->query($sql) !== FALSE);
    }
    return FALSE;
  }

  /**
  * Drop Index
  *
  * @param string $table
  * @param string $name
  * @access public
  * @return boolean
  */
  function dropIndex($table, $name) {
    $sql = "PRAGMA index_list('".$this->escapeString($table)."')";
    if ($res = $this->query($sql)) {
      $keyName = NULL;
      $keys = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $pattern = '(\('.preg_quote($table).' autoindex (\d+)\))';
        if (preg_match($pattern, $row['name'])) {
          return FALSE;
        } elseif (strpos($row['name'], $table) === 0) {
          $keyName = substr($row['name'], strlen($table) + 1);
        } else {
          $keyName = $row['name'];
        }
        $keys[$keyName]['orgname'] = $row['name'];
        $keys[$keyName]['name'] = $keyName;
      }
      if ($keyName && $keys[$keyName]) {
        $sql = 'DROP INDEX '.$keys[$keyName]['orgname'];
        return ($this->query($sql) !== FALSE);
      }
    }
    return FALSE;
  }

  /**
  * DBMS spezific SQL source
  *
  * @param string $function sql function
  * @param array $params params
  * @access public
  * @return mixed sql string or FALSE
  */
  function getSQLSource($function, $params) {
    switch (strtoupper($function)) {
    case 'CONCAT':
      $result = '';
      for ($i = 0; $i < count($params); $i += 2) {
        if (isset($params[$i + 1]) && $params[$i + 1] = FALSE) {
          $result .= $params[$i].'  ||';
        } else {
          $result .= "'".$this->escapeString($params[$i])."' ||";
        }
      }
      return substr($result, 0, -2);
    case 'SUBSTRING':
      return ' SUBSTRING('.$this->getSQLFunctionParams($params).')';
    case 'LENGTH' :
      return ' LENGTH('.$this->getSQLFunctionParams($params).')';
    case 'LOWER':
      return ' LOWER('.$this->getSQLFunctionParams($params).')';
    case 'UPPER':
      return ' UPPER('.$this->getSQLFunctionParams($params).')';
    case 'LOCATE':
      if (!isset($this->callbacks['LOCATE'])) {
        /** @noinspection PhpParamsInspection */
        sqlite_create_function(
          $this->databaseConnection,
          'LOCATE',
          array($this, 'sqliteCallBackLOCATE')
        );
        $this->callbacks['LOCATE'] = TRUE;
      }
      return ' LOCATE('.$this->getSQLFunctionParams($params).')';
    case 'RANDOM' :
      return ' RANDOM()';
    case 'LIKE' :
      return ' LIKE '.$this->getSQLFunctionParams($params).' ESCAPE \'\\\\\'';
    }
    return FALSE;
  }

  /**
  * sqllite callback locate position
  *
  * @param string $needle
  * @param string $haystack
  * @param integer $offset optional, default value 0
  * @access public
  * @return integer position
  */
  function sqliteCallBackLOCATE($needle, $haystack, $offset = 0) {
    $pos = strpos($haystack, $needle, $offset);
    if ($pos !== FALSE) {
      return ++$pos;
    } else {
      return 0;
    }
  }

  /**
  * Compare field structure
  *
  * @param array $xmlField
  * @param array $databaseField
  * @access public
  * @return boolean
  */
  function compareFieldStructure($xmlField, $databaseField) {
    if ($xmlField['type'] != $databaseField['type']) {
      return TRUE;
    } elseif ($xmlField['size'] != $databaseField['size']) {
      if ($xmlField['type'] == 'string' &&
          $xmlField['size'] > 255 &&
          $databaseField['size'] > 255) {
        return FALSE;
      }
      if ($xmlField['type'] == 'integer' &&
          $databaseField['autoinc'] == 'yes' &&
          $xmlField['autoinc'] == 'yes') {
        return FALSE;
      }
      return TRUE;
    } elseif ($xmlField['autoinc'] == 'yes' &&
              $databaseField['autoinc'] != 'yes') {
      return TRUE;
    } elseif ($xmlField['default'] != $databaseField['default']) {
      if (!(empty($xmlField['default']) && empty($databaseField['default']))) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Compare key structure
  *
  * @param array $xmlKey
  * @param array $databaseKey
  * @access public
  * @return boolean
  */
  function compareKeyStructure($xmlKey, $databaseKey) {
    if (($xmlKey['unique'] == 'yes' || $xmlKey['name'] == "PRIMARY") !=
        ($databaseKey['unique'] == 'yes' || $databaseKey['name'] == "PRIMARY")) {
      return TRUE;
    } elseif (count(array_intersect($xmlKey['fields'], $databaseKey['fields'])) !=
              count($xmlKey['fields'])) {
      return TRUE;
    }
    return FALSE;
  }
}

/**
* DB-Abstractionslayer - result object - SQLite
* @package Papaya-Library
* @subpackage Database
*/
class dbresult_sqlite extends dbresult_base {

  /**
  * destructor
  *
  * Free memory, unset self and resultID
  *
  * @access public
  */
  function free() {
    if (isset($this->result) && is_resource($this->result)) {
      unset($this->result);
    }
  }

  /**
  * Fetch next row of result
  *
  * @param integer $mode line return modus
  * @access public
  * @return mixed FALSE or next line
  */
  function fetchRow($mode = DB_FETCHMODE_DEFAULT) {
    if (isset($this->result) && is_resource($this->result)) {
      if ($mode == DB_FETCHMODE_ASSOC) {
        /** @noinspection PhpParamsInspection */
        $result = sqlite_fetch_array($this->result, SQLITE_ASSOC);
        if (isset($result) && is_array($result)) {
          $data = array();
          foreach ($result as $key => $val) {
            if (strpos($key, '.') !== FALSE) {
              $field = substr($key, strpos($key, '.') + 1);
            } else {
              $field = $key;
            }
            $data[$field] = $val;
          }
          $result = $data;
        }
      } else {
        /** @noinspection PhpParamsInspection */
        $result = sqlite_fetch_array($this->result, SQLITE_NUM);
      }
      if (isset($result) && is_array($result)) {
        $this->recNo++;
      }
      return $result;
    }
    return FALSE;
  }

  /**
  * Number rows affected by query
  *
  * @access public
  * @return mixed number of rows or FALSE
  */
  function count() {
    if (isset($this->result) && is_resource($this->result)) {
      return sqlite_num_rows($this->result);
    }
    return FALSE;
  }

  /**
  * Search index
  *
  * Move record pointer to given index
  * next call of pg_fetch_row() returns wanted value
  *
  * @param integer $index
  * @access public
  * @return boolean
  */
  function seek($index) {
    if (isset($this->result) && is_resource($this->result)) {
      if (sqlite_seek($this->result, $index)) {
        $this->recNo = $index;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Compile database explain for SELECT query
  *
  * @return NULL|PapayaMessageContextInterface
  */
  public function getExplain() {
    $explainQuery = 'EXPLAIN '.$this->query;
    if ($res = $this->connection->executeQuery($explainQuery)) {
      if (sqlite_num_rows($res) > 0 ) {
        $explain = new PapayaMessageContextTable('Explain');
        while ($row = sqlite_fetch_array($res, SQLITE_NUM)) {
          $explain->addRow($row);
        }
        return $explain;
      }
    }
    return NULL;
  }
}
