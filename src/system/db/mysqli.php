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

use Papaya\Database\Statement as DatabaseStatement;
use Papaya\Database\SQLStatement;

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
   * @return \Papaya\Database\Connection
   *@throws \Papaya\Database\Exception\ConnectionFailed
   */
  public function connect() {
    if (isset($this->_mysqli) && is_object($this->_mysqli)) {
      return $this;
    }
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
          new SQLStatement("SET NAMES 'utf8' COLLATE ?'", [PAPAYA_DATABASE_COLLATION])
        );
      } else {
        $this->_mysqli->set_charset('utf8');
      }
      return $this;
    }
    throw new \Papaya\Database\Exception\ConnectionFailed(mysqli_connect_error(), mysqli_connect_errno());
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
   * @param DatabaseStatement|string $statement
   * @param int $options
   * @return \Papaya\Database\Result|int
   * @throws \Papaya\Database\Exception\ConnectionFailed
   * @throws \Papaya\Database\Exception\QueryFailed
   */
  public function execute($statement, $options = 0) {
    if (!Papaya\Utility\Bitwise::inBitmask(self::DISABLE_RESULT_CLEANUP, $options)) {
      $this->cleanup();
    }
    $this->connect();
    if (!$statement instanceof DatabaseStatement) {
      $statement = new SQLStatement((string)$statement, []);
    }
    $calculateFoundRows = \Papaya\Utility\Bitwise::inBitmask(self::REQUIRE_ABSOLUTE_COUNT, $options);
    if (
      $calculateFoundRows &&
      ($rewrite = $this->rewriteStatementToCalculateFoundRows($statement))
    ) {
      $statement = $rewrite;
    } else {
      $calculateFoundRows = FALSE;
    }
    $dbmsResult = $this->process($statement);
    if ($dbmsResult instanceof mysqli_result) {
      $this->lastResult = $result = new dbresult_mysqli($this, $dbmsResult, $statement);
      if ($calculateFoundRows) {
        $counterResult = $this->process(new SQLStatement('SELECT FOUND_ROWS()'));
        if ($counterResult) {
          $result->setAbsCount((int)$counterResult->fetch_field());
          $counterResult->free();
        }
      }
      return $result;
    }
    return $dbmsResult;
  }

  private function rewriteStatementToCalculateFoundRows(
    DatabaseStatement $statement
  ) {
    $sql = $statement->getSQLString();
    if (strpos(trim($sql), 'SELECT') === 0) {
      return new SQLStatement(
        'SELECT SQL_CALC_FOUND_ROWS '.substr(trim($sql), 6),
        $statement->getSQLParameters()
      );
    }
    return NULL;
  }

  /**
   * @param DatabaseStatement $statement
   * @return FALSE|int|\mysqli_result
   * @throws \Papaya\Database\Exception\QueryFailed
   */
  private function process(DatabaseStatement $statement) {
    $sql = $statement->getSQLString();
    $parameters = $statement->getSQLParameters();
    if (empty($parameters)) {
      if ($dbmsResult = @$this->_mysqli->query($sql)) {
        if ($dbmsResult instanceof mysqli_result) {
          return $dbmsResult;
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
          return $dbmsResult;
        }
        return $dbmsStatement->affected_rows;
      }
    }
    throw $this->_createQueryException($statement);
  }

  /**
   * If a query fails, trow an database exception
   *
   * @param DatabaseStatement $sql
   * @return \Papaya\Database\Exception\QueryFailed
   */
  private function _createQueryException(DatabaseStatement $sql) {
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
  * Fetch the last inserted id
  *
  * @param string $table
  * @param string $idField
  * @return string|int|null
  */
  public function lastInsertId($table, $idField) {
    if ($result = $this->execute('SELECT LAST_INSERT_ID()', self::DISABLE_RESULT_CLEANUP)) {
      return $result->fetchField();
    }
    return NULL;
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
      $explainQuery, \Papaya\Database\Connection::KEEP_LAST_QUERY
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
