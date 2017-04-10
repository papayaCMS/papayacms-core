<?php
/**
* Papaya Database Access
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Database
* @version $Id: Access.php 39408 2014-02-27 16:00:49Z weinert $
*/

/**
* Papaya Database Access
* @package Papaya-Library
* @subpackage Database
*
* @method boolean addField() addField(string $table, array $fieldData)
* @method boolean addIndex() addIndex(string $table, array $index)
* @method boolean changeField() changeField(string $table, array $fieldData)
* @method boolean changeIndex() changeIndex(string $table, array $index)
* @method void close() close()
* @method true compareFieldStructure() compareFieldStructure(array $xmlField, array $databaseField)
* @method boolean compareKeyStructure() compareKeyStructure()
* @method boolean createTable() createTable(string $tableData, string $tablePrefix)
* @method void debugNextQuery() debugNextQuery(integer $count = 1)
* @method integer deleteRecord() deleteRecord(string $table, mixed $filter, mixed $value = NULL)
* @method boolean dropField() dropField(string $table, string $field)
* @method boolean dropIndex() dropIndex(string $table, string $name)
* @method void enableAbsoluteCount() enableAbsoluteCount()
* @method void emptyTable() emptyTable(string $table)
* @method string escapeString() escapeString(mixed $value)
* @method string quoteString() quoteString(mixed $value)
* @method string getProtocol() getProtocol()
* @method string getSqlSource() getSqlSource(string $function, array $params)
* @method string getSqlCondition() getSqlCondition(array $filter, $value = NULL, $operator = '=')
* @method integer|NULL insertRecord() insertRecord(string $table, string $idField, array $values = NULL)
* @method boolean insertRecords() insertRecords(string $table, array $values)
* @method integer lastInsertId() lastInsertId(string $table, string $idField)
* @method boolean|PapayaDatabaseResult query() query(string $sql, integer $max = NULL, integer $offset = NULL, boolean $readOnly = TRUE)
* @method boolean|PapayaDatabaseResult queryFmt() queryFmt(string $sql, array $values, integer $max = NULL, integer $offset = NULL, boolean $readOnly = TRUE)
* @method boolean|PapayaDatabaseResult queryFmtWrite() queryFmtWrite(string $sql, array $values)
* @method boolean|PapayaDatabaseResult queryWrite() queryWrite(string $sql)
* @method FALSE|array loadRecord() loadRecord(string $table, array $values, mixed $filter, mixed $value = NULL)
* @method integer updateRecord() updateRecord(string $table, array $values, mixed $filter, mixed $value = NULL)
* @method array queryTableNames() queryTableNames()
* @method array queryTableStructure() queryTableStructure(string $tableName)
*/
class PapayaDatabaseAccess extends PapayaObject {

  /**
  * calling object
  * @var PapayaObject
  */
  private $_owner = NULL;

  /**
  * a table names helper object
  * @var PapayaContentTables
  */
  private $_tables = NULL;

  /**
  * Database connection URI for read queries
  * @var string
  */
  private $_uriRead = NULL;
  /**
  * Database connection URI for write queries
  * @var string
  */
  private $_uriWrite = NULL;

  /**
  * Stored database connector object
  */
  private $_connector = NULL;

  /**
  * Data was modified (query on write connection)
  * @var boolean
  */
  private $_dataModified = FALSE;

  /**
  * Use only master (write) connection
  * @var boolean
  */
  private $_useMasterOnly = FALSE;

  /**
  * Member variable for a user defined error handler, if set this overrides the default
  * error handling
  *
  * @var NULL|callback|Closure
  */
  private $_errorHandler = NULL;

  /**
  * Delegate function
  *
  * FALSE = read function
  * TRUE = write function
  *
  * @var array
  */
  private $_delegateFunctions = array(
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
  );

  /**
  * Map lowercase versions of the deletegate functions to the real ones
  * @var array
  */
  private $_functionMapping = array();

  /**
  * The owner is used later to determine which object has uses the database access
  * (for example in logging).
  *
  * @param object $owner calling object
  * @param string|NULL $readUri
  * @param string|NULL $writeUri
  * @return PapayaDatabaseAccess
  */
  public function __construct($owner, $readUri = NULL, $writeUri = NULL) {
    $this->_owner = $owner;
    $this->_uriRead = $readUri;
    $this->_uriWrite = $writeUri;
    foreach ($this->_delegateFunctions as $name => $modifies) {
      $this->_functionMapping[strtolower($name)] = $name;
    }
  }

  /**
  * Get database connection (implicit create)
  *
  * @var PapayaDatabaseManager $databaseManager
  * @return db_simple
  */
  public function getDatabaseConnector() {
    if (isset($this->_connector)) {
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
  * @param db_simple $connector
  */
  public function setDatabaseConnector($connector) {
    $this->_connector = $connector;
  }

  /**
  * Get table name with prefix (if needed)
  *
  * @param string $tableName
  * @param boolean $usePrefix
  * @return boolean
  */
  public function getTableName($tableName, $usePrefix = TRUE) {
    return $this->tables()->get($tableName, $usePrefix);
  }

  /**
  * Get a timestamp for create/modified fields. This method is basically here so you can mock
  * it for tests.
  *
  * @return integer
  */
  public function getTimestamp() {
    return time();
  }

  /**
  * Get table name mapper object
  *
  * @param PapayaContentTables $tables
  * @return PapayaContentTables
  */
  public function tables(PapayaContentTables $tables = NULL) {
    if (isset($tables)) {
      $this->_tables = $tables;
    } elseif (is_null($this->_tables)) {
      $this->_tables = new PapayaContentTables();
    }
    return $this->_tables;
  }

  /**
  * set or read current master usage status
  *
  * @param boolean|NULL $forObject optional, default value NULL
  * @param boolean|NULL $forConnection optional, default value NULL
  * @access public
  * @return boolean use master connection only?
  */
  public function masterOnly($forObject = NULL, $forConnection = NULL) {
    if (isset($forObject)) {
      $this->_useMasterOnly = (bool)$forObject;
    }
    if (isset($forConnection)) {
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
  * @param boolean $useable read connection possible
  * @access public
  * @return boolean
  */
  public function readOnly($useable) {
    if (!$useable) {
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
      return $this->getDatabaseConnector()->readOnly($useable);
      break;
    case 1 : //object context
      return !($this->_dataModified);
      break;
    }
    return TRUE;
  }

  /**
  * Set data modified status (switch to write connection)
  * @return void
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
   * @throws BadMethodCallException
   * @return mixed
   */
  public function __call($functionName, $arguments) {
    if (isset($this->_delegateFunctions[$functionName])) {
      $delegateFunction = $functionName;
    } elseif (isset($this->_functionMapping[strtolower($functionName)])) {
      $delegateFunction = $this->_functionMapping[strtolower($functionName)];
    } else {
      $delegateFunction = NULL;
    }
    if (isset($delegateFunction) &&
        isset($this->_delegateFunctions[$delegateFunction])) {
      $connector = $this->getDatabaseConnector();
      if (!($connector instanceof db_simple)) {
        throw new BadMethodCallException(
          sprintf(
            'Invalid function call. Can not fetch database connector.'
          )
        );
      }
      if (method_exists($connector, $delegateFunction)) {
        array_unshift($arguments, $this->_owner);
        try {
          $result = call_user_func_array(array($connector, $delegateFunction), $arguments);
          if ($result &&
              $this->_delegateFunctions[$delegateFunction]) {
            $this->setDataModified();
          }
          return $result;
        } catch (PapayaDatabaseException $exception) {
          $this->_handleDatabaseException($exception);
          return FALSE;
        }
      } else {
        throw new BadMethodCallException(
          sprintf(
            'Invalid function call. Method %s::%s does not exist.',
            is_object($connector) ? get_class($connector) : gettype($connector),
            $functionName
          )
        );
      }
    } else {
      throw new BadMethodCallException(
        sprintf(
          'Invalid function call. Method %s::%s does not exist.',
          get_class($this),
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
   * @param callback|Closure|FALSE $callback
   * @throws InvalidArgumentException
   * @return callback|Closure|NULL void
   */
  public function errorHandler($callback = NULL) {
    if (isset($callback)) {
      if (FALSE === $callback) {
        $this->_errorHandler = NULL;
      } elseif (is_callable($callback)) {
        $this->_errorHandler = $callback;
      } else {
        throw new InvalidArgumentException('Given error callback is not callable.');
      }
    }
    return $this->_errorHandler;
  }

  /**
  * Call the given eror handler callback or if none is defined dipatch a log message.
  *
  * @param PapayaDatabaseException $exception
  */
  private function _handleDatabaseException(PapayaDatabaseException $exception) {
    $errorHandler = $this->errorHandler();
    if (isset($errorHandler)) {
      call_user_func($errorHandler, $exception);
    } else {
      $mapSeverity = array(
        PapayaDatabaseException::SEVERITY_INFO => PapayaMessage::SEVERITY_INFO,
        PapayaDatabaseException::SEVERITY_WARNING => PapayaMessage::SEVERITY_WARNING,
        PapayaDatabaseException::SEVERITY_ERROR => PapayaMessage::SEVERITY_ERROR,
      );
      $logMsg = new PapayaMessageLog(
        PapayaMessageLogable::GROUP_DATABASE,
        $mapSeverity[$exception->getSeverity()],
        'Database #'.$exception->getCode().': '.$exception->getMessage()
      );
      $logMsg->context()->append(new PapayaMessageContextBacktrace(3));
      if ($exception instanceof PapayaDatabaseExceptionQuery) {
        $logMsg->context()->append(new PapayaMessageContextText($exception->getStatement()));
      }
      $this->papaya()->messages->dispatch($logMsg);
    }
  }
}
