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
* DB-abstraction layer - connection object PostgreSQL
*
* @package Papaya-Library
* @subpackage Database
*/
class dbcon_pgsql extends dbcon_base {

  /**
   * Check that the pgsql extension is available
   *
   * @access public
   * @throws \Papaya\Database\Exception\Connect
   * @return boolean
   */
  function extensionFound() {
    if (!extension_loaded('pgsql')) {
      throw new \Papaya\Database\Exception\Connect(
        'Extension "pgsql" not available.'
      );
    }
    return TRUE;
  }

  /**
   * Establish connection to database
   *
   * @access public
   * @throws \Papaya\Database\Exception\Connect
   * @throws Exception
   * @return resource $this->databaseConnection connection ID
   */
  function connect() {
    if (isset($this->databaseConnection) && is_resource($this->databaseConnection)) {
      return TRUE;
    } else {
      $connectStr = 'host='.$this->databaseConfiguration->host;
      if ($this->databaseConfiguration->port > 0) {
        $connectStr .= ' port='.$this->databaseConfiguration->port;
      }
      $connectStr .= ' user='.$this->databaseConfiguration->username;
      $connectStr .= ' password='.$this->databaseConfiguration->password;
      $connectStr .= ' dbname='.$this->databaseConfiguration->database;
      $connection = NULL;
      try {
        set_error_handler(
          array($this, 'handleConnectionError'), E_ALL & ~E_STRICT
        );
        if (defined('PAPAYA_DB_CONNECT_PERSISTENT') && PAPAYA_DB_CONNECT_PERSISTENT) {
          $connection = pg_pconnect($connectStr);
        } else {
          $connection = pg_connect($connectStr, PGSQL_CONNECT_FORCE_NEW);
        }
        restore_error_handler();
      } catch (Exception $e) {
        restore_error_handler();
        throw $e;
      }
      if (isset($connection) && is_resource($connection)) {
        if (pg_set_client_encoding($connection, 'UNICODE') !== 0) {
          throw new \Papaya\Database\Exception\Connect(
            'Can not set client encoding for database connection.'
          );
        }
        $this->databaseConnection = $connection;
        return TRUE;
      }
      return FALSE;
    }
  }

  public function handleConnectionError($code, $message) {
    throw new \Papaya\Database\Exception\Connect(
      strip_tags(str_replace('&quot;', '"', $message)), $code
    );
  }

  /**
  * close connection
  *
  * @access public
  */
  function close() {
    if (isset($this->databaseConnection) &&
        is_resource($this->databaseConnection)) {
      pg_close($this->databaseConnection);
    }
  }

  /**
   * Wrap query execution so we can convert the erorr to an exception
   *
   * @throws \Papaya\Database\Exception\Query
   * @param string $sql
   * @return bool|resource
   */
  public function executeQuery($sql) {
    if (FALSE !== ($result = @pg_query($this->databaseConnection, $sql))) {
      return $result;
    }
    return $this->_createQueryException($sql);
  }

  /**
   * If a query failes, trow an database exception
   *
   * @param string $sql
   * @return \Papaya\Database\Exception\Query
   */
  private function _createQueryException($sql) {
    $errorMessage = pg_last_error($this->databaseConnection);
    return new \Papaya\Database\Exception\Query(
      empty($errorMessage) ? 'Unknown PostgreSQL error.' : $errorMessage, 0, NULL, $sql
    );
  }

  /**
  * String ecsaping for PostgreSQL use
  *
  * @param mixed $value Value to escape
  * @access public
  * @return string escaped value.
  */
  function escapeString($value) {
    $value = parent::escapeString($value);
    return pg_escape_string($this->databaseConnection, $value);
  }

  /**
  * Execute PostgreSQL-query
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
        is_a($this->lastResult, 'dbresult_pgsql')) {
      $this->lastResult->free();
    }
    $limitSQL = '';
    if (isset($max) && $max > 0 && strpos(trim($sql), 'SELECT') === 0) {
      $limitSQL .= (isset($offset) && $offset >= 0)
        ? ' OFFSET '.(int)$offset
        : '';
      $limitSQL .= ' LIMIT '.(int)$max;
    }
    $this->lastSQLQuery = $sql.$limitSQL;
    $res = $this->executeQuery($sql.$limitSQL);
    if (is_resource($res)) {
      $this->lastResult = new dbresult_pgsql($this, $res, $sql);
      $this->lastResult->setLimit($max, $offset);
      $this->lastResult->_absCount = -1;
      return $this->lastResult;
    } else {
      $result = pg_affected_rows($res);
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
      if ($res = $this->executeQuery($countSql)) {
        if ($row = pg_fetch_row($res)) {
          $result = $row[0];
        } else {
          $result = 0;
        }
        pg_free_result($res);
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
      $sql = 'INSERT INTO '.$this->escapeString($table).' ('.$fieldString.') VALUES ('.
        $valueString.')';
      if (isset($idField)) {
        $sql .= ' RETURNING ' . $idField;
      }
      if ($result = $this->query($sql, NULL, NULL, FALSE)) {
        if (isset($idField)) {
          return $result->fetchField();
        }
        return $result;
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
    $sql = "SELECT CURRVAL('".$table."_".$idField."_seq')";
    if ($res = $this->executeQuery($sql)) {
      $result = pg_fetch_result($res, 0, 0);
      return $result;
    } else {
      return NULL;
    }
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
    $baseSQL = "COPY ".$this->escapeString($table)." ";
    $lastFields = NULL;
    $specialChars = array("\t" => '\t', "\r" => '\r', "\n" => '\n');
    $this->lastSQLQuery = '';
    if (isset($values) && is_array($values) && count($values) > 0) {
      foreach ($values as $data) {
        if (is_array($data) && count($data) > 0) {
          $fields = array();
          $valueData = array();
          foreach ($data as $key => $val) {
            $fields[] = strtr($this->escapeString($key), $specialChars);
            if ($val === '') {
              $valueData[] = '';
            } else {
              $valueData[] = strtr($this->escapeString($val), $specialChars);
            }
          }
          if (!isset($lastFields)) {
            $sql = $baseSQL."(".implode(",", $fields).
              ") FROM STDIN USING DELIMITERS '\t' WITH NULL AS '\\NULL' \n";
            if (!$this->executeQuery($sql)) {
              return FALSE;
            }
            $lastFields = $fields;
          } elseif (count(array_diff($fields, $lastFields)) > 0) {
            if (!pg_end_copy($this->databaseConnection)) {
              return FALSE;
            }
            $sql = $baseSQL."(".implode(",", $fields).
              ") FROM STDIN USING DELIMITERS '\t' WITH NULL AS '\\NULL' \n";
            if (!$this->executeQuery($sql)) {
              return FALSE;
            }
            $lastFields = $fields;
          }
          $line = implode("\t", $valueData).LF;
          if (!pg_put_line($this->databaseConnection, $line)) {
            pg_end_copy($this->databaseConnection);
            return FALSE;
          }
        }
      }
      if (!pg_end_copy($this->databaseConnection)) {
        return FALSE;
      }
      $this->updateAutoIncrementFields($table);
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Update autoincrement fields
  *
  * @param string $table
  * @access public
  */
  function updateAutoIncrementFields($table) {
    $structure = $this->queryTableStructure($table);
    $fields = $structure['fields'];
    if (isset($fields) && is_array($fields)) {
      foreach ($fields as $field) {
        if ($field['autoinc'] == 'yes') {
          $tableName = $this->escapeString($table);
          $fieldName = $this->escapeString($field['name']);
          $sql = "SELECT SETVAL('".$tableName."_".$fieldName."_seq',"
            ."(SELECT MAX(".$fieldName.") FROM ".$tableName."));".LF;
          $this->query($sql);
        }
      }
    }
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
    $sql = "SELECT tablename
              FROM pg_tables
             WHERE schemaname = 'public'";
    $result = array();
    if ($res = $this->query($sql)) {
      while ($row = $res->fetchRow()) {
        $result[] = $row[0];
      }
    }
    return $result;
  }

  /**
  * Parse PostgreSQL field data
  *
  * @param array $row
  * @access private
  * @return array
  */
  function parsePostgresFieldData($row) {
    $autoIncrement = 0;
    if (substr($row['fieldtype'], 0, 3) == 'int') {
      $type = 'integer';
      switch ($row['format_type']) {
      case 'bigint':
        $size = 8;
        break;
      case 'integer':
        $size = 4;
        break;
      case 'smallint':
        $size = 2;
        break;
      default :
        $size = 8;
      }
    } elseif ($row['fieldtype'] == 'varchar' || $row['fieldtype'] == 'bpchar') {
      $type = 'string';
      $size = substr(
        $row['format_type'],
        strrpos($row['format_type'], '(') + 1,
        -1
      );
    } elseif ($row['fieldtype'] == 'text') {
      $type = 'string';
      $size = 65535;
    } elseif ($row['fieldtype'] == 'numeric') {
      $type = 'float';
      $size = substr($row['format_type'], 8, -1);
    } else {
      $size = 16777215;
      $type = 'string';
    }
    $stringFieldPattern = '#^\'(([^\']+|\\\\)+)\'::(character varying|bpchar)$#i';
    $numericFieldPattern = '#^(\d+)(::(smallint|integer|bigint|int|numeric))?$#i';
    if (preg_match('#^nextval\(\'[\w\.]+\'::text\)$#i', $row['defaultwert'], $regs) ||
        preg_match("(^nextval\(\(\'[\w\.]+\'::text\)::regclass\)$)i", $row['defaultwert'], $regs) ||
        preg_match("(^nextval\(\'[\w\.]+\'::regclass\)$)i", $row['defaultwert'], $regs)) {
      $autoIncrement = 1;
      $default = 1;
    } elseif (preg_match($stringFieldPattern, $row['defaultwert'], $regs)) {
      if ($regs[1] != '') {
        $default = $regs[1];
      }
    } elseif (preg_match($numericFieldPattern, $row['defaultwert'], $regs)) {
      if ($regs[1] != 0 || (strtolower($row['not_null']) == 't')) {
        $default = $regs[1];
      }
    }
    $result = array(
      'name' => $row['fieldname'],
      'type' => $type,
      'size' => $size,
      'null' => (strtolower($row['not_null']) == 't') ? 'no' : 'yes',
      'autoinc' => ($autoIncrement > 0) ? 'yes' : 'no',
      'default' => NULL
    );
    if ((!$autoIncrement) && isset($default)) {
      $result['default'] = $default;
    }
    return $result;
  }

  /**
   * PostgresSQL field type
   *
   * @param array $field
   * @param bool $seperated
   * @access private
   * @return string
   */
  function getPostgresFieldType($field, $seperated = FALSE) {
    if (isset($field['autoinc']) && $field['autoinc'] == 'yes') {
      if ($seperated) {
        $result['type'] = ($field['size'] > 4) ? 'BIGINT' : 'INTEGER';
        $result['not_null'] = 'NOT NULL';
        $result['default'] = 'DEFAULT 1';
        $result['autoinc'] = TRUE;
        return $result;
      } else {
        return ($field['size'] > 4) ? 'BIGSERIAL' : 'SERIAL';
      }
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
        switch(strtolower($field['type'])) {
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
          list($before, $after) = explode(',', $field['size']);
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
      default :
        $size = ($field['size'] > 0) ? (int)$field['size'] : 1;
        if ($size <= 255) {
          $result = "VARCHAR(".$field['size'].")";
        } else {
          $result = "TEXT";
        }
        break;
      }
      if ($seperated) {
        return array(
          'type' => $result,
          'default' => $defaultStr,
          'not_null' => $notNullStr,
          'autoinc' => FALSE
        );
      } else {
        return $result.$defaultStr.$notNullStr;
      }
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
    if ($tablePrefix) {
      $table = $tablePrefix.'_'.$tableName;
    } else {
      $table = $tableName;
    }
    $sql = "SELECT a.attname AS fieldname,
                   t.typname AS fieldtype,
                   pg_catalog.format_type(a.atttypid, a.atttypmod),
                   a.attlen AS fieldsize,
                   a.attnotNull AS not_null,
                   (SELECT substring(d.adsrc for 128)
              FROM pg_catalog.pg_attrdef d
             WHERE d.adrelid = a.attrelid
               AND d.adnum = a.attnum
               AND a.atthasdef) AS defaultwert
              FROM pg_class c, pg_attribute a, pg_type t
             WHERE relkind = 'r'
               AND c.relname='$table'
               AND a.attnum > 0
               AND a.atttypid = t.oid
               AND a.attrelid = c.oid
             ORDER BY a.attnum";
    if ($res = $this->query($sql)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $fields[$row['fieldname']] = $this->parsePostgresFieldData($row);
      }
    }
    $keys = array();

    // alternative method to using generate_series
    // a.attnum, i.indkey,
    $sql = "SELECT ic.relname AS index_name, a.attname AS column_name,
                   i.indisunique AS unique_key, i.indisprimary AS primary_key,
                   idx
              FROM pg_class bc, pg_class ic, pg_index i, pg_attribute a,
                   generate_series(0,current_setting('max_index_keys')::integer-1) idx
             WHERE bc.oid = i.indrelid AND ic.oid = i.indexrelid
               AND i.indkey[idx] = a.attnum
               AND a.attrelid = bc.oid
               AND bc.relname = '$table'
             ORDER BY a.attnum";
    if ($res = $this->query($sql)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($row['primary_key'] == 't') {
          $keyName = 'PRIMARY';
        } elseif (strpos($row['index_name'], $table) === 0) {
          $keyName = substr($row['index_name'], strlen($table) + 1);
        } else {
          $keyName = $row['index_name'];
        }
        // alternative method to using generate_series
        //        $indexOrder = explode(' ', $row['indkey']);
        //        $indexPosition = array_search($row['attnum'], $indexOrder);
        $keys[$keyName]['orgname'] = $row['index_name'];
        $keys[$keyName]['name'] = $keyName;
        $keys[$keyName]['unique'] = ($row['unique_key'] == 't') ? 'yes' : 'no';
        $keys[$keyName]['fields'][$row['idx']] = $row['column_name'];
        // alternative method to using generate_series
        //        $keys[$keyName]['fields'][$indexPosition] = $row['column_name'];
        ksort($keys[$keyName]['fields']);
        $keys[$keyName]['fulltext'] = 'no';
      }
    }
    $result = array(
      'name' => $tableName,
      'fields' => $fields,
      'keys' => $keys
    );
    return $result;
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
    if (is_array($tableData['fields']) && trim($tableData['name']) != '') {
      if ($tablePrefix) {
        $table = $tablePrefix.'_'.trim($tableData['name']);
      } else {
        $table = trim($tableData['name']);
      }
      $sql = 'CREATE TABLE '.$this->escapeString(strtolower($table)).' ('.LF;
      foreach ($tableData['fields'] as $field) {
        $sql .= '  '.strtolower($field['name']).' '.
          $this->getPostgresFieldType($field).",\n";
      }
      if (isset($tableData['keys']) && is_array($tableData['keys'])) {
        if (isset($tableData['keys']['PRIMARY'])) {
          $sql .= 'CONSTRAINT '.$this->escapeString(strtolower($table)).
            '_primary_key PRIMARY KEY ('.
            implode(',', $tableData['keys']['PRIMARY']['fields'])."),\n";
        }
        foreach ($tableData['keys'] as $keyName => $key) {
          if ($keyName != 'PRIMARY' &&
              isset($key['unique']) && $key['unique'] == 'yes') {
            $sql .= 'CONSTRAINT '.$this->escapeString(strtolower($table)).'_'.
              $keyName.' UNIQUE ';
            $sql .= '('.implode(',', $key['fields'])."),\n";
          }
        }
      }
      $sql = substr($sql, 0, -2)."\n)\n";
      if ($this->query($sql) !== FALSE) {
        if (isset($tableData['keys']) && is_array($tableData['keys'])) {
          foreach ($tableData['keys'] as $key) {
            $this->addIndex($table, $key);
          }
        }
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Get field information
  *
  * @param string $table
  * @param string $fieldName
  * @access public
  * @return mixed array with row or boolean FALSE
  */
  function getFieldInfo($table, $fieldName) {
    $sql = "SELECT a.attname AS fieldname,
                   t.typname AS fieldtype,
                   pg_catalog.format_type(a.atttypid, a.atttypmod),
                   a.attlen AS fieldsize,
                   a.attnotNull AS not_null,
                   (SELECT substring(d.adsrc for 128)
              FROM pg_catalog.pg_attrdef d
             WHERE d.adrelid = a.attrelid
               AND d.adnum = a.attnum
               AND a.atthasdef) AS defaultwert
              FROM pg_class c, pg_attribute a, pg_type t
             WHERE relkind = 'r'
               AND c.relname='".$this->escapeString($table)."'
               AND a.attname='".$this->escapeString($fieldName)."'
               AND a.attnum > 0
               AND a.atttypid = t.oid
               AND a.attrelid = c.oid
             ORDER BY a.attnum";
    if ($res = $this->query($sql)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        return $this->parsePostgresFieldData($row);
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
    $tableName = $this->escapeString($table);
    $fieldName = $this->escapeString($fieldData['name']);

    $xmlType = $this->getPostgresFieldType($fieldData);

    //check field exists in db
    if ($databaseField = $this->getFieldInfo($table, $fieldData['name'])) {
      $dbType = $this->getPostgresFieldType($databaseField);
      if ($xmlType != $dbType) {
        $sqlData = $this->getPostgresFieldType($fieldData, TRUE);
        $sql = '';
        if ($sqlData['autoinc']) {
          $sql .= "ALTER TABLE $tableName
                   ALTER COLUMN $fieldName
                     SET DEFAULT nextval('public.{$table}_{$fieldName}_seq'::text);\n";
        } else {
          if (!empty($sqlData['default'])) {
            $sql .= "ALTER TABLE $tableName
                     ALTER COLUMN $fieldName
                       SET ".$sqlData['default']."::".$sqlData['type'].";\n";
          }
        }
        $sql .= "ALTER TABLE $tableName
                ALTER COLUMN $fieldName
                 TYPE ".$sqlData['type']." USING $fieldName::".$sqlData['type'].";\n";
        $sql .= "ALTER TABLE $tableName
                 ALTER COLUMN $fieldName ";
        if (empty($sqlData['not_null'])) {
          $sql .= " DROP NOT NULL ";
        } else {
          $sql .= " SET NOT NULL ";
        }
        $sql .= ";\n";
        return ($this->query($sql) !== FALSE);
      }
      return TRUE;
    } else {
      //create field
      $sql = "ALTER TABLE ".$this->escapeString($table)."
                ADD COLUMN ".$this->escapeString($fieldData['name'])." ".$xmlType;
      return ($this->query($sql) !== FALSE);
    }
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
    $sql = "ALTER TABLE ".$this->escapeString($table)."
             DROP COLUMN ".$this->escapeString($field)."";
    return ($this->query($sql) !== FALSE);
  }


  /**
  * Get index information
  *
  * @param string $table
  * @param string $key
  * @access public
  * @return array $result
  */
  function getIndexInfo($table, $key) {
    $result = FALSE;
    $tableName = $this->escapeString($table);
    $keyName = ($key == 'PRIMARY') ? 'primary_key' : $this->escapeString($key);
    $sql = "SELECT ic.relname, ic.relname AS index_name, a.attname AS column_name,
                   i.indisunique AS unique_key, i.indisprimary AS primary_key
              FROM pg_class bc, pg_class ic, pg_index i, pg_attribute a
             WHERE bc.oid = i.indrelid AND ic.oid = i.indexrelid
               AND (i.indkey[0] = a.attnum OR
                   i.indkey[1] = a.attnum OR
                   i.indkey[2] = a.attnum OR
                   i.indkey[3] = a.attnum OR
                   i.indkey[4] = a.attnum OR
                   i.indkey[5] = a.attnum OR
                   i.indkey[6] = a.attnum OR
                   i.indkey[7] = a.attnum)
               AND a.attrelid = bc.oid
               AND bc.relname = '".$tableName."'
               AND ic.relname = '".$tableName.'_'.$keyName."'
             ORDER BY a.attnum";
    if ($res = $this->query($sql)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($row['primary_key'] == 't') {
          $keyName = 'PRIMARY';
        } elseif (strpos($row['index_name'], $table) === 0) {
          $keyName = substr($row['index_name'], strlen($table) + 1);
        } else {
          $keyName = $row['index_name'];
        }
        $result['orgname'] = $row['index_name'];
        $result['name'] = $keyName;
        $result['unique'] = ($row['unique_key'] == 't') ? 'yes' : 'no';
        $result['fields'][] = $row['column_name'];
        $result['fulltext'] = 'no';
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
    $result = NULL;
    try {
      return $this->changeIndex($table, $index, FALSE);
    } catch (\Papaya\Database\Exception\Query $e) {
      $logMessage = new \PapayaMessageLog(
        \PapayaMessageLogable::GROUP_DATABASE,
        \Papaya\Message::SEVERITY_ERROR,
        'Database #' . $e->getCode() . ': ' . $e->getMessage()
      );
      $logMessage
        ->context()
        ->append(new \PapayaMessageContextBacktrace(3))
        ->append(new \PapayaMessageContextText($table))
        ->append(new \PapayaMessageContextText($index))
        ->append(new \PapayaMessageContextText($e->getStatement()));
      //$this->getApplication()->messages->dispatch($logMessage);
    }
    return FALSE;
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
        $sql = "ALTER TABLE ".$this->escapeString($table)."
                  ADD PRIMARY KEY ".$fields;
      } elseif (isset($index['unique']) && $index['unique'] == 'yes') {
        $sql = 'CREATE UNIQUE INDEX '.$this->escapeString($table).'_'.
          $this->escapeString($index['name']).' ON '.$this->escapeString($table).' '.
            $fields;
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
    if ($key = $this->getIndexInfo($table, $name)) {
      $keyName = $this->escapeString($key['orgname']);
      if ($key['name'] == 'PRIMARY') {
        $sql = 'ALTER TABLE '.$table.'
                 DROP CONSTRAINT '.$keyName;
      } else {
        $sql = 'DROP INDEX '.$keyName;
      }
      return ($this->query($sql) !== FALSE);
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
        $result .= $this->getSQLFunctionParam(
          $params[$i], isset($params[$i + 1]) ? $params[$i + 1] : TRUE
        ).' || ';
      }
      return substr($result, 0, -3);
    case 'SUBSTRING':
      return ' SUBSTR('.$this->getSQLFunctionParams($params).')';
    case 'LENGTH':
      return ' CHAR_LENGTH('.$this->getSQLFunctionParams($params).')';
    case 'LOWER':
      return ' LOWER('.$this->getSQLFunctionParams($params).')';
    case 'UPPER':
      return ' UPPER('.$this->getSQLFunctionParams($params).')';
    case 'LOCATE':
      $result = '';
      if (count($params) >= 3) {
        $result = ' POSITION(';
        $result .= $this->getSQLFunctionParam(
          $params[0], isset($params[1]) ? $params[1] : TRUE
        ).' IN ';
        $result .= $this->getSQLFunctionParam(
          $params[2], isset($params[3]) ? $params[3] : TRUE
        ).')';
      }
      return $result;
    case 'RANDOM' :
      return ' RANDOM()';
    case 'LIKE' :
      // Default escape character is "\"
      return ' LIKE '.$this->getSQLFunctionParams($params).' ESCAPE \'\\\\\'';
    }
    return FALSE;
  }

  /**
  * Compare the field structure
  *
  * @param array $xmlField
  * @param array $databaseField
  * @access public
  * @return boolean different
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
          $xmlField['autoinc'] == 'yes' &&
          (
           ($xmlField['size'] == 2 && $databaseField['size'] == 4) ||
           ($xmlField['size'] == 6 && $databaseField['size'] == 8)
          )) {
        return FALSE;
      }
      return TRUE;
    } elseif ($xmlField['null'] != $databaseField['null']) {
      return TRUE;
    } elseif ($xmlField['autoinc'] == 'yes' && $databaseField['autoinc'] != 'yes') {
      return TRUE;
    } elseif (!(empty($xmlField['default']) && empty($databaseField['default'])) &&
              $xmlField['default'] != $databaseField['default']) {
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
    $result = FALSE;
    if (($xmlKey['unique'] == 'yes' || $xmlKey['name'] == "PRIMARY") !=
        ($databaseKey['unique'] == 'yes' || $databaseKey['name'] == "PRIMARY")) {
      $result = TRUE;
    } elseif (count(array_intersect($xmlKey['fields'], $databaseKey['fields'])) !=
              count($xmlKey['fields'])) {
      $result = TRUE;
    }
    return $result;
  }
}

/**
* DB-Abstractionslayer - result object PostgreSQL
* @package Papaya-Library
* @subpackage Database
*/
class dbresult_pgsql extends dbresult_base {

  /**
  * destructor
  *
  * Free memory, unset self and resultID
  *
  * @access public
  */
  function free() {
    if (isset($this->result) && is_resource($this->result)) {
      pg_free_result($this->result);
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
        $result = pg_fetch_assoc($this->result);
      } else {
        $result = pg_fetch_row($this->result);
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
      return pg_num_rows($this->result);
    }
    return FALSE;
  }

  /**
  * Search index
  *
  * Move record pointer to given index
  * next call of pg_fetch_row() returns wanted value
  *
  * @param $index
  * @access public
  * @return boolean
  */
  function seek($index) {
    if (isset($this->result) && is_resource($this->result)) {
      if (pg_result_seek($this->result, $index)) {
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
  * @return NULL|\PapayaMessageContextInterface
  */
  public function getExplain() {
    $explainQuery = 'EXPLAIN '.$this->query;
    if ($res = $this->connection->executeQuery($explainQuery)) {
      $explain = array();
      while ($row = pg_fetch_row($res)) {
        $explain[] = $row[0];
      }
      if (!empty($explain)) {
        return new \PapayaMessageContextList('Explain', $explain);
      }
    }
    return NULL;
  }
}

