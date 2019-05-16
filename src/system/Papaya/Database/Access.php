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
namespace Papaya\Database;

use Papaya\Message;

/**
 * Papaya Database Access
 *
 * @package Papaya-Library
 * @subpackage Database
 *
 * @method bool addField(string $table, array $fieldData)
 * @method bool addIndex(string $table, array $index)
 * @method bool changeField(string $table, array $fieldData)
 * @method bool changeIndex(string $table, array $index)
 * @method void close()
 * @method true compareFieldStructure(array $xmlField, array $databaseField)
 * @method bool compareKeyStructure()
 * @method bool createTable(string $tableData, string $tablePrefix)
 * @method void debugNextQuery(integer $count = 1)
 * @method int deleteRecord(string $table, mixed $filter, mixed $value = NULL)
 * @method bool dropField(string $table, string $field)
 * @method bool dropIndex(string $table, string $name)
 * @method void enableAbsoluteCount()
 * @method void emptyTable(string $table)
 * @method string escapeString(mixed $value)
 * @method string quoteString(mixed $value)
 * @method string getProtocol()
 * @method string getSqlSource(string $function, array $params)
 * @method string getSqlCondition(array $filter, $value = NULL, $operator = '=')
 * @method int|null insertRecord(string $table, string $idField, array $values = NULL)
 * @method bool insertRecords(string $table, array $values)
 * @method int lastInsertId(string $table, string $idField)
 * @method bool|Result query(string $sql, integer $max = NULL, integer $offset = NULL, boolean $readOnly = TRUE)
 * @method bool|Result queryFmt(string $sql, array $values, integer $max = NULL, integer $offset = NULL, boolean $readOnly = TRUE)
 * @method bool|Result queryFmtWrite(string $sql, array $values)
 * @method bool|Result queryWrite(string $sql)
 * @method false|array loadRecord(string $table, array $values, mixed $filter, mixed $value = NULL)
 * @method int updateRecord(string $table, array $values, mixed $filter, mixed $value = NULL)
 * @method array queryTableNames()
 * @method array queryTableStructure(string $tableName)
 */
class Access extends \Papaya\Application\BaseObject {
  /**
   * calling object
   *
   * @var \Papaya\Application\BaseObject
   */
  private $_owner;

  /**
   * a table names helper object
   *
   * @var \Papaya\Content\Tables
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
   * Delegate function
   *
   * FALSE = read function
   * TRUE = write function
   *
   * @var array
   */
  private $_delegateFunctions = [
    'addField' => TRUE,
    'addIndex' => TRUE,
    'changeField' => TRUE,
    'changeIndex' => TRUE,
    'close' => FALSE,
    'compareFieldStructure' => FALSE,
    'compareKeyStructure' => FALSE,
    'createTable' => TRUE,
    'debugNextQuery' => FALSE,
    'deleteRecord' => TRUE,
    'dropField' => TRUE,
    'dropIndex' => TRUE,
    'enableAbsoluteCount' => FALSE,
    'emptyTable' => TRUE,
    'escapeString' => FALSE,
    'getProtocol' => FALSE,
    'getSqlSource' => FALSE,
    'getSqlCondition' => FALSE,
    'insertRecord' => TRUE,
    'insertRecords' => TRUE,
    'lastInsertId' => TRUE,
    'query' => FALSE,
    'queryFmt' => FALSE,
    'queryFmtWrite' => TRUE,
    'queryWrite' => TRUE,
    'loadRecord' => FALSE,
    'updateRecord' => TRUE,
    'queryTableNames' => FALSE,
    'queryTableStructure' => FALSE
  ];

  /**
   * Map lowercase versions of the deletegate functions to the real ones
   *
   * @var array
   */
  private $_functionMapping = [];

  /**
   * The owner is used later to determine which object has uses the database access
   * (for example in logging).
   *
   * @param object $owner calling object
   * @param string|null $readUri
   * @param string|null $writeUri
   */
  public function __construct($owner, $readUri = NULL, $writeUri = NULL) {
    $this->_owner = $owner;
    $this->_uriRead = $readUri;
    $this->_uriWrite = $writeUri;
    foreach ($this->_delegateFunctions as $name => $modifies) {
      $this->_functionMapping[\strtolower($name)] = $name;
    }
  }

  /**
   * Get database connection (implicit create)
   *
   * @var \Papaya\Database\Manager $databaseManager
   *
   * @return \db_simple
   */
  public function getDatabaseConnector() {
    if (NULL !== $this->_connector) {
      return $this->_connector;
    }
    if (!isset($this->papaya()->database)) {
      return NULL;
    }
    $databaseManager = $this->papaya()->database;
    $this->_connector = $databaseManager->getConnector($this->_uriRead, $this->_uriWrite);
    return $this->_connector;
  }

  /**
   * Set database connection
   *
   * @todo define an interface for database connectors
   *
   * @param \db_simple $connector
   */
  public function setDatabaseConnector($connector) {
    $this->_connector = $connector;
  }

  /**
   * Create a new prepared statement from an SQL string.
   *
   * @param string $sql
   * @return Statement\Prepared
   */
  public function prepare($sql) {
    return new Statement\Prepared($this, $sql);
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
   * @param \Papaya\Content\Tables $tables
   *
   * @return \Papaya\Content\Tables
   */
  public function tables(\Papaya\Content\Tables $tables = NULL) {
    if (NULL !== $tables) {
      $this->_tables = $tables;
    } elseif (NULL === $this->_tables) {
      $this->_tables = new \Papaya\Content\Tables();
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

  /**
   * Delegate calls to the database connector object
   *
   * @param string $functionName
   * @param array $arguments
   *
   * @throws \BadMethodCallException
   *
   * @return mixed
   */
  public function __call($functionName, $arguments) {
    if (isset($this->_delegateFunctions[$functionName])) {
      $delegateFunction = $functionName;
    } elseif (isset($this->_functionMapping[\strtolower($functionName)])) {
      $delegateFunction = $this->_functionMapping[\strtolower($functionName)];
    } else {
      $delegateFunction = NULL;
    }
    if (
      NULL !== $delegateFunction &&
      isset($this->_delegateFunctions[$delegateFunction])) {
      $connector = $this->getDatabaseConnector();
      if (!($connector instanceof \db_simple)) {
        throw new \BadMethodCallException(
          \sprintf(
            'Invalid function call. Can not fetch database connector.'
          )
        );
      }
      if (\method_exists($connector, $delegateFunction)) {
        \array_unshift($arguments, $this->_owner);
        try {
          $result = $connector->$delegateFunction(...$arguments);
          if ($result &&
            $this->_delegateFunctions[$delegateFunction]) {
            $this->setDataModified();
          }
          return $result;
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (Exception $exception) {
          $this->_handleDatabaseException($exception);
          return FALSE;
        }
      } else {
        throw new \BadMethodCallException(
          \sprintf(
            'Invalid function call. Method %s::%s does not exist.',
            \is_object($connector) ? \get_class($connector) : \gettype($connector),
            $functionName
          )
        );
      }
    } else {
      throw new \BadMethodCallException(
        \sprintf(
          'Invalid function call. Method %s::%s does not exist.',
          \get_class($this),
          $functionName
        )
      );
    }
  }

  /**
   * Make it possible to define a different error callback. This will disable the default
   * error handling (dispatching log messages) and call the given callback. To remove the
   * callback and restore the default error handling set it to FALSE.
   *
   * @param callable|false $callback
   *
   * @throws \InvalidArgumentException
   *
   * @return callable|null void
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
}
