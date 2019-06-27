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
* DB-abstraction layer - connection object MySQL Improved
*
* @package Papaya-Library
* @subpackage Database
*/
class dbcon_mysqli extends dbcon_base {

  /**
  * @var \mysqli $_mysqli Connection-ID
  */
  private $_mysqli;

  public function __construct(\Papaya\Database\Source\Name $dsn) {
    parent::__construct(
      $dsn,
      new Papaya\Database\Syntax\MySQLSyntax($this),
      new Papaya\Database\Schema\MySQLSchema($this)
    );
  }

  /**
   * Check that the mysqli extension is available
   *
   * @access public
   * @throws \Papaya\Database\Exception\ConnectionFailed
   * @return boolean
   */
  function isExtensionAvailable() {
    if (!extension_loaded('mysqli')) {
      throw new \Papaya\Database\Exception\ConnectionFailed(
        'Extension "mysqli" not available.'
      );
    }
    return TRUE;
  }

  /**
   * Establish connection to database
   *
   * @access public
   * @throws \Papaya\Database\Exception\ConnectionFailed
   * @return boolean
   */
  function connect() {
    if (isset($this->_mysqli) && is_object($this->_mysqli)) {
      return TRUE;
    } else {
      if (isset($this->getDSN()->socket)) {
        $server = 'localhost';
        $port = NULL;
        $socket = $this->getDSN()->socket;
      } elseif ($this->getDSN()->port > 0) {
        $server = $this->getDSN()->host;
        $port = $this->getDSN()->port;
        $socket = NULL;
      } else {
        $server = $this->getDSN()->host;
        $port = NULL;
        $socket = NULL;
      }
      $connection = @mysqli_connect(
        $server,
        $this->getDSN()->username,
        $this->getDSN()->password,
        $this->getDSN()->database,
        $port,
        $socket
      );
      if ($connection) {
        $this->_mysqli = $connection;
        if (defined('PAPAYA_DATABASE_COLLATION')) {
          $this->process(
            new \Papaya\Database\SQLStatement("SET NAMES 'utf8' COLLATE ?'", [PAPAYA_DATABASE_COLLATION])
          );
        } else {
          $this->_mysqli->set_charset('utf8');
        }
        return TRUE;
      }
      throw new \Papaya\Database\Exception\ConnectionFailed(mysqli_connect_error(), mysqli_connect_errno());
    }
  }

  /**
  * close connection
  *
  * @access public
  */
  public function disconnect() {
    if (isset($this->_mysqli) &&
        is_object($this->_mysqli)) {
      $this->_mysqli->close();
      $this->_mysqli = NULL;
    }
  }

  /**
   * @param \Papaya\Database\Interfaces\Statement|string $statement
   * @param int $options
   * @return \Papaya\Database\Result|int
   * @throws \Papaya\Database\Exception\ConnectionFailed
   * @throws \Papaya\Database\Exception\QueryFailed
   */
  public function execute($statement, $options = 0) {
    if (!Papaya\Utility\Bitwise::inBitmask(self::KEEP_PREVIOUS_RESULT, $options)) {
      $this->cleanup();
    }
    $this->connect();
    if (!$statement instanceof \Papaya\Database\Interfaces\Statement) {
      $statement = new \Papaya\Database\SQLStatement((string)$statement, []);
    }
    $dbmsResult = $this->process($statement);
    if ($dbmsResult instanceof mysqli_result) {
      return new dbresult_mysqli($this, $dbmsResult, $statement);
    }
    return $dbmsResult;
  }

  private function process(\Papaya\Database\Interfaces\Statement $statement) {
    $sql = $statement->getSQLString();
    $parameters = $statement->getSQLParameters();
    $dbmsResult = FALSE;
    if (empty($parameters)) {
      if ($dbmsResult = @$this->_mysqli->query($sql)) {
        if ($dbmsResult instanceof mysqli_result) {
          $dbmsResult;
        }
        if ($dbmsResult) {
          return $this->_mysqli->affected_rows;
        }
      }
    } elseif ($dbmsStatement = @$this->_mysqli->prepare($sql)) {
      $dbmsStatement->bind_param(
        str_repeat('s', count($parameters)),
        ...$parameters
      );
      if (@$dbmsStatement->execute()) {
        if ($dbmsResult = $dbmsStatement->get_result()) {
          $dbmsResult;
        }
        return $dbmsStatement->affected_rows;
      }
    }
    throw $this->_createQueryException($statement);
  }

  /**
   * If a query failes, trow an database exception
   *
   * @param \Papaya\Database\Interfaces\Statement $sql
   * @return \Papaya\Database\Exception\QueryFailed
   */
  private function _createQueryException(\Papaya\Database\Interfaces\Statement $sql) {
    $errorCode = $this->_mysqli->errno;
    $errorMessage = $this->_mysqli->error;
    $severityMapping = array(
      // 1062 - duplicate entry
      1062 => \Papaya\Database\Exception::SEVERITY_WARNING,
      // 1205 - lock error
      1205 => \Papaya\Database\Exception::SEVERITY_INFO,
      // 1213 - deadlock error
      1213 => \Papaya\Database\Exception::SEVERITY_INFO,
    );
    if (isset($severityMapping[$errorCode])) {
      $severity = $severityMapping[$errorCode];
    } else {
      $severity = \Papaya\Database\Exception::SEVERITY_ERROR;
    }
    return new \Papaya\Database\Exception\QueryFailed(
      $errorMessage, $errorCode, $severity, (string)$sql
    );
  }

  /**
  * String ecsaping for MySQL use
  *
  * @param mixed $value Value to escape
  * @access public
  * @return string escaped value.
  */
  public function escapeString($value) {
    $value = parent::escapeString($value);
    return $this->_mysqli->escape_string($value);
  }

  /**
   * @param string $name
   * @return string
   */
  public function quoteIdentifier($name) {
    return '`'.substr(parent::quoteIdentifier($name), 1, -1).'`';
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
   * @return FALSE|int|\Papaya\Database\Result FALSE or number of affected_rows or database result object
   * @throws \Papaya\Database\Exception\ConnectionFailed
   * @throws \Papaya\Database\Exception\QueryFailed
   * @access public
   */
  function query(
    $sql, $max = NULL, $offset = NULL, $freeLastResult = TRUE, $enableCounter = FALSE
  ) {
    parent::query($sql, $max, $offset, $freeLastResult, $enableCounter);
    $options = 0;
    if (!$freeLastResult) {
      $options |= self::KEEP_PREVIOUS_RESULT;
    }
    if ($enableCounter) {
      $options |= self::REQUIRE_ABSOLUTE_COUNT;
    }
    $limitSQL = '';
    $queryRowCount = FALSE;
    if (isset($max) && $max > 0 && strpos(trim($sql), 'SELECT') === 0) {
      if ($enableCounter) {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS '.substr(trim($sql), 6);
        $queryRowCount = TRUE;
      }
      $limitSQL .= $this->syntax()->limit($max, $offset);
    }
    $this->lastSQLQuery = $sql.$limitSQL;
    $result = $this->execute($sql.$limitSQL, $options);
    if ($result instanceof \Papaya\Database\Result) {
      $this->lastResult = $result;
      $this->lastResult->setLimit($max, $offset);
      if ($queryRowCount) {
        $resCount = $this->execute('SELECT FOUND_ROWS()', self::KEEP_PREVIOUS_RESULT);
        if ($resCount) {
          $this->lastResult->setAbsCount((int)$resCount->fetchField());
          $resCount->free();
        }
      }
    }
    return $result;
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
      $sql = 'INSERT INTO '.$this->escapeString($table).' ('.$fieldString.') VALUES ('.
        $valueString.')';
      if ($this->query($sql, NULL, NULL, FALSE)) {
        if (isset($idField)) {
          return $this->lastInsertId($table, $idField);
        } else {
          return $this->_mysqli->affected_rows;
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
    if ($result = $this->execute('SELECT LAST_INSERT_ID()', self::KEEP_PREVIOUS_RESULT)) {
      return $result->fetchField();
    }
    return NULL;
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
  * @param string $filter Filter string without WHERE condition
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
        return $this->query($sql, NULL, NULL, FALSE);
      } else {
        $this->lastSQLQuery = 'NO VALID DATA';
      }
    } else {
      $this->lastSQLQuery = 'NO DATA';
    }
    return FALSE;
  }
}

/**
* database result for mysqli
*
* @package Papaya-Library
* @subpackage Database
*/
class dbresult_mysqli extends dbresult_base {

  /**
  * destructor
  *
  * Free memory, unset self and resultID
  *
  * @access public
  */
  function free() {
    if (isset($this->result) && is_object($this->result)) {
      $this->result->free();
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
    if (isset($this->result) && is_object($this->result)) {
      if ($mode == DB_FETCHMODE_ASSOC) {
        $result = $this->result->fetch_assoc();
      } else {
        $result = $this->result->fetch_row();
      }
      if (isset($result) && is_array($result)) {
        $this->_recordNumber++;
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
    if (isset($this->result) && is_object($this->result)) {
      return $this->result->num_rows;
    }
    return FALSE;
  }

  /**
  * Search index
  *
  * Move record pointer to given index
  * next call of mysqli->fetch_row() returns wanted value
  *
  * @param integer $index
  * @access public
  * @return boolean
  */
  function seek($index) {
    if (isset($this->result) && is_object($this->result)) {
      if ($this->result->data_seek($index)) {
        $this->_recordNumber = $index;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Compile database explain for SELECT query
  *
  * @access public
  * @return NULL|\Papaya\Message\Context\Data
  */
  public function getExplain() {
    $explainQuery = 'EXPLAIN '.$this->query;
    $dbmsResult = $res = $this->connection->execute(
      $explainQuery, \Papaya\Database\Interfaces\Connection::KEEP_LAST_QUERY
    );
    if ($dbmsResult && count($dbmsResult) > 0) {
      $explain = new \Papaya\Message\Context\Table('Explain');
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
      while ($row = $res->fetchAssoc()) {
        $explain->addRow($row);
      }
      return $explain;
    }
    return NULL;
  }
}
