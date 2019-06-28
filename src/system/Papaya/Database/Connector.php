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

  use Papaya\Application\Access as ApplicationAccess;
  use Papaya\Content\Tables as ContentTables;
  use Papaya\Database\Connection as DatabaseConnection;
  use Papaya\Database\Connection\MySQLiConnection;
  use Papaya\Database\Connection\PostgreSQLConnection;
  use Papaya\Database\Connection\SQLite3Connection;
  use \Papaya\Database\Exception as DatabaseException;
  use Papaya\Database\Exception\ConnectionFailed;
  use Papaya\Database\Result as DatabaseResult;
  use Papaya\Database\Source\Name as DataSourceName;
  use Papaya\Database\Statement as DatabaseStatement;
  use Papaya\Message;
  use Papaya\Utility;
  use Papaya\Utility\Bitwise;

  /**
   * DB - abstraction layer
   *
   * @package Papaya
   * @subpackage Database
   */
  class Connector implements ApplicationAccess {

    use ApplicationAccess\Aggregation;

    const MODE_READ = 'read';
    const MODE_WRITE = 'write';

    private static $_connectionClasses = [
      'mysql' => MySQLiConnection::class,
      'mysqli' => MySQLiConnection::class,
      'pgsql' => PostgreSQLConnection::class,
      'sqlite' => SQLite3Connection::class,
      'sqlite3' => SQLite3Connection::class
    ];

    /**
     * Id to keep track of requests for query log
     *
     * @var string $requestId
     */
    private static $requestId = '';

    /**
     * Internal absolute query counter
     *
     * @var integer
     */
    private static $queryCounterClass = 0;

    /**
     * counter for queries using this object
     *
     * @var integer $queryCounterObject
     */
    private $queryCounterObject = 0;

    /**
     * database URI
     *
     * @var array:string $databaseURIs
     */
    private $_databaseURIs = [
      self::MODE_READ => '',
      self::MODE_WRITE => ''
    ];

    /**
     * database configuration
     *
     * @var DataSourceName[] $_databaseConfiguration
     */
    private $_databaseConfiguration = [
      self::MODE_READ => NULL,
      self::MODE_WRITE => NULL
    ];

    /**
     * database connection for selects
     *
     * @var Connection[] $databaseObjects
     */
    private $_databaseObjects = [
      self::MODE_READ => NULL,
      self::MODE_WRITE => NULL
    ];

    /**
     * time spent on queries using this object
     * (Needs PAPAYA_DBG_DATABASE_EXPLAIN or PAPAYA_DBG_DATABASE_SLOWQUERIES activated.)
     *
     * @var float $querytimeSum
     */
    private $_queryTimeSum = 0;

    /**
     * Debug the next n queries
     *
     * @var integer $debugCounter
     */
    private $debugCounter = 0;

    /**
     * Enable Calculation of absolute records for the next n Queries.
     *
     * @var integer $_enableAbsoluteCounter
     */
    private $_enableAbsoluteCounter = 0;

    /**
     *
     * @var boolean $databaseStatusMasterOnly
     */
    private $_useMasterOnly = FALSE;

    /**
     * @var bool
     */
    private $_dataModified = FALSE;

    public function __construct($readURI, $writeURI = NULL) {
      $this->_databaseURIs = [
        self::MODE_READ => $readURI,
        self::MODE_WRITE => $writeURI
      ];
    }

    /**
     * @param string $mode
     * @return DataSourceName
     * @throws ConnectionFailed
     */
    private function getDSN($mode = self::MODE_READ) {
      if (isset($this->_databaseConfiguration[$mode])) {
        return $this->_databaseConfiguration[$mode];
      }
      if ($mode !== self::MODE_READ) {
        if (
          isset($this->_databaseURIs[$mode]) &&
          $this->_databaseURIs[$mode] !== 'PAPAYA_DB_URI_WRITE' &&
          $this->_databaseURIs[$mode] !== $this->_databaseURIs[self::MODE_READ] &&
          trim($this->_databaseURIs[$mode]) !== ''
        ) {
          return $this->_databaseConfiguration[$mode] = new DataSourceName(
            $this->_databaseURIs[$mode]
          );
        }
        if (isset($this->_databaseConfiguration[self::MODE_READ])) {
          return $this->_databaseConfiguration[$mode] = $this->_databaseConfiguration[self::MODE_READ];
        }
      }
      return
        $this->_databaseConfiguration[$mode] =
        $this->_databaseConfiguration[self::MODE_READ] =
          new DataSourceName($this->_databaseURIs[self::MODE_READ]);
    }

    /**
     * Fetch connection for use
     *
     * @param string $mode
     * @return Connection
     * @throws ConnectionFailed
     */
    private function getConnection($mode = self::MODE_READ) {
      if (isset($this->_databaseObjects[$mode])) {
        return $this->_databaseObjects[$mode];
      }
      $configuration = $this->getDSN($mode);
      if (
        $mode !== self::MODE_READ &&
        isset($this->_databaseObjects[self::MODE_READ]) &&
        $configuration === $this->getDSN()
      ) {
        return $this->_databaseObjects[$mode] = $this->_databaseObjects[self::MODE_READ];
      }
      return isset($this->_databaseObjects[$mode])
        ? $this->_databaseObjects[$mode] : $this->createConnection($configuration);
    }

    /**
     * @param DataSourceName $dsn
     * @return Connection
     * @throws ConnectionFailed
     */
    private function createConnection($dsn) {
      $className = isset(self::$_connectionClasses[$dsn->api])
        ? self::$_connectionClasses[$dsn->api] : '';
      if ($className) {
        if (defined('PAPAYA_DBG_DEVMODE') && PAPAYA_DBG_DEVMODE) {
          $found = class_exists($className);
        } else {
          $found = @class_exists($className);
        }
        if ($found) {
          return new $className($dsn);
        }
        throw new ConnectionFailed(
          sprintf(
            'Connection class "%s" could not be loaded.', $dsn->api
          )
        );
      }
      throw new ConnectionFailed(
        sprintf(
          'No connection class for API "%s" defined.', $dsn->api
        )
      );
    }


    /**
     * connect to database
     *
     * @param boolean $readOnly use read connection
     * @return Connection
     * @throws ConnectionFailed
     */
    public function connect($readOnly = TRUE) {
      $mode = $readOnly ? self::MODE_READ : self::MODE_WRITE;
      $connection = $this->getConnection($mode);
      if ($connection->isExtensionAvailable() && $connection->connect()) {
        return $connection;
      }
      throw new ConnectionFailed(
        sprintf('Could not connect to database.')
      );
    }

    /**
     * Close database connection
     *
     * @return void
     */
    public function disconnect() {
      if ($this->papaya()->options->get('PAPAYA_LOG_RUNTIME_DATABASE', FALSE)) {
        if ($this->_queryTimeSum > 0) {
          $message = 'Database Query Count: '.$this->queryCounterObject.' in '.
            Utility\Date::periodToString($this->_queryTimeSum);
        } else {
          $message = 'Database Query Count: '.$this->queryCounterObject;
        }
        $this->papaya()->messages->dispatch(
          new Message\Log(
            Message\Logable::GROUP_DEBUG,
            Message::SEVERITY_DEBUG,
            $message
          )
        );
      }
      foreach ($this->_databaseObjects as $connection) {
        $connection->disconnect();
      }
    }

    /**
     * Debug next query, set debug counter
     *
     * @param integer $count optional, default value 1
     * @access public
     */
    public function debugNextQuery($count = 1) {
      $this->debugCounter = $count;
    }

    /**
     * Calculate the absolute record count for the next query (if limited).
     *
     * @access public
     * @return void
     */
    public function enableAbsoluteCount() {
      $this->_enableAbsoluteCounter = 1;
    }

    /**
     * Escape value / string
     *
     * @param mixed $value value to escape
     * @param boolean $readOnly use read connection
     * @return string
     * @throws ConnectionFailed
     */
    public function escapeString($value, $readOnly = TRUE) {
      if ($connection = $this->connect($readOnly)) {
        return $connection->escapeString($value);
      }
      return '';
    }

    /**
     * Escape and quote value / string
     *
     * @param mixed $value value to escape
     * @param boolean $readOnly use read connection
     * @return string
     * @throws ConnectionFailed
     */
    public function quoteString($value, $readOnly = TRUE) {
      if ($connection = $this->connect($readOnly)) {
        return $connection->quoteString($value);
      }
      return '';
    }


    /**
     * @param string|DatabaseStatement $statement
     * @param int $options
     * @return mixed
     * @throws ConnectionFailed
     */
    public function execute($statement, $options = DatabaseConnection::EMPTY_OPTIONS) {
      $error = NULL;
      //global query counter
      self::$queryCounterClass++;
      //object query counter
      $this->queryCounterObject++;

      $settings = $this->papaya()->options;

      $measureTime = FALSE;
      if (
        $this->debugCounter > 0 ||
        $settings->get('PAPAYA_LOG_RUNTIME_DATABASE', FALSE) ||
        $settings->get('PAPAYA_LOG_DATABASE_QUERY', 0) > 0 ||
        $settings->get('PAPAYA_QUERYLOG', 0) > 0
      ) {
        $measureTime = TRUE;
      }

      $readOnly = Bitwise::inBitmask(
        DatabaseConnection::FORCE_WRITE_CONNECTION, $options
      );
      $this->connect($readOnly);

      $sql = (string)$statement;

      if (
        $readOnly &&
        $settings->get('PAPAYA_LOG_DATABASE_CLUSTER_VIOLATIONS', FALSE) &&
        preg_match(
          '~^\s*(INSERT|UPDATE|ALTER|CREATE|DROP)~i', $sql
        )
      ) {
        $logMessage = new Message\Log(
          Message\Logable::GROUP_DATABASE,
          Message::SEVERITY_WARNING,
          'Detected write query on read connection.'
        );
        $logMessage
          ->context()
          ->append(new Message\Context\Text($sql))
          ->append(new Message\Context\Backtrace(1));
        $this->papaya()->messages->dispatch($logMessage);
      }
      if ($this->_enableAbsoluteCounter > 0) {
        $options |= DatabaseConnection::REQUIRE_ABSOLUTE_COUNT;
        $this->_enableAbsoluteCounter--;
      }
      $timeStart = microtime(TRUE);
      if ($connection = $this->connect($readOnly)) {
        $result = $connection->execute($statement, $options);
        if ($measureTime) {
          $this->_logQueryExecution(
            $timeStart,
            [
              'sql' => $sql,
              'readOnly' => $readOnly,
              'result' => $result
            ]
          );
        }
        if (is_int($result) && $result === 0) {
          return TRUE;
        }
        return $result;
      }
      return FALSE;
    }

    /**
     * @throws ConnectionFailed
     */
    public function schema() {
      $this->connect(FALSE)->schema();
    }

    /**
     * @param bool $readOnly
     * @throws ConnectionFailed
     */
    public function syntax($readOnly = TRUE) {
      $this->connect($readOnly)->syntax();
    }

    /**
     * Log query to message system and/or query log
     *
     * @param float $timeStart
     * @param array $query
     */
    private function _logQueryExecution($timeStart, $query) {
      $timeStop = microtime(TRUE);
      $timeDelta = ($timeStop - $timeStart);
      $this->_queryTimeSum += $timeDelta;
      $dispatchLogMessage = FALSE;
      $dispatchLogMessageDetails = FALSE;
      $populateQueryLog = FALSE;
      $populateQueryLogDetails = FALSE;
      $options = $this->papaya()->options;
      if ($this->debugCounter > 0) {
        $dispatchLogMessage = TRUE;
        $dispatchLogMessageDetails = TRUE;
        $this->debugCounter--;
      } else {
        $deltaMilliseconds = $timeDelta * 1000;
        switch ($options->get('PAPAYA_LOG_DATABASE_QUERY', 0)) {
        case 2 : // all
          if (!empty($_GET['DEBUG_QUERIES'])) {
            $queryNumbers = explode(',', $_GET['DEBUG_QUERIES']);
            if (in_array(self::$queryCounterClass, $queryNumbers, FALSE)) {
              $dispatchLogMessage = TRUE;
            }
          } else {
            $dispatchLogMessage = TRUE;
          }
          break;
        case 1 : //slow queries
          $dispatchLogMessage =
            $options->get('PAPAYA_LOG_DATABASE_QUERY_SLOW', 0) < $deltaMilliseconds;
          break;
        }
        switch ($options->get('PAPAYA_QUERYLOG', 0)) {
        case 2 : // all
          $populateQueryLog = TRUE;
          break;
        case 1 : //slow queries
          $populateQueryLog =
            $options->get('PAPAYA_QUERYLOG_SLOW', 0) < $deltaMilliseconds;
          break;
        }
      }
      if ($dispatchLogMessage || $populateQueryLog) {
        if ($query['readOnly']) {
          $caption = sprintf(
            'Query #%d on read connection',
            self::$queryCounterClass
          );
        } else {
          $caption = sprintf(
            'Query #%d on write connection',
            self::$queryCounterClass
          );
        }
        $backtrace = NULL;
        $explain = NULL;
        $counter = NULL;
        if ($options->get('PAPAYA_LOG_DATABASE_QUERY_DETAILS', FALSE)) {
          $dispatchLogMessageDetails = TRUE;
        }
        if ($options->get('PAPAYA_QUERYLOG_DETAILS', FALSE)) {
          $populateQueryLogDetails = TRUE;
        }
        if ($dispatchLogMessageDetails || $populateQueryLogDetails) {
          if (is_object($query['result'])) {
            /** @var DatabaseResult $queryResult */
            $queryResult = $query['result'];
            if (isset($query['max'])) {
              $counter = sprintf(
                'Record(s): %d of %d from %d',
                $queryResult->count(),
                $queryResult->absCount(),
                empty($query['offset']) ? 0 : $query['offset']
              );
            } else {
              $counter = sprintf(
                'Record(s): %d',
                $queryResult->count()
              );
            }
            if (preg_match('(^\s*SELECT)i', $query['sql'])) {
              $explain = $queryResult->getExplain();
            }
          }
          $backtrace = new Message\Context\Backtrace(9);
        }
        if ($dispatchLogMessage) {
          $logMessage = new Message\Log(
            Message\Logable::GROUP_DATABASE,
            Message::SEVERITY_DEBUG,
            $caption
          );
          $logMessage->context()->append(new Message\Context\Runtime($timeStart, $timeStop));
          if (isset($counter)) {
            $logMessage->context()->append(new Message\Context\Text($counter));
          }
          $logMessage->context()->append(
            new Message\Context\Variable(['sql' => $query['sql']], 3, 99999)
          );
          if ($dispatchLogMessageDetails) {
            $logMessage->context()->append($backtrace);
            if (isset($explain)) {
              $logMessage->context()->append($explain);
            }
          }
          $this->papaya()->messages->dispatch($logMessage);
        }
        if ($populateQueryLog) {
          if (empty(self::$requestId)) {
            self::$requestId = md5(uniqid(mt_rand(), TRUE));
          }
          $logData = [
            'query_request' => self::$requestId,
            'query_timestamp' => time(),
            'query_class' => get_class($query['object']),
            'query_count' => self::$queryCounterClass,
            'query_conn' => $query['readOnly'] ? self::MODE_READ : self::MODE_WRITE,
            'query_time' => $timeDelta * 1000,
            'query_content' => $query['sql'],
            'query_hash' => md5($query['sql']),
            'query_uri' => empty($_SERVER['REQUEST_URI']) ? '' : $_SERVER['REQUEST_URI'],
          ];
          if ($query['result'] instanceof Result) {
            $logData['query_records'] = $query['result']->count();
            if (isset($query['max'])) {
              $logData['query_limit'] = $query['max'];
            }
            if (isset($query['offset'])) {
              $logData['query_offset'] = $query['offset'];
            }
          }
          if ($populateQueryLogDetails) {
            $logData['query_backtrace'] = $backtrace->asString();
            if (isset($explain) && $explain instanceof Message\Context\Interfaces\Text) {
              $logData['query_explain'] = $explain->asString();
            }
          }
          try {
            $this->insertRecord(
              $this->getTableName(ContentTables::LOG_DATABASE_QUERIES, TRUE),
              NULL,
              $logData
            );
          } catch (DatabaseException $e) {
          }
        }
      }
    }

    /**
     * @param $tableName
     * @param $identifierField
     * @param array $values
     * @return bool|string
     * @throws ConnectionFailed
     */
    public function insertRecord($tableName, $identifierField, array $values) {
      if (isset($identifierField)) {
        $values[$identifierField] = NULL;
      }
      if (isset($values) && is_array($values) && count($values) > 0) {
        $fieldString = '';
        $valueString = '';
        foreach ($values as $field => $value) {
          if (isset($identifierField) && $identifierField === $field) {
            continue;
          }
          $fieldString .= $this->quoteIdentifier($field).', ';
          if ($value === NULL) {
            $valueString .= 'NULL, ';
          } else {
            if (is_bool($value)) {
              $value = ($value ? '1' : '0');
            }
            $valueString .= $this->quoteString($value).', ';
          }
        }
        $sql = sprintf(
          'INSERT INTO %s (%s) VALUES (%s)',
          $this->quoteIdentifier($tableName),
          substr($fieldString, 0, -2),
          substr($valueString, 0, -2)
        );
        if ($this->execute($sql, DatabaseConnection::DISABLE_RESULT_CLEANUP)) {
          if (isset($identifierField)) {
            return $this->lastInsertId($tableName, $identifierField);
          }
          return TRUE;
        }
      }
      return FALSE;
    }

    private function getTableName($tableName, $usePrefix) {
      if ($usePrefix && isset($this->papaya()->options)) {
        $prefixString = $this->papaya()->options->get('PAPAYA_DB_TABLEPREFIX', 'papaya');
        if ('' !== $prefixString && 0 !== \strpos($tableName, $prefixString.'_')) {
          return $prefixString.'_'.$tableName;
        }
      }
      return $tableName;
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

    /**
     * DBMS spezific SQL condition string
     *
     * The passed filter parameter must not be empty,
     * to be converted into an array (if possible) and be passed
     * to the function getSQLCondition($filter).
     * If the SQL condition string can be created, it is returned
     *
     * @param array|string $filter sql condition array
     * @param mixed $value sql condition array
     * @param string $operator
     * @access public
     * @return mixed sql string or FALSE
     * @throws ConnectionFailed
     */
    public function getSQLCondition($filter, $value = NULL, $operator = '=') {
      if (!empty($filter)) {
        $this->connect();
        if (
          ($filterArray = $this->getConditionArray($filter, $value)) &&
          ($condition = new Condition\SQLCondition($this->getConnection(), $filterArray, $operator))
        ) {
          return (string)$condition;
        }
      }
      return FALSE;
    }

    /**
     * Gets the current used database syntax.
     *
     * @param string $mode
     * @return string current used database syntax
     * @throws ConnectionFailed
     */
    public function getProtocol($mode = self::MODE_WRITE) {
      return $this->getDSN($mode)->platform;
    }

    /**
     * set or read current master usage status
     *
     * @param boolean|NULL $forConnection optional, default value NULL
     * @access public
     * @return boolean use master connection only?
     */
    public function masterOnly($forConnection = NULL) {
      if (isset($forConnection)) {
        $this->_useMasterOnly = $forConnection;
      }
      return $this->_useMasterOnly;
    }

    /**
     * should the current read request go to the write connection?
     *
     * @param boolean $usable read connection usable
     * @access public
     * @return boolean
     */
    public function readOnly($usable) {
      if (!$usable) {
        return FALSE;
      }
      if ($this->masterOnly()) {
        return FALSE;
      }
      if (
        defined('PAPAYA_DATABASE_CLUSTER_SWITCH') &&
        (int)PAPAYA_DATABASE_CLUSTER_SWITCH === 2
      ) {
        return !$this->_dataModified;
      }
      return TRUE;
    }

    /**
     * Set data modified status to TRUE
     *
     * @return void
     */
    public function setDataModified() {
      $this->_dataModified = TRUE;
    }

    /**
     * @param $name
     * @return string
     * @throws ConnectionFailed
     */
    public function quoteIdentifier($name) {
      return $this->connect()->quoteIdentifier($name);
    }

    /**
     * @param $tableName
     * @param $identifierField
     * @return string
     * @throws ConnectionFailed
     */
    public function lastInsertId($tableName, $identifierField) {
      return $this->connect()->lastInsertId($tableName, $identifierField);
    }
  }
}
