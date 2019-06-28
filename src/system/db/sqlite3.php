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

use Papaya\Database\Statement;

/**
 * basic database connection and result class
 */
require_once __DIR__.'/base.php';

/**
 * DB-abstraction layer - SQLite
 *
 * @package Papaya-Library
 * @subpackage Database
 */
class dbcon_sqlite3 extends dbcon_base {

  /**
   * @var SQLite3|NULL
   */
  private $_sqlite3;

  public function __construct(\Papaya\Database\Source\Name $dsn) {
    parent::__construct(
      $dsn,
      new Papaya\Database\Syntax\SQLiteSyntax($this),
      new Papaya\Database\Schema\SQLiteSchema($this)
    );
  }

  /**
   * Check for sqlite database extension found
   *
   * @throws \Papaya\Database\Exception\ConnectionFailed
   * @return boolean
   */
  public function isExtensionAvailable() {
    if (!extension_loaded('sqlite3')){
      throw new \Papaya\Database\Exception\ConnectionFailed(
        'Extension "sqlite" not available.'
      );
    }
    return TRUE;
  }

  /**
   * Establish connection to database
   *
   * @return \Papaya\Database\Connection
   *@throws \Papaya\Database\Exception\ConnectionFailed
   */
  public function connect() {
    if (isset($this->_sqlite3) && ($this->_sqlite3 instanceof SQLite3)) {
      return $this;
    }
    try {
      $fileName = $this->getDSN()->filename;
      if (0 === strpos($fileName, '.')) {
        $fileName = \Papaya\Utility\File\Path::cleanup(
          \Papaya\Utility\File\Path::getDocumentRoot().'../'.$fileName, FALSE
        );
      }
      $this->_sqlite3 = new \SQLite3($fileName);
      $this->_sqlite3->enableExceptions(TRUE);
      $this->_sqlite3->busyTimeout(10000);
      $this->_sqlite3->exec('PRAGMA journal_mode = WAL');
      return $this;
    } catch (\Exception $e) {
      throw new \Papaya\Database\Exception\ConnectionFailed($e->getMessage());
    }
  }

  /**
   * @param \Papaya\Database\Statement|string $statement
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
    if (!$statement instanceof Statement) {
      $statement = new \Papaya\Database\SQLStatement((string)$statement, []);
    }
    $dbmsResult = $this->process($statement);
    if ($dbmsResult instanceof SQLite3Result) {
      return new dbresult_sqlite3($this, $dbmsResult, $statement);
    }
    return $dbmsResult;
  }

  /**
   * @param \Papaya\Database\Statement|string $statement
   * @return bool|int|\SQLite3Result
   * @throws \Papaya\Database\Exception\QueryFailed
   */
  private function process($statement) {
    if ($statement instanceof Statement) {
      $sql = $statement->getSQLString();
      $parameters = $statement->getSQLParameters();
    } else {
      $sql = (string)$statement;
      $parameters = [];
    }
    $dbmsResult = FALSE;
    try {
      if (empty($parameters)) {
        $dbmsResult = @$this->_sqlite3->query($sql);
      } elseif ($dbmsStatement = @$this->_sqlite3->prepare($sql)) {
        foreach ($parameters as $position => $value) {
          $dbmsStatement->bindValue($position + 1, $value, SQLITE3_TEXT);
        }
        $dbmsResult = @$dbmsStatement->execute();
      }
    } catch (Exception $e) {
      throw $this->_createQueryException($statement);
    }
    if ($dbmsResult instanceof SQLite3Result) {
      return $dbmsResult;
    }
    if ($dbmsResult) {
      return $this->_sqlite3->changes();
    }
    throw $this->_createQueryException($statement);
  }

  /**
   * close connection
   */
  public function disconnect() {
    if (
      isset($this->_sqlite3) &&
      ($this->_sqlite3 instanceof SQLite3)
    ) {
      $this->_sqlite3->close();
      $this->_sqlite3 = NULL;
    }
  }

  /**
   * If a query fails, throw an database exception
   *
   * @param \Papaya\Database\Statement $statement
   * @return \Papaya\Database\Exception\QueryFailed
   */
  private function _createQueryException(Statement $statement) {
    $errorCode = $this->_sqlite3->lastErrorCode();
    $errorMessage = $this->_sqlite3->lastErrorMsg();
    $severityMapping = array(
      // 5 - The database file is locked
      5 => \Papaya\Database\Exception::SEVERITY_WARNING,
      // 6 - A table in the database is locked
      6 => \Papaya\Database\Exception::SEVERITY_WARNING,
      // 20 - Data type mismatch
      20 => \Papaya\Database\Exception::SEVERITY_WARNING,
      // 100 - sqlite_step() has another row ready
      100 => \Papaya\Database\Exception::SEVERITY_INFO,
      // 101 - sqlite_step() has finished executing
      101 => \Papaya\Database\Exception::SEVERITY_INFO,
    );
    if (isset($severityMapping[$errorCode])) {
      $severity = $severityMapping[$errorCode];
    } else {
      $severity = \Papaya\Database\Exception::SEVERITY_ERROR;
    }
    return new \Papaya\Database\Exception\QueryFailed(
      $errorMessage, $errorCode, $severity, $statement
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
    $this->connect();
    $value = parent::escapeString($value);
    return $this->_sqlite3->escapeString($value);
  }

  /**
   * Fetch the last inserted id
   *
   * @param string $table
   * @param string $idField
   * @return int|string|null
   */
  public function lastInsertId($table, $idField) {
    return $this->_sqlite3->lastInsertRowID();
  }

  /**
   * @param string $name
   * @param callable $function
   */
  public function registerFunction($name, callable $function) {
    $this->_sqlite3->createFunction($name, $function);
  }
}

/**
 * DB-Abstractionslayer - result object - SQLite
 * @package Papaya-Library
 * @subpackage Database
 */
class dbresult_sqlite3 extends dbresult_base {

  /**
   * @var SQLite3Result
   */
  protected $result;

  /**
   * destructor
   *
   * Free memory, unset self and resultID
   *
   * @access public
   */
  function free() {
    if ($this->isValid()) {
      try {
        $this->result->finalize();
      } catch (Exception $e) {
      }
      $this->result = NULL;
    } else {
      $this->result = NULL;
    }
  }

  function isValid() {
    return isset($this->result) && ($this->result instanceof SQLite3Result);
  }

  /**
   * Fetch next row of result
   *
   * @param integer $mode line return modus
   * @access public
   * @return mixed FALSE or next line
   */
  function fetchRow($mode = DB_FETCHMODE_DEFAULT) {
    if ($this->isValid()) {
      if ($mode == DB_FETCHMODE_ASSOC) {
        /** @noinspection PhpParamsInspection */
        $result = $this->result->fetchArray(SQLITE3_ASSOC);
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
        $result = $this->result->fetchArray(SQLITE3_NUM);
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
    if ($this->isValid()) {
      return $this->result->numColumns();
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
    if ($this->isValid()) {
      if ($index < $this->_recordNumber) {
        $this->result->reset();
        $this->_recordNumber = 0;
      }
      while ($this->_recordNumber < $index) {
        $this->result->fetchArray(SQLITE3_NUM);
        ++$this->_recordNumber;
      }
      return ($this->_recordNumber == $index);
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
    if ($result && count($result) > 0) {
      $explain = new \Papaya\Message\Context\Table('Explain');
      while ($row = $result->fetchRow(self::FETCH_ORDERED)) {
        $explain->addRow($row);
      }
      return $explain;
    }
    return NULL;
  }
}
