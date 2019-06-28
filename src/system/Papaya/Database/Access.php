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
     * Map lowercase versions of the delegate functions to the real ones
     *
     * @var array|NULL
     */
    private static $_functionMapping;

    /**
     * The owner is used later to determine which object has uses the database access
     * (for example in logging).
     *
     * @param object $owner calling object
     * @param string|null $readUri
     * @param string|null $writeUri
     */
    public function __construct($owner, $readUri = NULL, $writeUri = NULL) {
      $this->_uriRead = $readUri;
      $this->_uriWrite = $writeUri;
    }

    /**
     * Get database connection (implicit create)
     *
     * @return \db_simple
     * @var \Papaya\Database\Manager $databaseManager
     *
     */
    public function getDatabaseConnector() {
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
        return $this->_connector;
      }
      throw new \BadMethodCallException(
        \sprintf(
          'Invalid function call. Can not fetch database connector.'
        )
      );
    }

    /**
     * Set database connection
     *
     * @param \db_simple $connector
     * @todo define an interface for database connectors
     *
     */
    public function setDatabaseConnector($connector) {
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
     * @param string $identifier
     * @return string
     */
    public function quoteIdentifier($identifier) {
      $connector = $this->getDatabaseConnector();
      if (\method_exists($connector, 'quoteIdentifier')) {
        return $connector->quoteIdentifier($identifier);
      }
      if (\preg_match('([a-zA-Z\\d_])', $identifier)) {
        return $identifier;
      }
      return '_invalid_identifier_';
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
      if (NULL !== $forConnection) {
        $this->getDatabaseConnector()->masterOnly($forConnection);
      }
      if ($this->_useMasterOnly) {
        return TRUE;
      }
      return $this->getDatabaseConnector()->masterOnly();
    }

    /**
     * should the current read request go to the write connection?
     *
     * @param bool $usable read connection possible
     *
     * @return bool
     */
    public function readOnly($usable) {
      if (!$usable) {
        $this->setDataModified();
        return FALSE;
      }
      if ($this->masterOnly()) {
        return FALSE;
      }
      $switchOption = $this->papaya()
        ->getObject('Options')
        ->getOption('PAPAYA_DATABASE_CLUSTER_SWITCH', 0);
      switch ($switchOption) {
      case 2 : //connection context
        return $this->getDatabaseConnector()->readOnly($usable);
        break;
      case 1 : //object context
        return !$this->_dataModified;
        break;
      }
      return TRUE;
    }

    /**
     * Set data modified status (switch to write connection)
     */
    public function setDataModified() {
      $this->_dataModified = TRUE;
      $this->getDatabaseConnector()->setDataModified();
    }

    public function debugNextQuery($counter = 1) {
      $this->getDatabaseConnector()->debugNextQuery($counter);
    }

    public function enableAbsoluteCount() {
      $this->getDatabaseConnector()->enableAbsoluteCount();
    }

    public function getProtocol() {
      return $this->getDatabaseConnector()->getProtocol();
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
          Exception::SEVERITY_INFO => Message::SEVERITY_INFO,
          Exception::SEVERITY_WARNING => Message::SEVERITY_WARNING,
          Exception::SEVERITY_ERROR => Message::SEVERITY_ERROR,
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
        return $this->getDatabaseConnector()->execute($statement, $options);
      } catch (Exception $exception) {
        $this->_handleDatabaseException($exception);
        return FALSE;
      }
    }

    /**
     * @return \Papaya\Database\Schema
     */
    public function schema() {
      try {
        return $this->getDatabaseConnector()->connect()->schema();
      } catch (Exception $exception) {
        $this->_handleDatabaseException($exception);
        throw new \BadMethodCallException('Can not fetch syntax object');
      }
    }

    /**
     * @return \Papaya\Database\Syntax
     */
    public function syntax() {
      try {
        return $this->getDatabaseConnector()->connect()->syntax();
      } catch (Exception $exception) {
        $this->_handleDatabaseException($exception);
        throw new \BadMethodCallException('Can not fetch schema object');
      }
    }

    public function isExtensionAvailable() {
      throw new \BadMethodCallException('General class, does not depend on extension.');
    }

    public function connect() {
      try {
        if ($this->getDatabaseConnector()->connect()) {
          return $this;
        }
      } catch (Exception $exception) {
        $this->_handleDatabaseException($exception);
      }
      return FALSE;
    }

    public function disconnect() {
      $this->getDatabaseConnector()->disconnect();
    }

    /**
     * @param string $name
     * @param callable $function
     * @return bool
     */
    public function registerFunction($name, callable $function) {
      try {
        return $this->getDatabaseConnector()->connect()->registerFunction(
          $name, $function
        );
      } catch (Exception $exception) {
        $this->_handleDatabaseException($exception);
        throw new \BadMethodCallException(
          'Can not register function, API class not available'
        );
      }
    }

    /**
     * @param string $literal
     * @return string
     */
    public function escapeString($literal) {
      try {
        return $this->getDatabaseConnector()->connect()->escapeString($literal);
      } catch (Exception $exception) {
        $this->_handleDatabaseException($exception);
        return '';
      }
    }

    /**
     * @param string $literal
     * @return string
     */
    public function quoteString($literal) {
      try {
        return $this->getDatabaseConnector()->connect()->quoteString($literal);
      } catch (Exception $exception) {
        $this->_handleDatabaseException($exception);
        return '';
      }
    }

    public function insert($tableName, array $values) {
      try {
        return $this->getDatabaseConnector()->connect(FALSE)->insert(
          $tableName, $values
        );
      } catch (Exception $exception) {
        $this->_handleDatabaseException($exception);
        return FALSE;
      }
    }

    public function lastInsertId($tableName, $idField) {
      try {
        return $this->getDatabaseConnector()->connect(FALSE)->lastInsertId(
          $tableName, $idField
        );
      } catch (Exception $exception) {
        $this->_handleDatabaseException($exception);
        return FALSE;
      }
    }

    /**
     * @param $sql
     * @param null $max
     * @param null $offset
     * @param bool $readOnly
     * @return bool|Result|int
     */
    public function query($sql, $max = NULL, $offset = NULL, $readOnly = TRUE) {
      return $this->execute(
        new LimitedStatement(
          $this, $sql instanceof Statement ? $sql : new SQLStatement($sql), $max, $offset
        ),
        $readOnly ? self::FORCE_WRITE_CONNECTION : self::EMPTY_OPTIONS
      );
    }

    /**
     * @param $sql
     * @return bool|int
     * @deprecated
     */
    public function queryWrite($sql) {
      return $this->execute(
        $sql instanceof Statement ? $sql : new SQLStatement($sql),
        self::FORCE_WRITE_CONNECTION
      );
    }

    /**
     * @param $sql
     * @param array $values
     * @param null $max
     * @param null $offset
     * @param bool $readOnly
     * @return bool|Result|int
     * @deprecated
     */
    public function queryFmt($sql, array $values, $max = NULL, $offset = NULL, $readOnly = TRUE) {
      return $this->execute(
        new LimitedStatement($this, new FormattedStatement($this, $sql, $values), $max, $offset),
        $readOnly ? self::FORCE_WRITE_CONNECTION : self::EMPTY_OPTIONS
      );
    }

    /**
     * @param $sql
     * @param array $values
     * @return bool|int
     * @deprecated
     */
    public function queryFmtWrite($sql, array $values) {
      return $this->execute(
        new FormattedStatement($this, $sql, $values),
        self::FORCE_WRITE_CONNECTION
      );
    }

    /**
     * @param array $tableStructure
     * @param string $tablePrefix
     * @return bool
     * @deprecated
     */
    public function createTable(array $tableStructure, $tablePrefix = '') {
      return $this->schema()->createTable($tableStructure, $tablePrefix);
    }

    /**
     * @param string $tableName
     * @param array $fieldStructure
     * @return bool
     * @deprecated
     */
    public function addField($tableName, array $fieldStructure) {
      return $this->schema()->addField($tableName, $fieldStructure);
    }

    /**
     * @param string $tableName
     * @param array $indexStructure
     * @return bool
     * @deprecated
     */
    public function addIndex($tableName, array $indexStructure) {
      return $this->schema()->addIndex($tableName, $indexStructure);
    }

    /**
     * @param string $tableName
     * @param array $fieldStructure
     * @return bool
     * @deprecated
     */
    public function changeField($tableName, array $fieldStructure) {
      return $this->schema()->changeField($tableName, $fieldStructure);
    }

    /**
     * @param string $tableName
     * @param array $indexStructure
     * @return bool
     * @deprecated
     */
    public function changeIndex($tableName, array $indexStructure) {
      return $this->schema()->changeIndex($tableName, $indexStructure);
    }

    /**
     * @param array $expectedStructure
     * @param array $currentStructure
     * @return bool
     * @deprecated
     */
    public function compareFieldStructure(array $expectedStructure, array $currentStructure) {
      return $this->schema()->isFieldDifferent($expectedStructure, $currentStructure);
    }

    /**
     * @param array $expectedStructure
     * @param array $currentStructure
     * @return bool
     * @deprecated
     */
    public function compareKeyStructure(array $expectedStructure, array $currentStructure) {
      return $this->schema()->isIndexDifferent($expectedStructure, $currentStructure);
    }

    /**
     * @param $tableName
     * @param $fieldName
     * @return bool
     * @deprecated
     */
    public function dropField($tableName, $fieldName) {
      return $this->schema()->dropField($tableName, $fieldName);
    }

    /**
     * @param $tableName
     * @param $indexName
     * @return bool
     * @deprecated
     */
    public function dropIndex($tableName, $indexName) {
      return $this->schema()->dropIndex($tableName, $indexName);
    }

    /**
     * @return array
     * @deprecated
     */
    public function queryTableNames() {
      return $this->schema()->getTables();
    }

    /**
     * @param $tableName
     * @return array
     * @deprecated
     */
    public function queryTableStructure($tableName) {
      return $this->schema()->describeTable($tableName);
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
     * @param $tableName
     * @param $identifierField
     * @param array $values
     * @return bool|string
     * @deprecated
     */
    public function insertRecord($tableName, $identifierField, array $values) {
      try {
        return $this->getDatabaseConnector()->insertRecord(
          $tableName, $identifierField, $values
        );
      } catch (Exception $exception) {
        $this->_handleDatabaseException($exception);
        return FALSE;
      }
    }

    /**
     * Change database records
     *
     * @param string $tableName Table
     * @param array $values values
     * @param array|NULL $filter condition
     * @param null $value
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
          return $this->execute($sql, self::FORCE_WRITE_CONNECTION);
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
      return $this->execute($sql, self::DISABLE_RESULT_CLEANUP);
    }

    /**
     * @param $function
     * @param array $parameters
     * @return string
     * @deprecated
     */
    public function getSQLSource($function, array $parameters) {
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
      return $source ?: FALSE;
    }

    /**
     * @param array|string $filter
     * @param null $value
     * @param string $operator
     * @return string
     * @throws \Papaya\Database\Exception\ConnectionFailed
     * @deprecated
     */
    public function getSqlCondition($filter, $value = NULL, $operator = '=') {
      return $this->getDatabaseConnector()->getSqlCondition($filter, $value, $operator);
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
