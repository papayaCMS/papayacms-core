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
  * @var resource $_postgresql Connection-ID
  * @access public
  */
  private $_postgresql;

  public function __construct(\Papaya\Database\Source\Name $dsn) {
    parent::__construct(
      $dsn,
      new Papaya\Database\Syntax\PostgreSQLSyntax($this),
      new Papaya\Database\Schema\PostgreSQLSchema($this)
    );
  }

  /**
   * Check that the pgsql extension is available
   *
   * @access public
   * @throws \Papaya\Database\Exception\ConnectionFailed
   * @return boolean
   */
  public function isExtensionAvailable() {
    if (!extension_loaded('pgsql')) {
      throw new \Papaya\Database\Exception\ConnectionFailed(
        'Extension "pgsql" not available.'
      );
    }
    return TRUE;
  }

  /**
   * Establish connection to database
   *
   * @access public
   * @return \Papaya\Database\Connection
   *@throws Exception
   * @throws \Papaya\Database\Exception\ConnectionFailed
   */
  public function connect() {
    if (isset($this->_postgresql) && is_resource($this->_postgresql)) {
      return $this;
    }
    $connectStr = 'host='.$this->getDSN()->host;
    if ($this->getDSN()->port > 0) {
      $connectStr .= ' port='.$this->getDSN()->port;
    }
    $connectStr .= ' user='.$this->getDSN()->username;
    $connectStr .= ' password='.$this->getDSN()->password;
    $connectStr .= ' dbname='.$this->getDSN()->database;
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
        throw new \Papaya\Database\Exception\ConnectionFailed(
          'Can not set client encoding for database connection.'
        );
      }
      $this->_postgresql = $connection;
      return $this;
    }
    throw new \Papaya\Database\Exception\ConnectionFailed('Invalid connection resource.');
  }

  /**
   * @param $code
   * @param $message
   * @throws \Papaya\Database\Exception\ConnectionFailed
   */
  private function handleConnectionError($code, $message) {
    throw new \Papaya\Database\Exception\ConnectionFailed(
      strip_tags(str_replace('&quot;', '"', $message)), $code
    );
  }

  /**
  * close connection
  *
  * @access public
  */
  public function disconnect() {
    if (isset($this->_postgresql) &&
        is_resource($this->_postgresql)) {
      pg_close($this->_postgresql);
    }
  }

  /**
   * @param \Papaya\Database\Statement|string $statement
   * @param int $options
   * @return \dbresult_pgsql|int|\Papaya\Database\Result|void
   * @throws \Papaya\Database\Exception\ConnectionFailed
   * @throws \Papaya\Database\Exception\QueryFailed
   */
  public function execute($statement, $options = 0) {
    if (!Papaya\Utility\Bitwise::inBitmask(self::DISABLE_RESULT_CLEANUP, $options)) {
      $this->cleanup();
    }
    $this->connect();
    if (!$statement instanceof \Papaya\Database\Statement) {
      $statement = new \Papaya\Database\SQLStatement((string)$statement, []);
    }
    $dbmsResult = $this->process($statement);
    if (is_resource($dbmsResult)) {
      return new dbresult_pgsql($this, $dbmsResult, $statement);
    }
  }

  private function process(\Papaya\Database\Statement $statement) {
    $sql = $statement->getSQLString();
    $parameters = $statement->getSQLParameters();
    $dbmsResult = FALSE;
    if (empty($parameters)) {
      $dbmsResult = @pg_query($this->_postgresql, $sql);
    } elseif ($dbmsStatement = @pg_prepare($this->_postgresql, '', $sql)) {
      $dbmsResult = @pg_execute($this->_postgresql, $parameters);
    }
    if (is_resource($dbmsResult)) {
      return $dbmsResult;
    }
    if ($dbmsResult) {
      return pg_affected_rows($this->_postgresql);
    }
    $errorMessage = pg_last_error($this->_postgresql);
    return new \Papaya\Database\Exception\QueryFailed(
      empty($errorMessage) ? 'Unknown PostgreSQL error.' : $errorMessage, 0, NULL, $statement
    );
  }
  /**
  * String ecsaping for PostgreSQL use
  *
  * @param mixed $value Value to escape
  * @access public
  * @return string escaped value.
  */
  public function escapeString($value) {
    $value = parent::escapeString($value);
    return pg_escape_string($this->_postgresql, $value);
  }

  /**
   * Fetch the last inserted id
   *
   * @param string $table
   * @param string $idField
   * @return int|string|null
   * @throws \Papaya\Database\Exception\ConnectionFailed
   * @throws \Papaya\Database\Exception\QueryFailed
   */
  public function lastInsertId($table, $idField) {
    $sql = "SELECT CURRVAL('".$table.'_'.$idField."_seq')";
    if ($result = $this->execute($sql)) {
      return $result->fetchField();
    }
    return NULL;
  }

  /**
  * Insert records
  *
  * @param string $table
  * @param array $values
  * @access public
  * @return boolean
  */
  public function insert($table, array $values) {
    $baseSQL = 'COPY '.$this->quoteIdentifier($table).' ';
    $lastFields = NULL;
    $specialChars = array("\t" => '\t', "\r" => '\r', "\n" => '\n');
    $this->lastSQLQuery = '';
    if (isset($values) && is_array($values) && count($values) > 0) {
      foreach ($values as $data) {
        if (is_array($data) && count($data) > 0) {
          $fields = array();
          $valueData = array();
          foreach ($data as $key => $val) {
            $fields[] = strtr($this->quoteIdentifier($key), $specialChars);
            if ($val === '') {
              $valueData[] = '';
            } else {
              $valueData[] = strtr($this->escapeString($val), $specialChars);
            }
          }
          if (!isset($lastFields)) {
            $sql = $baseSQL.'('.implode(',', $fields).
              ") FROM STDIN USING DELIMITERS '\t' WITH NULL AS '\\NULL' \n";
            if (!$this->process($sql)) {
              return FALSE;
            }
            $lastFields = $fields;
          } elseif (count(array_diff($fields, $lastFields)) > 0) {
            if (!pg_end_copy($this->_postgresql)) {
              return FALSE;
            }
            $sql = $baseSQL."(".implode(",", $fields).
              ") FROM STDIN USING DELIMITERS '\t' WITH NULL AS '\\NULL' \n";
            if (!$this->process($sql)) {
              return FALSE;
            }
            $lastFields = $fields;
          }
          $line = implode("\t", $valueData).LF;
          if (!pg_put_line($this->_postgresql, $line)) {
            pg_end_copy($this->_postgresql);
            return FALSE;
          }
        }
      }
      if (!pg_end_copy($this->_postgresql)) {
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
  private function updateAutoIncrementFields($table) {
    $structure = $this->schema()->describeTable($table);
    $fields = $structure['fields'];
    if (isset($fields) && is_array($fields)) {
      foreach ($fields as $field) {
        if ($field['autoinc'] === 'yes') {
          $tableName = $this->escapeString($table);
          $fieldName = $this->escapeString($field['name']);
          $sql =
            "SELECT SETVAL('".$tableName.'_'.$fieldName."_seq',".
            '(SELECT MAX('.$fieldName.') FROM '.$tableName.'));';
          $this->process($sql);
        }
      }
    }
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
        $this->_recordNumber = $index;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Compile database explain for SELECT query
  *
  * @return NULL|\Papaya\Message\Context\Data
  */
  public function getExplain() {
    $explainQuery = 'EXPLAIN '.$this->query;
    $result = $this->connection->execute(
      $explainQuery, \Papaya\Database\Connection::DISABLE_RESULT_CLEANUP
    );
    if ($result) {
      $explain = array();
      while ($row = $result->fetchRow(self::FETCH_ORDERED)) {
        $explain[] = $row[0];
      }
      if (!empty($explain)) {
        return new \Papaya\Message\Context\Items('Explain', $explain);
      }
    }
    return NULL;
  }
}

