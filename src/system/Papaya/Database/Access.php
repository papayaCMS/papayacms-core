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

namespace Papaya\Database {

  use Papaya\Content\Tables as ContentTables;
  use Papaya\Database\Statement\Formatted as FormattedStatement;
  use Papaya\Database\Statement\Limited as LimitedStatement;
  use Papaya\Database\Statement\Prepared as PreparedStatement;
  use Papaya\Message;
  use Papaya\Database\Exception as DatabaseException;

  /**
   * Papaya Database Access
   *
   * @package Papaya-Library
   * @subpackage Database
   */
  class Access
    extends \Papaya\Application\BaseObject
    implements Connection {

    /**
     * a table names helper object
     *
     * @var ContentTables
     */
    private $_tables;

    /**
     * Database connection URI for read queries
     *
     * @var string
     */
    private $_uriRead;

    /**
     * Database connection URI for write queries
     *
     * @var string
     */
    private $_uriWrite;

    /**
     * Stored database connector object
     */
    private $_connector;

    /**
     * Data was modified (query on write connection)
     *
     * @var bool
     */
    private $_dataModified = FALSE;

    /**
     * Use only master (write) connection
     *
     * @var bool
     */
    private $_useMasterOnly = FALSE;

    /**
     * Member variable for a user defined error handler, if set this overrides the default
     * error handling
     *
     * @var null|callable
     */
    private $_errorHandler;
    /**
     * @var object
     */
    private $_owner;

    /**
     * The owner is used later to determine which object has uses the database access
     * (for example in logging).
     *
     * @param string|null $readUri
     * @param string|null $writeUri
     */
    public function __construct($readUri = NULL, $writeUri = NULL) {
      $this->_uriRead = $readUri;
      $this->_uriWrite = $writeUri;
    }

    /**
     * Get database connection (implicit create)
     *
     * @param string|null $connectTo connect to specified (read or write) connection
     * @return \Papaya\Database\Connector|NULL
     */
    public function getDatabaseConnector($connectTo = NULL) {
      if (NULL !== $this->_connector) {
        return $this->_connector;
      }
      if (!isset($this->papaya()->database)) {
        return NULL;
      }
      $databaseManager = $this->papaya()->database;
      if (
      $this->_connector = $databaseManager->getConnector($this->_uriRead, $this->_uriWrite)
      ) {
        if ($connectTo) {
          try {
            $this->_connector->connect($this->getConnectionMode());
          } catch (DatabaseException $exception) {
            $this->_handleDatabaseException($exception);
          }
          return NULL;
        }
        return $this->_connector;
      }
      $this->_handleDatabaseException(
        new DatabaseException\ConnectionFailed(
          \sprintf(
            'Database connector not available.'
          )
        )
      );
      return NULL;
    }

    /**
     * Set database connection
     *
     * @param \Papaya\Database\Connector $connector
     *
     */
    public function setDatabaseConnector(Connector $connector) {
      $this->_connector = $connector;
    }

    /**
     * Create a new prepared statement from an SQL string.
     *
     * @param string $sql
     * @return PreparedStatement
     */
    public function prepare($sql) {
      return new PreparedStatement($this, $sql);
    }

    /**
     * Get table name with prefix (if needed)
     *
     * @param string $tableName
     * @param bool $usePrefix
     *
     * @return bool
     */
    public function getTableName($tableName, $usePrefix = TRUE) {
      return $this->tables()->get($tableName, $usePrefix);
    }

    /**
     * Get a timestamp for create/modified fields. This method is basically here so you can mock
     * it for tests.
     *
     * @return int
     */
    public function getTimestamp() {
      return \time();
    }

    /**
     * Get table name mapper object
     *
     * @param ContentTables $tables
     *
     * @return ContentTables
     */
    public function tables(ContentTables $tables = NULL) {
      if (NULL !== $tables) {
        $this->_tables = $tables;
      } elseif (NULL === $this->_tables) {
        $this->_tables = new ContentTables();
      }
      return $this->_tables;
    }

    /**
     * set or read current master usage status
     *
     * @param bool|null $forObject optional, default value NULL
     * @param bool|null $forConnection optional, default value NULL
     *
     * @return bool use master connection only?
     */
    public function masterOnly($forObject = NULL, $forConnection = NULL) {
      if (NULL !== $forObject) {
        $this->_useMasterOnly = (bool)$forObject;
      }
      if (
        (NULL !== $forConnection) &&
        ($connector = $this->getDatabaseConnector())
      ) {
        $connector->masterOnly($forConnection);
      }
      if ($this->_useMasterOnly) {
        return TRUE;
      }
      return ($connector = $this->getDatabaseConnector()) ? $connector->masterOnly() : FALSE;
    }

    /**
     * Which connection (read or write) should be used
     *
     * @param string $requestedMode
     * @return string
     */
    public function getConnectionMode($requestedMode = Connector::MODE_READ) {
      if ($requestedMode === Connector::MODE_WRITE) {
        $this->setDataModified();
        return Connector::MODE_WRITE;
      }
      if ($this->masterOnly()) {
        return Connector::MODE_WRITE;
      }
      $switchOption = 0;
      if ($options = $this->papaya()->options) {
        $switchOption = $options->get('PAPAYA_DATABASE_CLUSTER_SWITCH', $switchOption);
      }
      switch ($switchOption) {
      case 2 : //connection context
        if ($this->_dataModified) {
          return Connector::MODE_READ;
        }
        return $this->getDatabaseConnector()->getConnectionMode();
      case 1 : //object context
        return $this->_dataModified ? Connector::MODE_WRITE : Connector::MODE_READ;
      }
      return Connector::MODE_READ;
    }

    /**
     * Set data modified status (switch to write connection)
     */
    public function setDataModified() {
      $this->_dataModified = TRUE;
      if ($connector = $this->getDatabaseConnector()) {
        $connector->setDataModified();
      }
    }

    public function debugNextQuery($counter = 1) {
      if ($connector = $this->getDatabaseConnector()) {
        $connector->debugNextQuery($counter);
      }
    }

    public function enableAbsoluteCount() {
      if ($connector = $this->getDatabaseConnector()) {
        $connector->enableAbsoluteCount();
      }
    }

    public function getProtocol() {
      try {
        if ($connector = $this->getDatabaseConnector($mode = $this->getConnectionMode())) {
          return $connector->getProtocol($mode);
        }
      } catch (DatabaseException $exception) {
        $this->_handleDatabaseException($exception);
      }
      return '';
    }

    /**
     * Make it possible to define a different error callback. This will disable the default
     * error handling (dispatching log messages) and call the given callback. To remove the
     * callback and restore the default error handling set it to FALSE.
     *
     * @param callable|false $callback
     *
     * @return callable|null void
     * @throws \InvalidArgumentException
     *
     */
    public function errorHandler($callback = NULL) {
      if (NULL !== $callback) {
        if (FALSE === $callback) {
          $this->_errorHandler = NULL;
        } elseif (\is_callable($callback)) {
          $this->_errorHandler = $callback;
        } else {
          throw new \InvalidArgumentException('Given error callback is not callable.');
        }
      }
      return $this->_errorHandler;
    }

    /**
     * Call the given error handler callback or if none is defined dispatch a log message.
     *
     * @param Exception $exception
     */
    private function _handleDatabaseException(Exception $exception) {
      $errorHandler = $this->errorHandler();
      if (NULL !== $errorHandler) {
        $errorHandler($exception);
      } elseif ($messages = $this->papaya()->messages) {
        $mapSeverity = [
          DatabaseException::SEVERITY_INFO => Message::SEVERITY_INFO,
          DatabaseException::SEVERITY_WARNING => Message::SEVERITY_WARNING,
          DatabaseException::SEVERITY_ERROR => Message::SEVERITY_ERROR,
        ];
        $logMsg = new Message\Log(
          Message\Logable::GROUP_DATABASE,
          $mapSeverity[$exception->getSeverity()],
          'Database #'.$exception->getCode().': '.$exception->getMessage()
        );
        $logMsg->context()->append(new Message\Context\Backtrace(3));
        if ($exception instanceof Exception\QueryFailed) {
          $logMsg->context()->append(new Message\Context\Text($exception->getStatement()));
        }
        $this->papaya()->messages->dispatch($logMsg);
      }
    }

    /**
     * @param \Papaya\Database\Statement|string $statement
     * @param int $options
     * @return mixed
     */
    public function execute($statement, $options = 0) {
      try {
        if ($connector = $this->getDatabaseConnector($mode = $this->getConnectionMode())) {
          return $connector->execute($statement, $options, $mode);
        }
      } catch (DatabaseException $exception) {
        $this->_handleDatabaseException($exception);
      }
      return FALSE;
    }

    /**
     * @return \Papaya\Database\Schema
     * @throws \Papaya\Database\Exception\ConnectionFailed
     */
    public function schema() {
      $mode = $this->getConnectionMode(Connector::MODE_WRITE);
      if ($connector = $this->getDatabaseConnector($mode)) {
        return $connector->schema($mode);
      }
      new DatabaseException\ConnectionFailed(
        \sprintf(
          'Database connector not available.'
        )
      );
    }

    /**
     * @return \Papaya\Database\Syntax
     * @throws \Papaya\Database\Exception\ConnectionFailed
     */
    public function syntax() {
      $mode = $this->getConnectionMode(Connector::MODE_WRITE);
      if ($connector = $this->getDatabaseConnector($mode)) {
        return $connector->syntax($mode);
      }
      new DatabaseException\ConnectionFailed(
        \sprintf(
          'Database connector not available.'
        )
      );
    }

    public function isExtensionAvailable() {
      throw new \BadMethodCallException('General class, does not depend on extension.');
    }

    /**
     * @param string $mode
     * @return \Papaya\Database\Connection
     */
    public function connect($mode = Connector::MODE_READ) {
      $this->getDatabaseConnector($this->getConnectionMode($mode));
      return $this;
    }

    /**
     * Close database connection(s)
     */
    public function disconnect() {
      if ($connector = $this->getDatabaseConnector()) {
        $connector->disconnect();
      }
    }

    /**
     * Add close function alias for BC
     *
     * @deprecated
     */
    public function close() {
      $this->disconnect();
    }

    /**
     * @param string $name
     * @param callable $function
     * @return bool
     */
    public function registerFunction($name, callable $function) {
      try {
        if ($connector = $this->getDatabaseConnector()) {
          return $connector->connect()->registerFunction($name, $function);
        }
      } catch (DatabaseException $exception) {
        $this->_handleDatabaseException($exception);
      }
      return FALSE;
    }

    /**
     * @param string $literal
     * @return string
     */
    public function escapeString($literal) {
      try {
        $mode = $this->getConnectionMode();
        if ($connector = $this->getDatabaseConnector($mode)) {
          return $connector->escapeString($literal, $mode);
        }
      } catch (DatabaseException $exception) {
        $this->_handleDatabaseException($exception);
      }
      return '';
    }

    /**
     * @param string $literal
     * @return string
     */
    public function quoteString($literal) {
      try {
        $mode = $this->getConnectionMode();
        if ($connector = $this->getDatabaseConnector($mode)) {
          return $connector->quoteString($literal, $mode);
        }
      } catch (DatabaseException $exception) {
        $this->_handleDatabaseException($exception);
      }
      return '';
    }

    /**
     * @param string $name
     * @return string
     */
    public function quoteIdentifier($name) {
      try {
        $mode = $this->getConnectionMode();
        if ($connector = $this->getDatabaseConnector($mode)) {
          return $connector->quoteIdentifier($name, $mode);
        }
      } catch (DatabaseException $exception) {
        $this->_handleDatabaseException($exception);
      }
      if (\preg_match('((?:[a-zA-Z\\d_]\\.)?[a-zA-Z\\d_])', $name)) {
        return $name;
      }
      return '_invalid_identifier_';
    }

    /**
     * @param string $tableName
     * @param array $values
     * @return bool
     */
    public function insert($tableName, array $values) {
      try {
        $mode = $this->getConnectionMode(Connector::MODE_WRITE);
        if ($connector = $this->getDatabaseConnector($mode)) {
          return $connector->connect()->insert($tableName, $values);
        }
      } catch (DatabaseException $exception) {
        $this->_handleDatabaseException($exception);
      }
      return FALSE;
    }

    /**
     * @param string $tableName
     * @param string $idField
     * @return bool
     */
    public function lastInsertId($tableName, $idField) {
      try {
        $mode = $this->getConnectionMode(Connector::MODE_WRITE);
        if ($connector = $this->getDatabaseConnector($mode)) {
          return $connector->connect()->lastInsertId(
            $tableName, $idField
          );
        }
      } catch (DatabaseException $exception) {
        $this->_handleDatabaseException($exception);
      }
      return FALSE;
    }

    /**
     * @param string $sql
     * @param null $max
     * @param null $offset
     * @param bool $readOnly
     * @return bool|Result|int
     * @deprecated
     */
    public function query($sql, $max = NULL, $offset = NULL, $readOnly = TRUE) {
      return $this->execute(
        new LimitedStatement(
          $this, $sql instanceof Statement ? $sql : new SQLStatement($sql), $max, $offset
        ),
        $readOnly ? self::USE_WRITE_CONNECTION : self::EMPTY_OPTIONS
      );
    }

    /**
     * @param string $sql
     * @return bool|int
     * @deprecated
     */
    public function queryWrite($sql) {
      return $this->execute(
        $sql instanceof Statement ? $sql : new SQLStatement($sql),
        self::USE_WRITE_CONNECTION
      );
    }

    /**
     * @param string $sql
     * @param array|string|NULL $values
     * @param null|int $max
     * @param null|int $offset
     * @param bool $readOnly
     * @return bool|Result|int
     * @deprecated
     */
    public function queryFmt($sql, $values, $max = NULL, $offset = NULL, $readOnly = TRUE) {
      if (NULL === $values) {
        $values = [];
      } elseif (!is_array($values)) {
        $values = [$values];
      }
      return $this->execute(
        new LimitedStatement($this, new FormattedStatement($this, $sql, $values), $max, $offset),
        $readOnly ? self::USE_WRITE_CONNECTION : self::EMPTY_OPTIONS
      );
    }

    /**
     * @param string $sql
     * @param array|string|NULL $values
     * @return bool|int
     * @deprecated
     */
    public function queryFmtWrite($sql, $values) {
      return $this->queryFmt($sql, $values, NULL, NULL, FALSE);
    }

    /**
     * @param array $tableStructure
     * @param string $tablePrefix
     * @return bool
     * @deprecated
     */
    public function createTable(array $tableStructure, $tablePrefix = '') {
      try {
        return $this->schema()->createTable($tableStructure, $tablePrefix);
      } catch (DatabaseException $exception) {
        $this->_handleDatabaseException($exception);
      }
      return FALSE;
    }

    /**
     * @param string $tableName
     * @param array $fieldStructure
     * @return bool
     * @deprecated
     */
    public function addField($tableName, array $fieldStructure) {
      try {
        return $this->schema()->addField($tableName, $fieldStructure);
      } catch (DatabaseException $exception) {
        $this->_handleDatabaseException($exception);
      }
      return FALSE;
    }

    /**
     * @param string $tableName
     * @param array $indexStructure
     * @return bool
     * @deprecated
     */
    public function addIndex($tableName, array $indexStructure) {
      try {
        return $this->schema()->addIndex($tableName, $indexStructure);
      } catch (DatabaseException $exception) {
        $this->_handleDatabaseException($exception);
      }
      return FALSE;
    }

    /**
     * @param string $tableName
     * @param array $fieldStructure
     * @return bool
     * @deprecated
     */
    public function changeField($tableName, array $fieldStructure) {
      try {
        return $this->schema()->changeField($tableName, $fieldStructure);
      } catch (DatabaseException $exception) {
        $this->_handleDatabaseException($exception);
      }
      return FALSE;
    }

    /**
     * @param string $tableName
     * @param array $indexStructure
     * @return bool
     * @deprecated
     */
    public function changeIndex($tableName, array $indexStructure) {
      try {
        return $this->schema()->changeIndex($tableName, $indexStructure);
      } catch (DatabaseException $exception) {
        $this->_handleDatabaseException($exception);
      }
      return FALSE;
    }

    /**
     * @param array $expectedStructure
     * @param array $currentStructure
     * @return bool
     * @deprecated
     */
    public function compareFieldStructure(array $expectedStructure, array $currentStructure) {
      try {
        return $this->schema()->isFieldDifferent($expectedStructure, $currentStructure);
      } catch (DatabaseException $exception) {
        $this->_handleDatabaseException($exception);
      }
      return FALSE;
    }

    /**
     * @param array $expectedStructure
     * @param array $currentStructure
     * @return bool
     * @deprecated
     */
    public function compareKeyStructure(array $expectedStructure, array $currentStructure) {
      try {
        return $this->schema()->isIndexDifferent($expectedStructure, $currentStructure);
      } catch (DatabaseException $exception) {
        $this->_handleDatabaseException($exception);
      }
      return FALSE;
    }

    /**
     * @param $tableName
     * @param $fieldName
     * @return bool
     * @deprecated
     */
    public function dropField($tableName, $fieldName) {
      try {
        return $this->schema()->dropField($tableName, $fieldName);
      } catch (DatabaseException $exception) {
        $this->_handleDatabaseException($exception);
      }
      return FALSE;
    }

    /**
     * @param $tableName
     * @param $indexName
     * @return bool
     * @deprecated
     */
    public function dropIndex($tableName, $indexName) {
      try {
        return $this->schema()->dropIndex($tableName, $indexName);
      } catch (DatabaseException $exception) {
        $this->_handleDatabaseException($exception);
      }
      return FALSE;
    }

    /**
     * @return array
     * @deprecated
     */
    public function queryTableNames() {
      try {
        return $this->schema()->getTables();
      } catch (DatabaseException $exception) {
        $this->_handleDatabaseException($exception);
      }
      return [];
    }

    /**
     * @param $tableName
     * @return array|NULL
     * @deprecated
     */
    public function queryTableStructure($tableName) {
      try {
        return $this->schema()->describeTable($tableName);
      } catch (DatabaseException $exception) {
        $this->_handleDatabaseException($exception);
      }
      return NULL;
    }

    /**
     * @param $tableName
     * @param array $values
     * @return bool
     * @deprecated
     */
    public function insertRecords($tableName, array $values) {
      return $this->insert($tableName, $values);
    }

    /**
     * @param string $tableName
     * @return int
     * @deprecated
     */
    public function emptyTable($tableName) {
      return $this->deleteRecord($tableName, NULL);
    }

    /**
     * @param string $tableName
     * @param array $fields
     * @param array|string $filter
     * @param mixed $value
     * @return array|FALSE
     * @deprecated
     */
    public function loadRecord($tableName, array $fields, $filter, $value = NULL) {
      $sql = 'SELECT ';
      if (count($fields) > 0) {
        foreach ($fields as $fieldName) {
          $sql .= $this->quoteIdentifier(trim($fieldName)).', ';
        }
        $sql = substr($sql, 0, -2);
      } else {
        $sql .= '*';
      }
      $sql .= ' FROM '.$this->quoteIdentifier($tableName);
      $sql .= ' WHERE '.$this->getSQLCondition($filter, $value);
      $statement = new LimitedStatement($this, new SQLStatement($sql), 1);
      if (
        ($result = $this->execute($statement)) &&
        ($row = $result->fetchAssoc())
      ) {
        return $row;
      }
      return FALSE;
    }

    /**
     * @param string $tableName
     * @param string|NULL $identifierField
     * @param array $values
     * @return bool|string
     * @deprecated
     */
    public function insertRecord($tableName, $identifierField, array $values) {
      try {
        $mode = $this->getConnectionMode(Connector::MODE_WRITE);
        if ($connector = $this->getDatabaseConnector($mode)) {
          return $connector->insertRecord(
            $tableName, $identifierField, $values
          );
        }
      } catch (DatabaseException $exception) {
        $this->_handleDatabaseException($exception);
      }
      return FALSE;
    }

    /**
     * Change database records
     *
     * @param string $tableName Table
     * @param array $values values
     * @param array|NULL|string $filter condition
     * @param mixed $value
     * @return \Papaya\Database\Result|boolean|integer
     * @deprecated
     */
    public function updateRecord($tableName, array $values, $filter, $value = NULL) {
      if (isset($values) && is_array($values) && count($values) > 0) {
        $sql = '';
        foreach ($values as $fieldName => $fieldValue) {
          $fieldName = trim($fieldName);
          if ($fieldValue === NULL) {
            $sql .= ' '.$this->quoteIdentifier($fieldName).' = NULL, ';
          } else {
            if (is_bool($fieldValue)) {
              $fieldValue = $fieldValue ? '1' : '0';
            }
            $sql .= ' '.$this->quoteIdentifier($fieldName).' = '.$this->quoteString($fieldValue).', ';
          }
        }
        if (!empty($sql)) {
          $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $this->quoteIdentifier($tableName),
            substr($sql, 0, -2),
            $this->getSQLCondition($filter, $value)
          );
          return $this->execute($sql, self::USE_WRITE_CONNECTION);
        }
      }
      return FALSE;
    }

    /**
     * @param string $tableName
     * @param string|array $filter
     * @param mixed $value
     * @return int|FALSE
     * @deprecated
     */
    public function deleteRecord($tableName, $filter, $value = NULL) {
      $sql = sprintf(
        'DELETE FROM %s WHERE %s',
        $this->quoteIdentifier($tableName),
        $this->getSQLCondition($this->getConditionArray($filter, $value))
      );
      return $this->execute($sql, self::DISABLE_RESULT_CLEANUP | self::USE_WRITE_CONNECTION);
    }

    /**
     * @param $function
     * @param array $parameters
     * @return string|NULL
     * @deprecated
     */
    public function getSQLSource($function, array $parameters) {
      try {
        $arguments = [];
        for ($i = 0, $c = count($parameters); $i < $c; $i += 2) {
          if (isset($parameters[$i + 1]) && !$parameters[$i + 1]) {
            $arguments[] = new \Papaya\Database\Syntax\SQLSource($parameters[$i]);
          } else {
            $arguments[] = (string)$parameters[$i];
          }
        }
        $call = [$this->syntax(), $function];
        $source = $call(...$arguments);
        return $source ?: NULL;
      } catch (DatabaseException $exception) {
        $this->_handleDatabaseException($exception);
      }
      return NULL;
    }

    /**
     * @param array|string $filter
     * @param mixed $value
     * @param string $operator
     * @return string
     * @deprecated
     */
    public function getSQLCondition($filter, $value = NULL, $operator = '=') {
      try {
        $mode = $this->getConnectionMode();
        if ($connector = $this->getDatabaseConnector($mode)) {
          return $connector->getSqlCondition($filter, $value, $operator, $mode);
        }
      } catch (DatabaseException $exception) {
        $this->_handleDatabaseException($exception);
      }
      return '(0 = 1)';
    }

    /**
     * Convert different $filter arguments to an array
     *
     * @param string|array|NULL $filter
     * @param mixed $value
     * @return array|NULL
     */
    private function getConditionArray($filter, $value = NULL) {
      if (empty($filter)) {
        return NULL;
      }
      if (is_string($filter)) {
        return [$filter => $value];
      }
      return $filter;
    }
  }
}
