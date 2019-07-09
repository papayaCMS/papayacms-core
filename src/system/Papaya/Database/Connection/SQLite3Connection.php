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

namespace Papaya\Database\Connection;

use Exception;
use Papaya\Database\Exception as DatabaseException;
use Papaya\Database\Exception\ConnectionFailed;
use Papaya\Database\Exception\QueryFailed;
use Papaya\Database\Result as DatabaseResult;
use Papaya\Database\Schema\SQLiteSchema;
use Papaya\Database\Source\Name as DataSourceName;
use Papaya\Database\SQLStatement;
use Papaya\Database\Statement;
use Papaya\Database\Syntax\SQLiteSyntax;
use Papaya\Utility\Bitwise;
use Papaya\Utility\File\Path as FilePath;
use SQLite3 as SQLite3Extension;
use SQLite3Result as SQLiteExtensionResult;

/**
 * @package Papaya-Library
 * @subpackage Database
 */
class SQLite3Connection extends AbstractConnection {

  /**
   * @var SQLite3Extension|NULL
   */
  private $_sqlite3;

  public function __construct(DataSourceName $dsn) {
    parent::__construct(
      $dsn, new SQLiteSyntax($this), new SQLiteSchema($this)
    );
  }

  /**
   * @return boolean
   * @throws ConnectionFailed
   */
  public function isExtensionAvailable() {
    if (!extension_loaded('sqlite3')) {
      throw new ConnectionFailed(
        'Extension "sqlite" not available.'
      );
    }
    return TRUE;
  }

  /**
   * @return self
   * @throws ConnectionFailed
   */
  public function connect() {
    if ($this->_sqlite3 instanceof SQLite3Extension) {
      return $this;
    }
    try {
      $fileName = $this->getDSN()->filename;
      if (0 === strpos($fileName, '.')) {
        $fileName = FilePath::cleanup(
          FilePath::getDocumentRoot().'../'.$fileName, FALSE
        );
      }
      $this->_sqlite3 = new SQLite3Extension($fileName);
      $this->_sqlite3->enableExceptions(TRUE);
      $this->_sqlite3->busyTimeout(10000);
      $this->_sqlite3->exec('PRAGMA journal_mode = WAL');
      return $this;
    } catch (\Exception $e) {
      throw new ConnectionFailed($e->getMessage());
    }
  }

  /**
   * @param Statement|string $statement
   * @param int $options
   * @return DatabaseResult|int
   * @throws QueryFailed
   */
  public function execute($statement, $options = 0) {
    if (!Bitwise::inBitmask(self::DISABLE_RESULT_CLEANUP, $options)) {
      $this->cleanup();
    }
    if (!$statement instanceof Statement) {
      $statement = new SQLStatement((string)$statement, []);
    }
    $dbmsResult = $this->process($statement);
    if ($dbmsResult instanceof SQLiteExtensionResult) {
      $result = new SQLite3Result($this, $statement, $dbmsResult);
      if (!Bitwise::inBitmask(self::DISABLE_RESULT_CLEANUP, $options)) {
        $this->buffer($result);
      }
      return $result;
    }
    return $dbmsResult;
  }

  /**
   * @param Statement|string $statement
   * @return bool|int|SQLiteExtensionResult
   * @throws QueryFailed
   */
  private function process($statement) {
    if ($statement instanceof Statement) {
      $sql = $statement->getSQLString(FALSE);
      $parameters = $statement->getSQLParameters(FALSE);
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
    if ($dbmsResult instanceof SQLiteExtensionResult) {
      return $dbmsResult;
    }
    if ($dbmsResult) {
      return $this->_sqlite3->changes();
    }
    throw $this->_createQueryException($statement);
  }

  public function disconnect() {
    if (
      isset($this->_sqlite3) &&
      ($this->_sqlite3 instanceof SQLite3Extension)
    ) {
      $this->_sqlite3->close();
      $this->_sqlite3 = NULL;
    }
  }

  /**
   * If a query fails, throw an database exception
   *
   * @param Statement $statement
   * @return QueryFailed
   */
  private function _createQueryException(Statement $statement) {
    $errorCode = $this->_sqlite3->lastErrorCode();
    $errorMessage = $this->_sqlite3->lastErrorMsg();
    $severityMapping = [
      // 5 - The database file is locked
      5 => DatabaseException::SEVERITY_WARNING,
      // 6 - A table in the database is locked
      6 => DatabaseException::SEVERITY_WARNING,
      // 20 - Data type mismatch
      20 => DatabaseException::SEVERITY_WARNING,
      // 100 - sqlite_step() has another row ready
      100 => DatabaseException::SEVERITY_INFO,
      // 101 - sqlite_step() has finished executing
      101 => DatabaseException::SEVERITY_INFO,
    ];
    if (isset($severityMapping[$errorCode])) {
      $severity = $severityMapping[$errorCode];
    } else {
      $severity = DatabaseException::SEVERITY_ERROR;
    }
    return new QueryFailed(
      $errorMessage, $errorCode, $severity, $statement
    );
  }

  /**
   * String escaping for SQLite use
   *
   * @param mixed $value Value to escape
   * @access public
   * @return string escaped value.
   */
  public function escapeString($value) {
    $value = parent::escapeString($value);
    return SQLite3Extension::escapeString(str_replace("\x00", '', $value));
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
