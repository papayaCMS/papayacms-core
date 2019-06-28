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

namespace Papaya\Database\Connection {

  use mysqli_result;
  use Papaya\Database\Connection as DatabaseConnection;
  use Papaya\Database\Exception as DatabaseException;
  use Papaya\Database\Exception\ConnectionFailed;
  use Papaya\Database\Exception\QueryFailed;
  use Papaya\Database\Result as DatabaseResult;
  use Papaya\Database\Schema\MySQLSchema;
  use Papaya\Database\Statement as DatabaseStatement;
  use Papaya\Database\SQLStatement;
  use Papaya\Database\Source\Name as DataSourceName;
  use Papaya\Database\Syntax\MySQLSyntax;
  use Papaya\Utility\Bitwise;

  /**
   * DB-abstraction layer - connection object MySQL Improved
   *
   * @package Papaya-Library
   * @subpackage Database
   */
  class MySQLiConnection extends AbstractConnection {

    /**
     * @var \mysqli $_mysqli Connection-ID
     */
    private $_mysqli;

    public function __construct(DataSourceName $dsn) {
      parent::__construct(
        $dsn,
        new MySQLSyntax($this),
        new MySQLSchema($this)
      );
    }

    /**
     * Check that the mysqli extension is available
     *
     * @access public
     * @throws ConnectionFailed
     * @return boolean
     */
    public function isExtensionAvailable() {
      if (!extension_loaded('mysqli')) {
        throw new ConnectionFailed(
          'Extension "mysqli" not available.'
        );
      }
      return TRUE;
    }

    /**
     * Establish connection to database
     *
     * @access public
     * @return DatabaseConnection
     * @throws ConnectionFailed
     * @throws QueryFailed
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
      throw new ConnectionFailed(mysqli_connect_error(), mysqli_connect_errno());
    }

    /**
     * close connection
     *
     * @access public
     */
    public function disconnect() {
      if (
        isset($this->_mysqli) &&
        is_object($this->_mysqli)
      ) {
        $this->_mysqli->close();
        $this->_mysqli = NULL;
      }
    }

    /**
     * @param DatabaseStatement|string $statement
     * @param int $options
     * @return DatabaseResult|int
     * @throws ConnectionFailed
     * @throws QueryFailed
     */
    public function execute($statement, $options = 0) {
      if (!Bitwise::inBitmask(self::DISABLE_RESULT_CLEANUP, $options)) {
        $this->cleanup();
      }
      $this->connect();
      if (!$statement instanceof DatabaseStatement) {
        $statement = new SQLStatement((string)$statement, []);
      }
      $calculateFoundRows = Bitwise::inBitmask(self::REQUIRE_ABSOLUTE_COUNT, $options);
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
        $result = new MySQLiResult($this, $statement, $dbmsResult);
        if (!Bitwise::inBitmask(self::DISABLE_RESULT_CLEANUP, $options)) {
          $this->buffer($result);
        }
        if ($calculateFoundRows) {
          $counterResult = $this->process(new SQLStatement('SELECT FOUND_ROWS()'));
          if ($counterResult) {
            $result->setAbsoluteCount((int)$counterResult->fetch_field());
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
     * @param DatabaseStatement|String $statement
     * @return FALSE|int|\mysqli_result
     * @throws QueryFailed
     */
    private function process($statement) {
      if ($statement instanceof DatabaseStatement) {
        $sql = $statement->getSQLString(FALSE);
        $parameters = $statement->getSQLParameters(FALSE);
      } else {
        $sql = (string)$statement;
        $parameters = [];
      }
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
     * @return QueryFailed
     */
    private function _createQueryException(DatabaseStatement $sql) {
      $errorCode = $this->_mysqli->errno;
      $errorMessage = $this->_mysqli->error;
      $severityMapping = [
        // 1062 - duplicate entry
        1062 => DatabaseException::SEVERITY_WARNING,
        // 1205 - lock error
        1205 => DatabaseException::SEVERITY_INFO,
        // 1213 - deadlock error
        1213 => DatabaseException::SEVERITY_INFO,
      ];
      if (isset($severityMapping[$errorCode])) {
        $severity = $severityMapping[$errorCode];
      } else {
        $severity = DatabaseException::SEVERITY_ERROR;
      }
      return new QueryFailed(
        $errorMessage, $errorCode, $severity, (string)$sql
      );
    }

    /**
     * String escaping for MySQL use
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
     * @param string $quoteChar
     * @return string
     */
    public function quoteIdentifier($name, $quoteChar = '`') {
      return parent::quoteIdentifier($name, $quoteChar);
    }

    /**
     * Fetch the last inserted id
     *
     * @param string $table
     * @param string $idField
     * @return string|int|null
     * @throws ConnectionFailed
     * @throws QueryFailed
     */
    public function lastInsertId($table, $idField) {
      if ($result = $this->execute('SELECT LAST_INSERT_ID()', self::DISABLE_RESULT_CLEANUP)) {
        return $result->fetchField();
      }
      return NULL;
    }
  }
}
