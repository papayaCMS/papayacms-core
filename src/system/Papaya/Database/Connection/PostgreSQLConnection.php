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

  use Exception;
  use Papaya\Database\Connection as DatabaseConnection;
  use Papaya\Database\Exception\ConnectionFailed;
  use Papaya\Database\Exception\QueryFailed;
  use Papaya\Database\Schema\PostgreSQLSchema;
  use Papaya\Database\Statement as DatabaseStatement;
  use Papaya\Database\SQLStatement;
  use Papaya\Database\Source\Name as DataSourceName;
  use Papaya\Database\Syntax\PostgreSQLSyntax;
  use Papaya\Utility\Bitwise;

  /**
   * @package Papaya-Library
   * @subpackage Database
   */
  class PostgreSQLConnection extends AbstractConnection {

    /**
     * @var resource $_postgresql Connection-ID
     * @access public
     */
    private $_postgresql;

    public function __construct(DataSourceName $dsn) {
      parent::__construct(
        $dsn,
        new PostgreSQLSyntax($this),
        new PostgreSQLSchema($this)
      );
    }

    /**
     * @throws ConnectionFailed
     * @return boolean
     */
    public function isExtensionAvailable() {
      if (!extension_loaded('pgsql')) {
        throw new ConnectionFailed(
          'Extension "pgsql" not available.'
        );
      }
      return TRUE;
    }

    /**
     * @return self
     * @throws Exception
     * @throws ConnectionFailed
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
          [$this, 'handleConnectionError'], E_ALL & ~E_STRICT
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
          throw new ConnectionFailed(
            'Can not set client encoding for database connection.'
          );
        }
        $this->_postgresql = $connection;
        return $this;
      }
      throw new ConnectionFailed('Invalid connection resource.');
    }

    /**
     * @param int $code
     * @param string $message
     * @throws ConnectionFailed
     */
    private function handleConnectionError($code, $message) {
      throw new ConnectionFailed(
        strip_tags(str_replace('&quot;', '"', $message)), $code
      );
    }

    public function disconnect() {
      if (
        isset($this->_postgresql) &&
        is_resource($this->_postgresql)
      ) {
        pg_close($this->_postgresql);
      }
    }

    /**
     * @param DatabaseStatement|string $statement
     * @param int $options
     * @return PostgreSQLResult|int
     * @throws QueryFailed
     */
    public function execute($statement, $options = 0) {
      if (!Bitwise::inBitmask(self::DISABLE_RESULT_CLEANUP, $options)) {
        $this->cleanup();
      }
      if (!$statement instanceof DatabaseStatement) {
        $statement = new SQLStatement((string)$statement, []);
      }
      $dbmsResult = $this->process($statement);
      if (is_resource($dbmsResult)) {
        $result = new PostgreSQLResult($this, $statement, $dbmsResult);
        if (!Bitwise::inBitmask(self::DISABLE_RESULT_CLEANUP, $options)) {
          $this->buffer($result);
        }
        return $result;
      }
      return $dbmsResult;
    }

    /**
     * @param DatabaseStatement|string $statement
     * @return bool|int|resource
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
      $dbmsResult = FALSE;
      if (empty($parameters)) {
        $dbmsResult = @pg_query($this->_postgresql, $sql);
      } elseif ($dbmsStatement = @pg_prepare($this->_postgresql, '', $this->rewritePlaceholders($sql))) {
        $dbmsResult = @pg_execute($this->_postgresql, $parameters);
      }
      if (is_resource($dbmsResult)) {
        return $dbmsResult;
      }
      if ($dbmsResult) {
        return pg_affected_rows($this->_postgresql);
      }
      $errorMessage = pg_last_error($this->_postgresql);
      throw new QueryFailed(
        empty($errorMessage) ? 'Unknown PostgreSQL error.' : $errorMessage, 0, NULL, $statement
      );
    }

    /**
     * Replace standard positional placeholders (?) outside literals/identifiers with the ones used by the
     * PostgreSQL extension ($n).
     *
     * @param string $sql
     * @return string
     */
    private function rewritePlaceholders($sql) {
      $quoteCharacters = ["'", '"'];
      $patterns = [];
      foreach ($quoteCharacters as $quoteCharacter) {
        $patterns[] = \sprintf('(?:%1$s(?:[^%1$s]|\\\\%1$s|%1$s{2})*%1$s)', $quoteCharacter);
      }
      $pattern = '(('.\implode('|', $patterns).'))';
      $parts = \preg_split($pattern, $sql, -1, PREG_SPLIT_DELIM_CAPTURE);
      $result = '';
      foreach ($parts as $part) {
        if (\in_array(\substr($part, 0, 1), $quoteCharacters, TRUE)) {
          $result .= $part;
          continue;
        }
        $result .= \preg_replace_callback(
          '(\\?)',
          static function() {
            static $index = 1;
            return '\$'.($index++);
          },
          $part
        );
      }
      return $result;
    }

    /**
     * @param mixed $value Value to escape
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
     * @throws ConnectionFailed
     * @throws QueryFailed
     */
    public function lastInsertId($table, $idField) {
      $sql = "SELECT CURRVAL('".$table.'_'.$idField."_seq')";
      if ($result = $this->execute($sql)) {
        return $result->fetchField();
      }
      return NULL;
    }

    /**
     * @param string $table
     * @param array $values
     * @return boolean
     * @throws QueryFailed
     */
    public function insert($table, array $values) {
      $baseSQL = 'COPY '.$this->quoteIdentifier($table).' ';
      $lastFields = NULL;
      $specialChars = ["\t" => '\t', "\r" => '\r', "\n" => '\n'];
      if (isset($values) && is_array($values) && count($values) > 0) {
        foreach ($values as $data) {
          if (is_array($data) && count($data) > 0) {
            $fields = [];
            $valueData = [];
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
              $sql = $baseSQL.'('.implode(',', $fields).
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
     * @param string $table
     * @throws QueryFailed
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
}

