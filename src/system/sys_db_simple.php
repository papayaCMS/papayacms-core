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
* DB - abstraction layer
*
* @package Papaya
* @subpackage Database
*/
class db_simple extends base_object {

  /**
  * Id to keep track of requests for query log
  * @var string $requestId
  */
  private static $requestId = '';

  /**
  * Internal absolute query counter
  * @var integer
  */
  private static $queryCounterClass = 0;

  /**
  * counter for queries using this object
  * @var integer $queryCounterObject
  */
  private $queryCounterObject = 0;

  /**
  * database URI
  * @var array:string $databaseURIs
  */
  var $databaseURIs = array(
    'read' => '',
    'write' => ''
  );

  /**
  * database configuration
  * @var array:string $databaseConfiguration
  */
  var $databaseConfiguration = array(
    'read' => NULL,
    'write' => NULL
  );

  /**
  * database connection for selects
  * @var array:dbcon_base $databaseObjects
  */
  var $databaseObjects = array(
    'read' => NULL,
    'write' => NULL
  );


  /**
  * database
  * @var string $dbsyntaxArr
  */
  var $dbsyntaxArr = array('mysql', 'mysqli', 'pgsql', 'sqlite');

  /**
  * time spent on queries using this object
  * (Needs PAPAYA_DBG_DATABASE_EXPLAIN or PAPAYA_DBG_DATABASE_SLOWQUERIES activated.)
  * @var float $querytimeSum
  */
  var $queryTimeSum = 0;

  /**
  * Debug the next n queries
  * @var integer $debugCounter
  */
  var $debugCounter = 0;

  /**
  * Enable Calculation of absolute records for the next n Queries.
  * @var integer $enableAbsoluteCounter
  */
  var $enableAbsoluteCounter = 0;

  /**
  *
  * @var boolean $databaseDataModified
  */
  var $dataModified = FALSE;

  /**
  *
  * @var boolean $databaseStatusMasterOnly
  */
  var $useMasterOnly = FALSE;

  /**
   * @var bool
   */
  private $_dataModified = FALSE;


  /**
  * connect to database
  *
  * @param object|PapayaDatabaseAccess $object calling object
  * @param boolean $readOnly use read connection
  * @return dbcon_base
  */
  function connect($object, $readOnly = TRUE) {
    $mode = ($readOnly) ? 'read' : 'write';
    if (!(
          isset($this->databaseObjects[$mode]) &&
          is_object($this->databaseObjects[$mode]) &&
          $this->getConnection($mode)->connect(NULL, $readOnly)
        )) {
      $this->createConnection($object, $readOnly);
    }
    if (!(isset($this->databaseObjects[$mode]) && is_object($this->databaseObjects[$mode]))) {
      unset($this->databaseObjects[$mode]);
      return FALSE;
    } elseif ($this->getConnection($mode)->extensionFound()) {
      return $this->getConnection($mode)->connect(NULL, $readOnly);
    } else {
      unset($this->databaseObjects[$mode]);
      return FALSE;
    }
  }

  /**
  * Close database connection
  * @return void
  */
  function close() {
    if ($this->papaya()->options->get('PAPAYA_LOG_RUNTIME_DATABASE', FALSE)) {
      if ($this->queryTimeSum > 0) {
        $message = 'Database Query Count: '.(int)$this->queryCounterObject. ' in '.
          PapayaUtilDate::periodToString($this->queryTimeSum);
      } else {
        $message = 'Database Query Count: '.(int)$this->queryCounterObject;
      }
      $this->papaya()->messages->dispatch(
        new \PapayaMessageLog(
          PapayaMessageLogable::GROUP_DEBUG,
          PapayaMessage::SEVERITY_DEBUG,
          $message
        )
      );
    }
    if ($connection = $this->getConnection('read')) {
      $connection->close();
    }
    if ($connection = $this->getConnection('write')) {
      $connection->close();
    }
  }

  /**
   * establish connection
   *
   * @param object $object calling object
   * @param bool $readOnly
   * @throws Papaya\Database\Exception\Connect
   * @return boolean Success
   */
  function createConnection($object, $readOnly = TRUE) {
    $mode = ($readOnly) ? 'read' : 'write';
    if ((!$readOnly) &&
        isset($this->databaseURIs['write']) &&
        trim($this->databaseURIs['write']) != '' &&
        $this->databaseURIs['write'] != 'PAPAYA_DB_URI_WRITE' &&
        $this->databaseURIs['write'] != $this->databaseURIs['read']) {
      $uriString = $this->databaseURIs['write'];
    } elseif (!$readOnly &&
              isset($this->databaseObjects['read']) &&
              is_object($this->databaseObjects['read'])) {
      $this->databaseObjects['write'] = $this->databaseObjects['read'];
      $uriString = $this->databaseURIs['read'];
    } else {
      $uriString = $this->databaseURIs['read'];
    }
    $this->databaseConfiguration[$mode] = new \Papaya\Database\Source\Name($uriString);
    if (isset($this->databaseObjects[$mode]) &&
        is_object($this->databaseObjects[$mode])) {
      if ($this->getConnection($mode)->extensionFound()) {
        $this->getConnection($mode)->connect();
        return TRUE;
      }
    } else {
      $className = 'dbcon_'.$this->databaseConfiguration[$mode]->api;
      if (defined('PAPAYA_DBG_DEVMODE') && PAPAYA_DBG_DEVMODE) {
          $found = class_exists($className);
      } else {
        $found = @class_exists($className);
      }
      if ($found) {
        $this->databaseObjects[$mode] = new $className($this->databaseConfiguration[$mode]);
        $this->getConnection($mode)->extensionFound();
        $this->getConnection($mode)->connect($object);
      } else {
        throw new \Papaya\Database\Exception\Connect(
          sprintf(
            'Abstraction "%s" could not be loaded.',
            $this->databaseConfiguration[$mode]->api
          )
        );
      }
    }
    return FALSE;
  }

  /**
  * Debug next query, set debug counter
  *
  * @param object $object calling object
  * @param integer $count optional, default value 1
  * @access public
  */
  function debugNextQuery($object, $count = 1) {
    $this->debugCounter = $count;
  }

  /**
  * Calculate the absolute record count for the next query (if limited).
  *
  * @param object $object calling object
  * @access public
  * @return void
  */
  function enableAbsoluteCount($object) {
    $this->enableAbsoluteCounter = 1;
  }

  /**
  * Escape value / string
  *
  * @param object $object calling object
  * @param mixed $value value to escape
  * @param boolean $readOnly use read connection
  * @access public
  * @return string
  */
  function escapeString($object, $value, $readOnly = TRUE) {
    $this->connect($object, $readOnly);
    $mode = ($readOnly) ? 'read' : 'write';
    return $this->getConnection($mode)->escapeString($value);
  }

  /**
  * Escape and quote value / string
  *
  * @param object $object calling object
  * @param mixed $value value to escape
  * @param boolean $readOnly use read connection
  * @access public
  * @return string
  */
  function quoteString($object, $value, $readOnly = TRUE) {
    $this->connect($object, $readOnly);
    $mode = ($readOnly) ? 'read' : 'write';
    return $this->getConnection($mode)->quoteString($value);
  }

  /**
  * Process a query
  *
  * @param object $object calling object
  * @param string $sql
  * @param integer $max limit data sets
  * @param integer $offset limit data sets - Start
  * @param boolean $readOnly optional, default TRUE
  * @access public
  * @return boolean|integer|dbresult_base on failure FALSE; on success a
  *   database result object or number of affected rows or TRUE if the number
  *   of affected rows is 0
  */
  function query($object, $sql, $max = NULL, $offset = NULL, $readOnly = TRUE) {
    return $this->_executeQuery($object, $sql, $max, $offset, $readOnly);
  }

  /**
  * Process a query
  *
  * @param object $object calling object
  * @param string $sql
  * @param integer $max limit data sets
  * @param integer $offset limit data sets - Start
  * @param boolean $readOnly optional, default TRUE
  * @access public
  * @return boolean|integer|dbresult_base on failure FALSE; on success a
  *   database result object or number of affected rows or TRUE if the number
  *   of affected rows is 0
  */
  private function _executeQuery($object, $sql, $max = NULL, $offset = NULL, $readOnly = TRUE) {
    $error = NULL;
    //global query counter
    self::$queryCounterClass++;
    //object query counter
    $this->queryCounterObject++;

    $options = $this->papaya()->options;

    $measureTime = FALSE;
    if ($this->debugCounter > 0 ||
        $options->get('PAPAYA_LOG_RUNTIME_DATABASE', FALSE) ||
        $options->get('PAPAYA_LOG_DATABASE_QUERY', 0) > 0 ||
        $options->get('PAPAYA_QUERYLOG', 0) > 0) {
      $measureTime = TRUE;
    }

    $mode = ($readOnly) ? 'read' : 'write';
    $this->connect($object, $readOnly);
    if ($readOnly && $options->get('PAPAYA_LOG_DATABASE_CLUSTER_VIOLATIONS', FALSE)) {
      if (preg_match('~^\s*(INSERT|UPDATE|ALTER|CREATE|DROP)~i', $sql)) {
        $logMessage = new \PapayaMessageLog(
          PapayaMessageLogable::GROUP_DATABASE,
          PapayaMessage::SEVERITY_WARNING,
          'Detected write query on read connection.'
        );
        $logMessage
          ->context()
          ->append(new \PapayaMessageContextText($sql))
          ->append(new \PapayaMessageContextBacktrace(1));
        $this->papaya()->messages->dispatch($logMessage);
      }
    }
    if ($this->enableAbsoluteCounter > 0) {
      $enableAbsoluteCounter = TRUE;
      $this->enableAbsoluteCounter--;
    } else {
      $enableAbsoluteCounter = FALSE;
    }
    $timeStart = microtime(TRUE);
    $result = $this->getConnection($mode)->query(
      $sql, $max, $offset, TRUE, $enableAbsoluteCounter
    );
    if ($measureTime) {
      $this->_logQueryExecution(
        $timeStart,
        array(
          'object' => $object,
          'sql' => $sql,
          'max' => $max,
          'offset' => $offset,
          'readOnly' => $readOnly,
          'result' => $result
        )
      );
    }
    if (is_int($result) && $result == 0) {
      return TRUE;
    } else {
      return $result;
    }
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
    $this->queryTimeSum += $timeDelta;
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
          if (in_array(self::$queryCounterClass, $queryNumbers)) {
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
          'Query #%d on read connection from class "%s"',
          self::$queryCounterClass,
          get_class($query['object'])
        );
      } else {
        $caption = sprintf(
          'Query #%d on write connection from class "%s"',
          self::$queryCounterClass,
          get_class($query['object'])
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
          /** @var \PapayaDatabaseResult $queryResult */
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
        $backtrace = new \PapayaMessageContextBacktrace(9);
      }
      if ($dispatchLogMessage) {
        $logMessage = new \PapayaMessageLog(
          PapayaMessageLogable::GROUP_DATABASE,
          PapayaMessage::SEVERITY_DEBUG,
          $caption
        );
        $logMessage->context()->append(new \PapayaMessageContextRuntime($timeStart, $timeStop));
        if (isset($counter)) {
          $logMessage->context()->append(new \PapayaMessageContextText($counter));
        }
        $logMessage->context()->append(
          new \PapayaMessageContextVariable(array('sql' => $query['sql']), 3, 99999)
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
          self::$requestId = md5(uniqid(rand()));
        }
        $logData = array(
          'query_request' => self::$requestId,
          'query_timestamp' => time(),
          'query_class' => get_class($query['object']),
          'query_count' => self::$queryCounterClass,
          'query_conn' => $query['readOnly'] ? 'read' : 'write',
          'query_time' => $timeDelta * 1000,
          'query_content' => $query['sql'],
          'query_hash' => md5($query['sql']),
          'query_uri' => empty($_SERVER['REQUEST_URI']) ? '' : $_SERVER['REQUEST_URI'],
        );
        if ($query['result'] instanceof PapayaDatabaseResult) {
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
          if (isset($explain) && $explain instanceof PapayaMessageContextInterfaceString) {
            $logData['query_explain'] = $explain->asString();
          }
        }
        try {
          $this->insertRecord($this, PAPAYA_DB_TBL_LOG_QUERIES, NULL, $logData);
        } catch (PapayaDatabaseException $e) {
        }
      }
    }
  }

  /**
  * Escape values for current database connection, insert into statement and execute statement
  *
  * @param object $object calling object
  * @param string $sql
  * @param array|string $values
  * @param integer $max limit data sets
  * @param integer $offset limit data sets - Start
  * @param boolean $readOnly use read connection
  * @return integer Result Index
  */
  function queryFmt($object, $sql, $values, $max = NULL, $offset = NULL, $readOnly = TRUE) {
    $this->connect($object, $readOnly);
    return $this->_executeQuery(
      $object, $this->_prepareQuery($object, $sql, $values, $readOnly), $max, $offset, $readOnly
    );
  }

  /**
   * Prepare a query by excaping each value and compiling all to a string
   *
   * @param object $object calling object
   * @param string $sql
   * @param array|string $values
   * @param boolean $readOnly use read connection
   * @return string
   */
  private function _prepareQuery($object, $sql, $values, $readOnly) {
    $sqlParams = array();
    if (isset($values) && is_array($values)) {
      foreach ($values as $sqlParam) {
        $sqlParams[] = $this->escapeString($object, $sqlParam, $readOnly);
      }
      $sql = vsprintf($sql, $sqlParams);
    } elseif (isset($values)) {
      $sql = sprintf($sql, $this->escapeString($object, $values, $readOnly));
    }
    return $sql;
  }

  /**
  * Execute sql query that changes data
  *
  * @param object $object calling object
  * @param string $sql
  * @return integer Result Index
  */
  function queryWrite($object, $sql) {
    return $this->_executeQuery($object, $sql, NULL, NULL, FALSE);
  }

  /**
  * Escape values for current database connection, insert into statement and execute statement
  * that changes data
  *
  * @param object $object calling object
  * @param string $sql
  * @param array|string $values
  * @return integer Result Index
  */
  function queryFmtWrite($object, $sql, $values) {
    $this->connect($object, FALSE);
    return $this->_executeQuery(
      $object, $this->_prepareQuery($object, $sql, $values, FALSE), NULL, NULL, FALSE
    );
  }

  /**
  * create new record, return id, set default values
  *
  * @param object $object calling object
  * @param string $table table
  * @param string $idField fields with ID
  * @param array $values default values
  * @access public
  * @return integer data sets number
  */
  function insertRecord($object, $table, $idField, $values = NULL) {
    $this->connect($object, FALSE);
    return $this->getConnection('write')->insertRecord($table, $idField, $values);
  }

  /**
   * Fetch the last inserted id
   *
   * @param $object
   * @param string $table
   * @param string $idField
   * @return int|float|string|boolean|null
   */
  function lastInsertId($object, $table, $idField) {
    $this->connect($object, FALSE);
    return $this->getConnection('write')->lastInsertId($table, $idField);
  }

  /**
   * insert records
   *
   * @param object $object calling object
   * @param string $table table
   * @param array $values default values
   * @throws InvalidArgumentException
   * @access public
   * @return boolean
   */
  function insertRecords($object, $table, array $values) {
    if (empty($values)) {
      throw new InvalidArgumentException('Argument $values array is empty, nothing to insert.');
    }
    $this->connect($object, FALSE);
    return $this->getConnection('write')->insertRecords($table, $values);
  }

  /**
  * load s single record
  *
  * @param object $object calling object
  * @param string $table table
  * @param array $fields field names
  * @param array|string $filter condition
  * @param mixed $value condition value if $filter is field name
  * @param boolean $readOnly query is read only (does not change data)
  * @access public
  * @return integer number changed records
  */
  function loadRecord($object, $table, array $fields, $filter, $value = NULL, $readOnly = FALSE) {
    $this->connect($object, FALSE);
    $mode = ($readOnly) ? 'read' : 'write';
    return $this->getConnection($mode)->loadRecord(
      $table, $fields, $this->getConditionArray($filter, $value)
    );
  }

  /**
   * change data sets
   *
   * if the passed array with default values is empty, an exception is thrown.
   * Otherwise, a connection to the database is build and the function
   * updateRecord(...) in the dbcon file of the used database is called and returned.
   *
   * @param object $object calling object
   * @param string $table table
   * @param array $values default values
   * @param array|string $filter condition
   * @param mixed $value condition value if $filter is field name
   * @throws InvalidArgumentException
   * @access public
   * @return integer number changed records
   * @see dbcon_base::getSQLCondition()
   */
  function updateRecord($object, $table, array $values, $filter, $value = NULL) {
    if (empty($values)) {
      throw new InvalidArgumentException('Argument $values array is empty, nothing to update.');
    }
    $this->connect($object, FALSE);
    return $this->getConnection('write')->updateRecord(
      $table, $values, $this->getConditionArray($filter, $value)
    );
  }

  /**
  * delete data sets
  *
  * @param object $object calling object
  * @param string $table table
  * @param string|array $filter condition
  * @param mixed $value condition value if $filter is field name
  * @access public
  * @return integer number deleted records
  */
  function deleteRecord($object, $table, $filter, $value = NULL) {
    $this->connect($object, FALSE);
    return $this->getConnection('write')->deleteRecord(
      $table, $this->getConditionArray($filter, $value)
    );
  }

  /**
   * delete data sets
   *
   * @param object $object calling object
   * @param string $table table
   * @return int
   */
  function emptyTable($object, $table) {
    return $this->deleteRecord($object, $table, NULL);
  }

  /**
  * Get all table names
  *
  * @param object $object calling object
  * @access public
  * @return array
  */
  function queryTableNames($object) {
    $this->connect($object);
    return $this->getConnection('read')->queryTableNames();
  }

  /**
  * Return table structur as arrays
  *
  * @param object $object calling object
  * @param string $tableName table name
  * @param string $tablePrefix Prefix
  * @access public
  * @return array
  */
  function queryTableStructure($object, $tableName, $tablePrefix = '') {
    $this->connect($object);
    return $this->getConnection('read')->queryTableStructure(
      $tableName, $tablePrefix
    );
  }


  /**
  * Create given table
  *
  * @param object $object calling object
  * @param string $tableData
  * @param string $tablePrefix
  * @access public
  * @return boolean
  */
  function createTable($object, $tableData, $tablePrefix) {
    $this->connect($object, FALSE);
    return $this->getConnection('write')->createTable($tableData, $tablePrefix);
  }

  /**
  * Add field
  *
  * @param object $object calling object
  * @param string $table
  * @param array $fieldData
  * @access public
  * @return boolean
  */
  function addField($object, $table, $fieldData) {
    $this->connect($object, FALSE);
    return $this->getConnection('write')->addField($table, $fieldData);
  }

  /**
  * Change field
  *
  * @param object $object calling object
  * @param string $table
  * @param array $fieldData
  * @access public
  * @return boolean
  */
  function changeField($object, $table, $fieldData) {
    $this->connect($object, FALSE);
    return $this->getConnection('write')->changeField($table, $fieldData);
  }

  /**
  * Delete field
  *
  * @param object $object calling object
  * @param string $table
  * @param string $field
  * @access public
  * @return boolean
  */
  function dropField($object, $table, $field) {
    $this->connect($object, FALSE);
    return $this->getConnection('write')->dropField($table, $field);
  }

  /**
  * Add index
  *
  * @param object $object calling object
  * @param string $table
  * @param array $index
  * @access public
  * @return boolean
  */
  function addIndex($object, $table, $index) {
    $this->connect($object, FALSE);
    return $this->getConnection('write')->addIndex($table, $index);
  }

  /**
  * Change index
  *
  * @param object $object calling object
  * @param string $table
  * @param array $index
  * @access public
  * @return boolean
  */
  function changeIndex($object, $table, $index) {
    $this->connect($object, FALSE);
    return $this->getConnection('write')->changeIndex($table, $index);
  }

  /**
   * DBMS spezific SQL source
   *
   * @param object $object calling object
   * @param string $function sql function
   * @param mixed ...$param
   * @access public
   * @return mixed sql string or FALSE
   */
  function getSQLSource($object, $function) {
    $this->connect($object, FALSE);
    $params = func_get_args();
    array_splice($params, 0, 2);
    if ($str = $this->getConnection('write')->getSQLSource($function, $params)) {
      return $str;
    }
    return FALSE;
  }

  /**
  * Convert different $filter arguments to an array
  * @param string|array|NULL $filter
  * @param mixed $value
  * @return array|NULL
  */
  function getConditionArray($filter, $value = NULL) {
    if (empty($filter)) {
      return NULL;
    } elseif (is_string($filter)) {
      return array($filter => $value);
    } else {
      return $filter;
    }
  }

  /**
   * DBMS spezific SQL condition string
   *
   * The passed filter parameter must not be empty,
   * to be converted into an array (if possible) and be passed
   * to the function getSQLCondition($filter).
   * If the SQL condition string can be created, it is returned
   *
   * @param object $object calling object
   * @param array $filter sql condition array
   * @param mixed $value sql condition array
   * @param string $operator
   * @access public
   * @return mixed sql string or FALSE
   */
  function getSQLCondition($object, $filter, $value = NULL, $operator = '=') {
    if (!empty($filter)) {
      $this->connect($object, TRUE);
      $filter = $this->getConditionArray($filter, $value);
      if ($str = $this->getConnection('read')->getSQLCondition($filter, $operator)) {
        return $str;
      }
    }
    return FALSE;
  }

  /**
  * Delete index
  *
  * @param object $object calling object
  * @param string $table
  * @param string $name
  * @access public
  * @return boolean
  */
  function dropIndex($object, $table, $name) {
    $this->connect($object, FALSE);
    return $this->getConnection('write')->dropIndex($table, $name);
  }


  /**
  * Compare field definition in XML and database
  *
  * @param object $object calling object
  * @param array $xmlField
  * @param array $databaseField
  * @access public
  * @return boolean
  */
  function compareFieldStructure($object, $xmlField, $databaseField) {
    $this->connect($object, FALSE);
    return $this->getConnection('write')->compareFieldStructure(
      $xmlField, $databaseField
    );
  }

  /**
  * Compare key structure
  *
  * @param object $object calling object
  * @param array $xmlKey
  * @param array $databaseKey
  * @access public
  * @return boolean
  */
  function compareKeyStructure($object, $xmlKey, $databaseKey) {
    $this->connect($object, FALSE);
    return $this->getConnection('write')->compareKeyStructure($xmlKey, $databaseKey);
  }

  /**
  * Gets the current used database syntax.
  *
  * @param string $type either 'read' or 'write'
  * @return string current used database syntax
  */
  function getProtocol($type = 'write') {
    if ($type == 'write' && isset($this->databaseConfiguration[$type])) {
      return $this->databaseConfiguration[$type]->platform;
    }
    return $this->databaseConfiguration['read']->platform;
  }

  /**
  * set or read current master usage status
  *
  * @param boolean|NULL $forConnection optional, default value NULL
  * @access public
  * @return boolean use master connection only?
  */
  function masterOnly($forConnection = NULL) {
    if (isset($forConnection)) {
      $this->useMasterOnly = $forConnection;
    }
    return $this->useMasterOnly;
  }

  /**
  * should the current read request go to the write connection?
  *
  * @param boolean $useable read connection usable
  * @access public
  * @return boolean
  */
  function readOnly($useable) {
    if (!$useable) {
      return FALSE;
    }
    if ($this->masterOnly()) {
      return FALSE;
    }
    if (defined('PAPAYA_DATABASE_CLUSTER_SWITCH') &&
        PAPAYA_DATABASE_CLUSTER_SWITCH == 2) {
      return !($this->_dataModified);
    }
    return TRUE;
  }

  /**
  * Set data modified status to TRUE
  * @return void
  */
  function setDataModified() {
    $this->_dataModified = TRUE;
  }

  /**
   * Fetch connection for use
   *
   * @param string $for
   * @return dbcon_base
   */
  private function getConnection($for = 'read') {
    return isset($this->databaseObjects[$for]) ? $this->databaseObjects[$for] : NULL;
  }
}
