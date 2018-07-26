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
require_once __DIR__.'/base.php';

/**
* DB-abstraction layer - connection object MySQLDB
*
* @package Papaya-Library
* @subpackage Database
*/
class dbcon_mysql extends dbcon_base {

  /**
  * MySQL version
  * @var integer $mysqlVersion
  */
  var $mysqlVersion = 0;


  /**
  * Check that the mysql extension is available
  *
  * @throws Papaya\Database\Exception\Connect
  * @return bool
  */
  public function extensionFound() {
    if (!extension_loaded('mysql')) {
      throw new Papaya\Database\Exception\Connect(
        'Extension "mysql" not available.'
      );
    }
    return TRUE;
  }

  /**
   * Establish connection to database
   *
   * @throws Papaya\Database\Exception\Connect
   * @return boolean
   */
  public function connect() {
    if (isset($this->databaseConnection) && is_resource($this->databaseConnection)) {
      return TRUE;
    } else {
      if (isset($this->databaseConfiguration->socket)) {
        $server = 'localhost:'.$this->databaseConfiguration->socket;
      } else {
        $server = ($this->databaseConfiguration->port > 0)
          ? ($this->databaseConfiguration->host.':'.$this->databaseConfiguration->port)
          : $this->databaseConfiguration->host;
      }
      if (defined('PAPAYA_DB_CONNECT_PERSISTENT') && PAPAYA_DB_CONNECT_PERSISTENT) {
        $connection = @mysql_pconnect(
          $server,
          $this->databaseConfiguration->username,
          $this->databaseConfiguration->password
        );
      } else {
        $connection = @mysql_connect(
          $server,
          $this->databaseConfiguration->username,
          $this->databaseConfiguration->password,
          TRUE
        );
      }
      if ($connection) {
        $selected = mysql_select_db(
          $this->databaseConfiguration->database,
          $connection
        );
        if (!$selected) {
          throw new Papaya\Database\Exception\Connect(
            sprintf(
              'Could not select database "%s".',
              $this->databaseConfiguration->database
            )
          );
        } elseif ($this->mysqlVersion = @mysql_get_server_info($connection)) {
          $this->databaseConnection = $connection;
          if (version_compare($this->mysqlVersion, '4.1', '>=')) {
            if (defined('PAPAYA_DATABASE_COLLATION')) {
              $this->executeQuery("SET NAMES 'utf8' COLLATE '".PAPAYA_DATABASE_COLLATION."'");
            } else {
              $this->executeQuery("SET NAMES 'utf8'");
            }
          }
          return TRUE;
        }
      }
      throw new Papaya\Database\Exception\Connect(mysql_error(), mysql_errno());
    }
  }

  /**
  * close connection
  *
  * @access public
  */
  function close() {
    if (isset($this->databaseConnection) &&
        is_resource($this->databaseConnection)) {
      mysql_close($this->databaseConnection);
    }
  }

  /**
  * String ecsaping for MySQL use
  *
  * @param mixed $value Value to escape
  * @return string escaped value.
  */
  function escapeString($value) {
    $value = parent::escapeString($value);
    if (isset($this->databaseConnection) && is_resource($this->databaseConnection)) {
      return mysql_real_escape_string((string)$value, $this->databaseConnection);
    } else {
      /** @noinspection PhpDeprecationInspection */
      return mysql_escape_string((string)$value);
    }
  }

  /**
  * Execute MySQL-query
  *
  * @param string $sql SQL-String with query
  * @param integer $max maximum number of returned records
  * @param integer $offset Offset
  * @param boolean $freeLastResult free last result (if here is one)
  * @param boolean $enableCounter enable direct calculation of
  *                               absolute record count for limited queries
  * @access public
  * @return boolean|integer|dbresult_mysql on failure FALSE; on success number
  *   of affected rows or a database result object
  */
  function &query(
    $sql, $max = NULL, $offset = NULL, $freeLastResult = TRUE, $enableCounter = FALSE
  ) {
    if (
      $freeLastResult &&
      is_object($this->lastResult) &&
      is_a($this->lastResult, 'dbresult_mysql')
    ) {
      $this->lastResult->free();
    }
    $queryRowCount = FALSE;
    $limitSQL = '';
    if (isset($max) && $max > 0 && strpos(trim(strtoupper($sql)), 'SELECT') === 0) {
      if ($enableCounter &&
          version_compare($this->mysqlVersion, '4.1', '>=')) {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS '.substr(trim($sql), 6);
        $queryRowCount = TRUE;
      }
      $limitSQL = (isset($offset) && $offset >= 0) ?
        ' LIMIT '.(int)$offset.','.(int)$max : ' LIMIT '.(int)$max;
    }
    $this->lastSQLQuery = $sql.$limitSQL;
    $res = $this->executeQuery($sql.$limitSQL);
    if ($res) {
      if (is_resource($res)) {
        $this->lastResult = new dbresult_mysql($this, $res, $sql);
        $this->lastResult->setLimit($max, $offset);
        $this->lastResult->_absCount = -1;
        if ($queryRowCount) {
          if ($res2 = $this->executeQuery("SELECT FOUND_ROWS()")) {
            if ($row = mysql_fetch_row($res2)) {
              $this->lastResult->_absCount = (int)$row[0];
            }
            mysql_free_result($res2);
          }
        }
        return $this->lastResult;
      } else {
        $result = @mysql_affected_rows($this->databaseConnection);
        return $result;
      }
    } else {
      $result = FALSE;
      return $result;
    }
  }

  /**
   * Wrap query execution so we can convert the erorr to an exception
   *
   * @param string $sql
   * @throws Papaya\Database\Exception\Query
   * @return boolean|NULL|resource on success a resource if a result set is
   *   available, otherwise TRUE; on failure NULL
   */
  public function executeQuery($sql) {
    if ($result = @mysql_query($sql, $this->databaseConnection)) {
      return $result;
    }
    throw $this->_createQueryException($sql);
  }

  /**
   * If a query failes, trow an database exception
   *
   * @param string $sql
   * @return \Papaya\Database\Exception\Query
   */
  private function _createQueryException($sql) {
    $errorCode = mysql_errno($this->databaseConnection);
    $errorMessage = mysql_error($this->databaseConnection);
    $severityMapping = array(
      // 1062 - duplicate entry
      1062 => PapayaDatabaseException::SEVERITY_WARNING,
      // 1205 - lock error
      1205 => PapayaDatabaseException::SEVERITY_INFO,
      // 1213 - deadlock error
      1213 => PapayaDatabaseException::SEVERITY_INFO,
    );
    if (isset($severityMapping[$errorCode])) {
      $severity = $severityMapping[$errorCode];
    } else {
      $severity = PapayaDatabaseException::SEVERITY_ERROR;
    }
    return new Papaya\Database\Exception\Query(
      $errorMessage, $errorCode, $severity, $sql
    );
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
      if ($res = $this->executeQuery($countSql)) {
        if ($row = mysql_fetch_row($res)) {
          $result = $row[0];
        } else {
          $result = 0;
        }
        @mysql_free_result($res);
        return $result;
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
        if (isset($idField) && $idField == $field) {
          continue;
        }
        $fieldString .= $this->escapeString($field).', ';
        if ($value === NULL) {
          $valueString .= "NULL, ";
        } elseif (is_bool($value)) {
          $valueString .= "'".($value ? '1' : '0')."', ";
        } else {
          $valueString .= "'".$this->escapeString($value)."', ";
        }
      }
      $fieldString = substr($fieldString, 0, -2);
      $valueString = substr($valueString, 0, -2);
      $sql = 'INSERT INTO '.$this->escapeString($table).' ('.$fieldString.')
              VALUES ('.$valueString.')';
      if ($this->query($sql, NULL, NULL, FALSE)) {
        if (isset($idField)) {
          return $this->lastInsertId($table, $idField);
        } else {
          return mysql_affected_rows($this->databaseConnection);
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
  * @return string|int|null
  */
  public function lastInsertId($table, $idField) {
    if ($res = $this->executeQuery('SELECT LAST_INSERT_ID()')) {
      return mysql_result($res, 0, 0);
    } else {
      return NULL;
    }
  }

  /**
  * Insert records into table
  *
  * @param string $table
  * @param array $values
  * @access public
  * @return boolean
  */
  function insertRecords($table, $values) {
    $baseSQL = 'INSERT INTO '.$this->escapeString($table).' ';
    $valueString = '';
    $lastFields = NULL;
    $maxQuerySize = 524288;
    $this->lastSQLQuery = '';
    if (isset($values) && is_array($values) && count($values) > 0) {
      foreach ($values as $data) {
        if (is_array($data) && count($data) > 0) {
          $fields = array();
          $valueData = array();
          foreach ($data as $key => $val) {
            $fields[] = $this->escapeString($key);
            $valueData[] = $this->escapeString($val);
          }
          if (!isset($lastFields)) {
            $valueString = "('".implode("','", $valueData)."'), ";
            $lastFields = $fields;
          } elseif (strlen($valueString) > $maxQuerySize) {
            if (trim($valueString) != '') {
              $sql = $baseSQL."(".implode(",", $lastFields).") VALUES ".
                substr($valueString, 0, -2);
              if (FALSE === $this->query($sql)) {
                return FALSE;
              }
            }
            $valueString = "('".implode("','", $valueData)."'), ";
            $lastFields = $fields;
          } elseif (count(array_diff($fields, $lastFields)) == 0) {
            $valueString .= "('".implode("','", $valueData)."'), ";
          } else {
            if (trim($valueString) != '') {
              $sql = $baseSQL."(".implode(",", $lastFields).") VALUES ".
                substr($valueString, 0, -2);
              if (FALSE === $this->query($sql)) {
                return FALSE;
              }
            }
            $valueString = "('".implode("','", $valueData)."'), ";
            $lastFields = $fields;
          }
        }
      }
      if (trim($valueString) != '') {
        $sql = $baseSQL."(".implode(",", $lastFields).") VALUES ".
          substr($valueString, 0, -2);
        if (FALSE !== $this->query($sql)) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
  * Update records via filter
  *
  * @param string $table table name
  * @param array $values update values
  * @param mixed $filter Filter string without WHERE condition
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  * @see dbcon_base::getSQLCondition()
  */
  function updateRecord($table, $values, $filter) {
    if (isset($values) && is_array($values) && count($values) > 0) {
      $sql = '';
      foreach ($values as $col => $val) {
        $fieldName = trim($col);
        if (preg_match('/[^`]+/', $fieldName)) {
          if ($val === NULL) {
            $sql .= " ".$this->escapeString($fieldName)." = NULL, ";
          } elseif (is_bool($val)) {
            $sql .= " ".$this->escapeString($fieldName)." = '".($val ? '1' : '0')."', ";
          } else {
            $sql .= " ".$this->escapeString($fieldName)." = '".$this->escapeString($val)."', ";
          }
        }
      }
      if (!empty($sql)) {
        $sql = "UPDATE ".$this->escapeString($table)." SET ".substr($sql, 0, -2).
              " WHERE ".$this->getSQLCondition($filter);
        $this->lastSQLQuery = $sql;
        return $this->query($sql);
      } else {
        $this->lastSQLQuery = 'NO VALID DATA';
      }
    } else {
      $this->lastSQLQuery = 'NO DATA';
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
    $sql = "DELETE
              FROM $table
             WHERE ".$this->getSQLCondition($filter);
    return $this->query($sql);
  }

  /**
  * Get table names
  *
  * @access public
  * @return array
  */
  function queryTableNames() {
    $sql = "SHOW TABLES";
    $result = array();
    if ($res = $this->query($sql)) {
      while ($row = $res->fetchRow()) {
        $result[] = $row[0];
      }
    }
    return $result;
  }

  /**
  * Parse MySQL Field type
  *
  * @param string $typeString
  * @access private
  * @return array
  */
  function parseMySQLFieldType($typeString) {
    $p = strpos($typeString, '(');
    if ($p !== FALSE) {
      $mysqlType = trim(substr($typeString, 0, $p));
      $size = trim(substr($typeString, $p + 1, strpos($typeString, ')') - $p - 1));
    } else {
      $mysqlType = trim($typeString);
      $size = 0;
    }
    switch (strtoupper($mysqlType)) {
    case 'TINYINT':
    case 'SMALLINT':
      $type = 'integer';
      $size = 2;
      break;
    case 'MEDIUMINT':
    case 'INT':
    case 'INTEGER':
      $type = 'integer';
      $size = 4;
      break;
    case 'BIGINT':
      $size = 8;
      $type = 'integer';
      break;
    case 'FLOAT':
    case 'DOUBLE':
    case 'REAL':
    case 'DECIMAL':
    case 'NUMERIC':
      $type = 'float';
      break;
    case 'TINYTEXT':
      $type = 'string';
      $size = '255';
      break;
    case 'TEXT':
      $type = 'string';
      $size = '65535';
      break;
    case 'MEDIUMTEXT':
      $type = 'string';
      $size = '16777215';
      break;
    case 'LONGTEXT':
      $type = 'string';
      $size = '4294967295';
      break;
    case 'CHAR':
    case 'VARCHAR':
    default :
      $type = 'string';
      break;
    }
    return array($type, $size);
  }

  /**
  * Parse MySQL field data
  *
  * @param array $row
  * @access private
  * @return array
  */
  function parseMySQLFieldData($row) {
    $type = $this->parseMySQLFieldType($row['Type']);
    $autoIncrement = (strtolower($row['Extra']) == 'auto_increment');
    $default = NULL;
    if (isset($row['Default'])) {
      if ($type[0] == 'integer') {
        $default = (int)$row['Default'];
      } elseif ($type[0] == 'float') {
        $default = (float)$row['Default'];
      } elseif (empty($row['Default']) && strtolower($row['Null']) == 'yes') {
        $default = NULL;
      } else {
        $default = $row['Default'];
      }
    }
    return array(
      'name' => $row['Field'],
      'type' => $type[0],
      'size' => $type[1],
      'null' => (strtolower($row['Null']) == 'yes') ? 'yes' : 'no',
      'default' => isset($default) ? (string)$default : NULL,
      'autoinc' => $autoIncrement ? 'yes' : 'no'
    );
  }

  /**
  * MySQL field type
  *
  * @param string $type
  * @param string $size
  * @access private
  * @return string
  */
  function getMySQLFieldType($type, $size) {
    switch (strtolower(trim($type))) {
    case 'integer':
      $size = ($size > 0) ? (int)$size : 1;
      if ($size <= 2) {
        $result = "SMALLINT";
      } else if ($size <= 4) {
        $result = "INT";
      } else {
        $result = "BIGINT";
      }
      break;
    case 'float':
      if (FALSE !== strpos($size, ',')) {
        list($before, $after) = explode(',', $size);
        $before = ($before > 0) ? (int)$before : 1;
        $after = (int)$after;
        if ($after > $before) {
          $before += $after;
        }
        $result = "DECIMAL(".$before.','.$after.")";
      } else {
        $result = "DECIMAL(".(int)$size.',0)';
      }
      break;
    case 'string':
    default:
      $size = ($size > 0) ? (int)$size : 1;
      if ($size <= 4) {
        $result = "CHAR(".$size.")";
      } elseif ($size <= 255) {
        $result = "VARCHAR(".$size.")";
      } elseif ($size <= 65535) {
        $result = "TEXT";
      } elseif ($size <= 16777215) {
        $result = "MEDIUMTEXT";
      } else {
        $result = "LONGTEXT";
      }
      break;
    }
    return $result;
  }

  /**
   * Get MySQL field extras
   *
   * @param array $field
   * @param bool $allowAutoIncrement
   * @access private
   * @return string
   */
  function getMySQLFieldExtras($field, $allowAutoIncrement = FALSE) {
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
    if (isset($default)) {
      switch(strtolower($field['type'])) {
      case 'integer':
        $defaultStr = " DEFAULT '".(int)$default."'";
        break;
      case 'float':
        $defaultStr = " DEFAULT '".(double)$default."'";
        break;
      case 'string' :
        if ($field['size'] > 255) {
          $defaultStr = "";
        } else {
          $defaultStr = " DEFAULT '".$this->escapeString($default)."'";
        }
        break;
      default:
        $defaultStr = " DEFAULT '".$this->escapeString($default)."'";
        break;
      }
    } else {
      $defaultStr = " DEFAULT NULL";
    }
    if (($allowAutoIncrement) &&
        isset($field['autoinc']) && $field['autoinc'] == 'yes') {
      $autoIncrementString = ' auto_increment';
      $defaultStr = '';
    } else {
      $autoIncrementString = '';
    }
    return $defaultStr.$notNullStr.$autoIncrementString;
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
    if ($tablePrefix) {
      $table = $tablePrefix.'_'.$tableName;
    } else {
      $table = $tableName;
    }
    $sql = "SHOW TABLE STATUS LIKE '$table'";
    $tableType = NULL;
    if ($res = $this->query($sql)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $tableType = (strtoupper($row['Engine']) == 'INNODB') ?
          'transactions' : NULL;
      }
    }
    $sql = "SHOW FIELDS
            FROM $table";
    if ($res = $this->query($sql)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $fields[$row['Field']] = $this->parseMySQLFieldData($row);
      }
    }
    $keys = array();
    $sql = "SHOW KEYS
            FROM $table";
    if ($res = $this->query($sql)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $keyName = $row['Key_name'];
        $keys[$keyName]['name'] = $keyName;
        $keys[$keyName]['unique'] = ($row['Non_unique'] == 0) ? 'yes' : 'no';
        $keys[$keyName]['fields'][$row['Seq_in_index']] = $row['Column_name'];
        if (isset($row['Sub_part'])) {
          $keys[$keyName]['keysize'][$row['Column_name']] = (int)$row['Sub_part'];
        } elseif ($fields[$row['Column_name']] == 'string' &&
                  $fields[$row['Column_name']] >= 255) {
          $keys[$keyName]['keysize'][$row['Column_name']] = 255;
        } else {
          $keys[$keyName]['keysize'][$row['Column_name']] = 0;
        }
        if (isset($row['Index_type']) && $row['Index_type'] == 'FULLTEXT') {
          $keys[$keyName]['fulltext'] = 'yes';
        } elseif ($row['Comment'] == 'FULLTEXT') {
          $keys[$keyName]['fulltext'] = 'yes';
        } else {
          $keys[$keyName]['fulltext'] = 'no';
        }
      }
    }
    return array(
      'name' => $tableName,
      'type' => $tableType,
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
  function createTable($tableData, $tablePrefix) {
    if (isset($tableData['fields']) &&
        is_array($tableData['fields']) &&
        isset($tableData['name']) &&
        trim($tableData['name']) != '') {
      if ($tablePrefix) {
        $table = $tablePrefix.'_'.trim($tableData['name']);
      } else {
        $table = trim($tableData['name']);
      }
      $sql = 'CREATE TABLE `'.$this->escapeString(strtolower($table)).'` ('.LF;
      $autoIncrementField = FALSE;
      foreach ($tableData['fields'] as $field) {
        $sql .= '  `'.strtolower($field['name']).'` '.
          $this->getMySQLFieldType($field['type'], $field['size']).
          $this->getMySQLFieldExtras($field, !$autoIncrementField).",\n";
      }
      $fulltextIndex = FALSE;
      if (isset($tableData['keys']) && is_array($tableData['keys'])) {
        if (isset($tableData['keys']['PRIMARY'])) {
          $key = $tableData['keys']['PRIMARY'];
          $fieldStr = '(';
          foreach ($key['fields'] as $fieldName) {
            if (isset($key['keysize'][$fieldName]) &&
                $key['keysize'][$fieldName] > 0) {
              $fieldStr .= '`'.$this->escapeString($fieldName).'` ('.
                (int)$key['keysize'][$fieldName].'), ';
            } else {
              $fieldStr .= '`'.$this->escapeString($fieldName).'`, ';
            }
          }
          $sql .= 'PRIMARY KEY '.substr($fieldStr, 0, -2)."),\n";
        }
        foreach ($tableData['keys'] as $keyName => $key) {
          if ($keyName != 'PRIMARY') {
            if (isset($key['unique']) && $key['unique'] == 'yes') {
              $sql .= '  UNIQUE ';
            } elseif (isset($key['fulltext']) && $key['fulltext'] == 'yes') {
              $sql .= '  FULLTEXT ';
              $fulltextIndex = TRUE;
            } else {
              $sql .= ' KEY ';
            }
            $fieldStr = '(';
            foreach ($key['fields'] as $fieldName) {
              if (isset($key['keysize'][$fieldName]) &&
                  $key['keysize'][$fieldName] > 0) {
                $fieldStr .= '`'.$this->escapeString($fieldName).'` ('.
                  (int)$key['keysize'][$fieldName].'), ';
              } else {
                $fieldStr .= '`'.$this->escapeString($fieldName).'`, ';
              }
            }
            $sql .= '`'.$keyName.'` '.substr($fieldStr, 0, -2)."),\n";
          }
        }
      }
      $sql = substr($sql, 0, -2)."\n) ";
      if (version_compare($this->mysqlVersion, '4.1.2', '>=')) {
        $typeKeyword = 'ENGINE';
      } else {
        $typeKeyword = 'TYPE';
      }
      if ($fulltextIndex) {
        $sql .= ' '.$typeKeyword.'=MyISAM';
      } elseif (isset($tableData['type']) && $tableData['type'] == 'transactions') {
        $sql .= ' '.$typeKeyword.'=InnoDB';
      }
      if (version_compare($this->mysqlVersion, '4.1.0', '>=')) {
        $sql .= ' DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci';
      }
      return ($this->query($sql) !== FALSE);
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
    $sql = "ALTER TABLE `".$this->escapeString($table)."` ADD COLUMN `".
          $this->escapeString($fieldData['name'])."` ".
          $this->getMySQLFieldType($fieldData['type'], $fieldData['size']).
          $this->getMySQLFieldExtras($fieldData);
    return ($this->query($sql) !== FALSE);
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
    $allowAutoIncrement = FALSE;
    if (isset($fieldData['autoinc']) && $fieldData['autoinc'] == 'yes') {
      $sql = "SHOW COLUMNS FROM `".$this->escapeString($table)."`";
      if ($res = $this->query($sql)) {
        $autoIncrementField = NULL;
        $fieldExists = FALSE;
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          if ($row['Field'] == $fieldData['name']) {
            $allowAutoIncrement = (trim($row['Key']) != '');
            if (strtolower($row['Extra']) == 'auto_increment') {
              unset($autoIncrementField);
              break;
            }
            $fieldExists = TRUE;
          } elseif (strtolower($row['Extra']) == 'auto_increment') {
            $autoIncrementField = $this->parseMySQLFieldData($row);
          }
          if ($fieldExists && isset($autoIncrementField)) {
            break;
          }
        }
        $res->free();
        if (isset($autoIncrementField)) {
          $autoIncrementField['autoinc'] = 'no';
          $sql = "ALTER TABLE `".$this->escapeString($table)."` MODIFY COLUMN `".
            $this->escapeString($autoIncrementField['name'])."` ".
            $this->getMySQLFieldType(
              $autoIncrementField['type'],
              $autoIncrementField['size']
            ).
            $this->getMySQLFieldExtras($autoIncrementField);
          $this->query($sql);
        }
      }
    }
    $sql = "ALTER TABLE `".$this->escapeString($table)."` MODIFY COLUMN `".
          $this->escapeString($fieldData['name'])."` ".
          $this->getMySQLFieldType($fieldData['type'], $fieldData['size']).
          $this->getMySQLFieldExtras($fieldData, $allowAutoIncrement);
    return ($this->query($sql) !== FALSE);
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
    $sql = "ALTER TABLE `".$this->escapeString($table)."` DROP COLUMN `".
      $this->escapeString($field)."`";
    return ($this->query($sql) !== FALSE);
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
    if (isset($index['fields']) && is_array($index['fields'])) {
      $sql = "SHOW COLUMNS
              FROM `".$this->escapeString($table)."`";
      if ($res = $this->query($sql)) {
        $needed = count($index['fields']);
        $indb = 0;
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          if (in_array($row['Field'], $index['fields'])) {
            if (++$indb >= $needed) {
              break;
            }
          }
        }
        $res->free();
        if ($indb >= $needed) {
          $fields = '(';
          foreach ($index['fields'] as $fieldName) {
            if (isset($index['keysize'][$fieldName]) &&
                $index['keysize'][$fieldName] > 0) {
              $fields .= '`'.$this->escapeString($fieldName).'` ('.
                (int)$index['keysize'][$fieldName].'), ';
            } else {
              $fields .= '`'.$this->escapeString($fieldName).'`, ';
            }
          }
          $fields = substr($fields, 0, -2).")";
          $drop = ($dropCurrent) ?
            " DROP INDEX `".$this->escapeString($index['name'])."`," : '';
          if ($index['name'] == 'PRIMARY') {
            $sql = "ALTER TABLE `".$this->escapeString($table)."`".$drop.
              " ADD PRIMARY KEY ".$fields;
          } elseif (isset($index['fulltext']) && $index['fulltext'] == 'yes') {
            $sql = "ALTER TABLE `".$this->escapeString($table)."`".$drop.
              " ADD FULLTEXT `".$this->escapeString($index['name'])."` ".$fields;
          } elseif (isset($index['unique']) && $index['unique'] == 'yes') {
            $sql = "ALTER TABLE `".$this->escapeString($table)."`".$drop.
              " ADD UNIQUE `".$this->escapeString($index['name'])."` ".$fields;
          } else {
            $sql = "ALTER TABLE `".$this->escapeString($table)."`".$drop.
              " ADD INDEX `".$this->escapeString($index['name'])."` ".$fields;
          }
          return ($this->query($sql) !== FALSE);
        }
      }
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
    if ($name == 'PRIMARY') {
      $sql = "ALTER TABLE `".$this->escapeString($table)."` DROP PRIMARY KEY";
    } else {
      $sql = "ALTER TABLE `".$this->escapeString($table)."` DROP INDEX `".
        $this->escapeString($name)."`";
    }
    return ($this->query($sql) !== FALSE);
  }

  /**
  * DBMS specific SQL source
  *
  * @param string $function sql function
  * @param array $params params
  * @access public
  * @return mixed sql string or FALSE
  */
  function getSQLSource($function, array $params = NULL) {
    switch (strtoupper($function)) {
    case 'CONCAT' :
      return ' CONCAT('.$this->getSQLFunctionParams($params).')';
      break;
    case 'SUBSTRING' :
      return ' SUBSTRING('.$this->getSQLFunctionParams($params).')';
    case 'LENGTH' :
      return ' LENGTH('.$this->getSQLFunctionParams($params).')';
    case 'LOWER' :
      return ' LOWER('.$this->getSQLFunctionParams($params).')';
    case 'UPPER' :
      return ' UPPER('.$this->getSQLFunctionParams($params).')';
    case 'LOCATE' :
      return ' LOCATE('.$this->getSQLFunctionParams($params).')';
    case 'RANDOM' :
      return ' RAND()';
    case 'LIKE' :
      // Default escape character is "\"
      return ' LIKE '.$this->getSQLFunctionParams($params).' ESCAPE \'\\\\\'';
    }
    return FALSE;
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
      return TRUE;
    } elseif ($xmlField['null'] != $databaseField['null']) {
      return TRUE;
    } elseif ($xmlField['autoinc'] == 'yes' && $databaseField['autoinc'] != 'yes') {
      return TRUE;
    } elseif ($xmlField['default'] !== $databaseField['default']) {
      return TRUE;
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
    } elseif ($xmlKey['fulltext'] == 'yes' && $databaseKey['fulltext'] != 'yes') {
      return TRUE;
    } elseif (count(array_diff_assoc($xmlKey['keysize'], $databaseKey['keysize'])) > 0) {
      return TRUE;
    } elseif (count(array_diff($xmlKey['fields'], $databaseKey['fields'])) > 0) {
      return TRUE;
    }
    return FALSE;
  }
}

/**
* DB-Abstractionslayer - result object MySQL
*
* @package Papaya-Library
* @subpackage Database
*/
class dbresult_mysql extends dbresult_base {

  /**
  * destructor
  *
  * Free memory, unset self and resultID
  *
  * @access public
  */
  function free() {
    if (isset($this->result) && is_resource($this->result)) {
      @mysql_free_result($this->result);
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
        $result = @mysql_fetch_assoc($this->result);
      } else {
        $result = @mysql_fetch_row($this->result);
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
      return @mysql_num_rows($this->result);
    }
    return FALSE;
  }

  /**
  * Search index
  *
  * Move record pointer to given index
  * next call of mysql_fetch_row() returns wanted value
  *
  * @param integer $index
  * @access public
  * @return boolean
  */
  function seek($index) {
    if (isset($this->result) && is_resource($this->result)) {
      if (@mysql_data_seek($this->result, $index)) {
        $this->recNo = $index;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Compile database explain for SELECT query
  *
  * @access public
  * @return NULL|PapayaMessageContextInterface
  */
  public function getExplain() {
    $explainQuery = 'EXPLAIN '.$this->query;
    if ($res = $this->connection->executeQuery($explainQuery)) {
      if (mysql_num_rows($res) > 0 ) {
        $explain = new PapayaMessageContextTable('Explain');
        $explain->setColumns(
          array(
            'id' => 'Id',
            'select_type' => 'Select Type',
            'table' => 'Table',
            'type' => 'Type',
            'possible_keys' => 'Possible Keys',
            'key' => 'Key',
            'key_len' => 'Key Length',
            'ref' => 'Reference',
            'rows' => 'Rows',
            'Extra' => 'Extra'
          )
        );
        while ($row = mysql_fetch_assoc($res)) {
          $explain->addRow($row);
        }
        return $explain;
      }
    }
    return NULL;
  }
}
