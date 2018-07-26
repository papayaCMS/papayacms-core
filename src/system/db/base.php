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
* Data record as array with fieldname and Indizes
*/
if (!defined("DB_FETCHMODE_DEFAULT")) {
  define("DB_FETCHMODE_DEFAULT", 0);
}

/**
* Data record as numeric array
*/
if (!defined("DB_FETCHMODE_ORDERED")) {
  define("DB_FETCHMODE_ORDERED", 1);
}

/**
* Data record with fieldname as array key
*/
if (!defined("DB_FETCHMODE_ASSOC")) {
  define("DB_FETCHMODE_ASSOC", 2);
}

/**
* Database connection superclass
*
* @package Papaya-Library
* @subpackage Database
*/
abstract class dbcon_base extends base_object {
  /**
  * @var array $databaseConfiguration Configuration
  * @access private
  */
  var $databaseConfiguration;

  /**
  * @var resource|object $databaseConnection Connection-ID
  * @access public
  */
  var $databaseConnection = NULL;

  /**
  * @var object dbresult_base $lastResult last database result
  * @access public
  */
  var $lastResult;

  /**
  * @var string $lastSQLQuery last Query
  * @access public
  */
  var $lastSQLQuery = '';

  /**
  * Patterns matching select queries for rewrite (absolute record count)
  * @var array(string)
  */
  var $sqlSelectPatterns = array(
    '(^\s*(?:SELECT.*?)(\bFROM\b.*)(?:\bORDER\s+BY.*)$)si',
    '(^\s*(?:SELECT.*?)(\bFROM\b.*)$)si'
  );

  /**
   * Constructor
   *
   * @param array|\Papaya\Database\Source\Name $conf
   * @return \dbcon_base
   */
  public function __construct(\Papaya\Database\Source\Name $conf) {
    $this->databaseConfiguration = $conf;
  }

  /**
   * @param $explainQuery
   * @return mixed
   */
  abstract public function executeQuery($explainQuery);

  /**
  * extension needed for abstraction layer found
  * @return boolean
  */
  function extensionFound() {
    return FALSE;
  }

  /**
  * connect to Database
  *
  * @return boolean Erfolg
  * @access public
  */
  function connect() {
    return FALSE;
  }

  /**
   * Escape a string for database sql
   *
   * @deprecated
   * @param mixed $value
   * @return string
   */
  function escapeStr($value) {
    return $this->escapeString($value);
  }

  /**
  * Escape a string for database sql
  *
  * @param mixed $value Value to escape
  * @return string escaped value.
  */
  function escapeString($value) {
    if (is_bool($value)) {
      return $value ? 1 : 0;
    } elseif (is_int($value)) {
      return $value;
    } elseif (isset($value)) {
      return (string)$value;
    }
    return '';
  }

  /**
  * Eascpae and quote a string for the database sql
  *
  * @param mixed $value Value to escape
  * @return string escaped value.
  */
  function quoteString($value) {
    return "'".$this->escapeString($value)."'";
  }

  /**
   * Database request
   *
   * @param string $sql SQL string
   * @param integer $max data record limit
   * @param integer $offset startindex - data record limit
   * @param boolean $freeLastResult free last result (if here is one)
   * @param bool $enableCounter
   * @access public
   * @return dbresult_base|boolean|integer false or number of affected_rows or
   *                                                database result object
   */
  function query(
    $sql, $max = NULL, $offset = NULL, $freeLastResult = TRUE, $enableCounter = FALSE
  ) {
    $this->lastSQLQuery = '';
    $result = FALSE;
    return $result;
  }

  /**
  * Rewrite query to get record count of a limited query and execute it.
  *
  * @param string $sql SQL string
  * @access public
  * @return integer | FALSE record count or failure
  */
  function queryRecordCount($sql) {
    return FALSE;
  }

  /**
  * Rewrite the given sql query to get a count query.
  *
  * @param string $sql
  * @access public
  * @return string | FALSE
  */
  function getCountQuerySQL($sql) {
    foreach ($this->sqlSelectPatterns as $pattern) {
      if (preg_match($pattern, $sql, $match)) {
        return 'SELECT COUNT(*) '.$match[1];
      }
    }
    return FALSE;
  }

  /**
  * Insert new record
  *
  * @param string $table table
  * @param string $idField Index column
  * @param array $values values
  * @access public
  * @return boolean|integer FALSE or Id of new record
  */
  function insertRecord($table, $idField, $values = NULL) {
    $this->lastSQLQuery = '';
    return FALSE;
  }

  /**
  * Fetch the last inserted id
  *
  * @param string $table
  * @param string $idField
  * @return string|int|null
  */
  abstract function lastInsertId($table, $idField);

  /**
  * Insert many records at once
  *
  * @param string $table tablen
  * @param array $values values
  * @access public
  * @return boolean|integer FALSE or Id of new record
  */
  function insertRecords($table, $values) {
    $this->lastSQLQuery = '';
    return FALSE;
  }

  /**
  * Get SQL condition
  *
  * For each element in the filter array, a sql condition string is built. This strings
  * are concatenated and returned as one string if it is not empty.
  * The condition operator is only set to "OR" or "AND", if the field is a number
  * and the field value contains the string "OR" or "AND". Otherwise its empty, and the
  * strings are concatenated by the operator "AND".
  * Then the sql condition strings are created depending on the data type.
  * They always contain the operator at the beginning, followed by the field.
  * The field value is added in different ways at the end of the string.
  * If it is a boolean as number. If it is an array with more then one element,
  * each element is listed after an "IN" operator. Otherwise the value is
  * added unchanged as normal string.
  * If the filter is not set, the returned string will be '1 = 1',
  * so that you get the whole table entries.
  * Otherwise the string '1 = 0' will be returned,
  * so that you just get the headers of the fields without values.
  *
  * Examples:
  *
  *   array('field' => 'value', 'other_field' => 'value')
  *
  *   field = 'value' AND other_field = 'value'
  *
  *
  *
  *   array('field' => 'value', 'OR', 'other_field' => 'value')
  *
  *   field = 'value' OR other_field = 'value'
  *
  *
  *
  *   array('field' => 'value', 'AND', 'other_field' => 'value')
  *
  *   field = 'value' AND other_field = 'value'
  *
  *
  *
  *   array('field' => array('value1', 'value2', 'value3'))
  *
  *   field IN ('value1', 'value2', 'value3')
  *
  * @param array $filter
  * @param string $operator
  * @return string
  */
  function getSQLCondition($filter, $operator = '') {
    if (is_array($filter)) {
      $str = '';
      if (count($filter) > 0) {
        $op = '';
        foreach ($filter as $field => $value) {
          if (empty($value) || is_array($value) || strlen($value) > 10) {
            $conditionValue = '';
          } elseif (strlen($value) < 10) {
            $conditionValue = strtoupper(trim($value));
          } else {
            $conditionValue = '';
          }
          if (is_int($field) && ($conditionValue == 'OR' || $conditionValue == 'AND')) {
            if ($str != '') {
              $op = ' '.$conditionValue.' ';
            }
          } else {
            if (isset($value)) {
              $str .= $op.$this->getSqlConditionElement($field, $value, $operator);
            } else {
              $str .= $op.$this->getSqlConditionElement($field, $value, 'ISNULL');
            }
            $op = ' AND ';
          }
        }
      }
      if ($str != '') {
        return $str;
      } else {
        return '1=0';
      }
    } elseif (is_string($filter)) {
      return $filter;
    } elseif (!isset($filter)) {
      return '1=1';
    } else {
      return '1=0';
    }
  }

  protected function getSqlConditionElement($field, $value, $operator = '=') {
    if (is_array($value)) {
      if (empty($value)) {
        return '1 = 0';
      }
      $quoted = array();
      foreach ($value as $subValue) {
        $quoted[] = $this->quoteString($subValue);
      }
    } else {
      $quoted = $this->quoteString($value);
    }
    $escapedField = $this->escapeString($field);
    switch ($operator) {
    case 'ISNULL' :
      return $escapedField.' IS NULL';
    case 'LIKE' :
    case '>' :
    case '<' :
    case '>=' :
    case '<=' :
      if (is_array($quoted)) {
        $result = '';
        foreach ($quoted as $quotedValue) {
          $result .= ' OR '.$escapedField.' '.$operator.' '.$quotedValue;
        }
        return '('.substr($result, 4).')';
      } else {
        return $escapedField.' '.$operator.' '.$quoted;
      }
    case '!=' :
      if (is_array($quoted)) {
        return 'NOT('.$escapedField.' IN ('.implode(', ', $quoted).'))';
      } else {
        return $escapedField.' != '.$quoted;
      }
      break;
    case '=' :
    default :
      if (is_array($quoted)) {
        return $escapedField.' IN ('.implode(', ', $quoted).')';
      } else {
        return $escapedField.' = '.$quoted;
      }
    }
  }

  /**
  * Load a single record via filter
  *
  * @param string $table table name
  * @param array $fields field names, select all fields if empty
  * @param string $filter
  * @return array|FALSE
  */
  function loadRecord($table, array $fields, $filter) {
    $sql = "SELECT ";
    if (count($fields) > 0) {
      foreach ($fields as $fieldName) {
        $sql .= $this->escapeString(trim($fieldName)).', ';
      }
      $sql = substr($sql, 0, -2);
    } else {
      $sql .= '*';
    }
    $sql .= " FROM ".$this->escapeString($table);
    $sql .= " WHERE ".$this->getSQLCondition($filter);
    if ($res = $this->query($sql, 1)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        return $row;
      }
    }
    return FALSE;
  }

  /**
  * Change database records
  *
  * @param string $table Table
  * @param array $values values
  * @param string $filter condition
  * @access public
  * @return dbresult_base|boolean|integer false or number of affected_rows or
  *                                                database result object
  */
  function updateRecord($table, $values, $filter) {
    $this->lastSQLQuery = '';
    return FALSE;
  }

  /**
  * delete database record
  *
  * @param string $table table
  * @param string $filter condition
  * @access public
  * @return dbresult_base|boolean|integer false or number of affected_rows or
  *                                                database result object
  */
  function deleteRecord($table, $filter) {
    $this->lastSQLQuery = '';
    return FALSE;
  }

  /**
  * Get all table names
  *
  * @access public
  * @return array
  */
  function queryTableNames() {
    return array();
  }

  /**
  * table structur as array
  *
  * @param string $tableName table name
  * @param string $tablePrefix Prefix
  * @access public
  * @return array
  */
  function queryTableStructure($tableName, $tablePrefix = '') {
    return array();
  }

  /**
  * Create table
  *
  * @param string $tableData
  * @param string $tablePrefix
  * @access public
  * @return boolean
  */
  function createTable($tableData, $tablePrefix) {
    return FALSE;
  }

  /**
  * Add field
  *
  * @param string $table
  * @param array $fieldData
  * @access public
  * @return boolean
  */
  function addField($table, $fieldData) {
    return FALSE;
  }

  /**
  * Change field
  *
  * @param string $table
  * @param array $fieldData
  * @access public
  * @return boolean
  */
  function changeField($table, $fieldData) {
    return FALSE;
  }

  /**
  * Delete field
  *
  * @param string $table
  * @param string $field
  * @access public
  * @return boolean
  */
  function dropField($table, $field) {
    return FALSE;
  }

  /**
  * Add index
  *
  * @param string $table
  * @param array $index
  * @access public
  * @return boolean
  */
  function addIndex($table, $index) {
    return FALSE;
  }

  /**
  * Change index
  *
  * @param string $table
  * @param array $index
  * @param boolean $dropCurrent
  * @access public
  * @return boolean
  */
  function changeIndex($table, $index, $dropCurrent = TRUE) {
    return FALSE;
  }

  /**
  * Delete index
  *
  * @param string $table
  * @param string $name
  * @access public
  * @return boolean
  */
  function dropIndex($table, $name) {
    return FALSE;
  }

  /**
  * DBMS specific SQL source
  *
  * @param string $function sql function
  * @param array $params params
  * @access public
  * @return mixed sql string or FALSE
  */
  function getSQLSource($function, array $params = NULL) {
    return FALSE;
  }

  /**
  * Get sql function through parameters
  *
  * @param array $params
  * @access public
  * @return string
  */
  function getSQLFunctionParams(array $params = NULL) {
    if (empty($params)) {
      return '';
    }
    $result = '';
    for ($i = 0; $i < count($params); $i += 2) {
      $result .= $this->getSQLFunctionParam(
        $params[$i], isset($params[$i + 1]) ? $params[$i + 1] : TRUE
      ).',';
    }
    return substr($result, 0, -1);
  }

  /**
  * Get sql function through parameter
  *
  * @param string $value
  * @param boolean $escaping
  * @access public
  * @return string
  */
  function getSQLFunctionParam($value, $escaping) {
    if ($escaping === FALSE) {
      return $value;
    } else {
      return "'".$this->escapeString($value)."'";
    }
  }


  /**
  * Compare the field structure
  *
  * @param array $xmlField
  * @param array $databaseField
  * @access public
  * @return boolean different
  */
  function compareFieldStructure($xmlField, $databaseField) {
    return FALSE;
  }

  /**
  * Compare the key/index structure
  *
  * @param array $xmlKey
  * @param array $databaseKey
  * @access public
  * @return boolean different
  */
  function compareKeyStructure($xmlKey, $databaseKey) {
    return FALSE;
  }

  /**
   * Declare close function
   * @return bool
   */
  abstract function close();
}

/**
* DB abstraction layer - result object
*
* @package Papaya-Library
* @subpackage Database
* @author Thomas Weinert <info@papaya-cms.com>
*/
class dbresult_base extends base_object implements \Papaya\Database\Result {
  /**
  * @var dbcon_base $connection connection object
  * @access private
  */
  var $connection = NULL;
  /**
  * @var resource $result result ressources
  * @access private
  */
  var $result = NULL;

  /**
  * @var string $query database query string
  * @access private
  */
  var $query = '';

  /**
  * @var boolean $hasLimit data record limit
  * @access public
  */
  var $hasLimit = FALSE;

  /**
  * @var integer $limitMax data record limit amount
  */
  var $limitMax = NULL;

  /**
  * @var integer $limitOffset data record limit offset
  */
  var $limitOffset = NULL;

  /**
  * @var integer $recNo current data record
  */
  var $recNo = 0;

  /**
  * @var string $_absCount absolute number
  */
  var $_absCount = 0;

  /**
   * Constructor
   *
   * @param dbcon_base $connection connection object
   * @param resource|object $result result ressource
   * @param $query
   */
  function __construct($connection, $result, $query) {
    $this->connection = $connection;
    $this->result = $result;
    $this->query = $query;
  }

  /**
  * Return and Iterator for the result, allowing to use foreach on it.
  *
  * @return \Iterator
  */
  public function getIterator() {
    return new \Papaya\Database\Result\Iterator($this);
  }

  /**
  * Destruktor
  * @access public
  */
  function free() {
    if (isset($this->result) && is_resource($this->result)) {
      unset($this->result);
    }
  }

  /**
  * data record as array
  *
  * @param integer $mode modus (numeric oder fieldname)
  * @access public
  * @return array data record
  */
  public function fetchRow($mode = DB_FETCHMODE_DEFAULT) {
    return FALSE;
  }

  /**
  * fetch data from field
  *
  * @param mixed $fieldIndex Index/Name of field
  * @access public
  * @return string
  */
  function fetchField($fieldIndex = 0) {
    if (is_int($fieldIndex)) {
      $data = $this->fetchRow(DB_FETCHMODE_ORDERED);
      return $data[$fieldIndex];
    } elseif (is_string($fieldIndex)) {
      $data = $this->fetchRow(DB_FETCHMODE_ASSOC);
      return $data[$fieldIndex];
    }
    return FALSE;
  }

  /**
  * Put data records in an array
  *
  * @param array $row Array for data records
  * @param integer $mode Modus (numeric oder fieldname)
  * @access public
  * @return integer|boolean 0 or FALSE
  */
  function fetchInto($row, $mode = DB_FETCHMODE_DEFAULT) {
    $row = $this->fetchRow($mode);
    if (isset($row) && is_array($row)) {
      return 0;
    } else {
      return NULL;
    }
  }

  /**
  * Acquire number of database records
  *
  * @return boolean FALSE
  * @access public
  */
  function count() {
    return FALSE;
  }

  /**
  * Acquire absolute number of database records
  *
  * @return integer|FALSE
  * @access public
  */
  function absCount() {
    if ($this->_absCount === -1) {
      $absCount = $this->connection->queryRecordCount($this->query);
      $this->_absCount = (FALSE === $absCount) ? FALSE : (int)$absCount;
    }
    return $this->_absCount;
  }

  /**
  * Move record pointer
  *
  * @param integer $index new position
  * @access public
  * @return boolean success ?
  */
  function seek($index) {
    return FALSE;
  }

  /**
  * Move database record pointer to first record
  *
  * @access public
  * @return boolean success ?
  */
  function seekFirst() {
    return $this->seek(0);
  }

  /**
  * Move record pointer to last record
  *
  * @access public
  * @return boolean success ?
  */
  function seekLast() {
    if (FALSE !== ($count = $this->count())) {
      return $this->seek($count);
    }
    return FALSE;
  }

  /**
  * Set record limiter
  *
  * @param integer $max data record limit
  * @param integer $offset start index data record limit
  * @return int
  */
  function setLimit($max = NULL, $offset = NULL) {
    if ($max > 0) {
      $this->hasLimit = TRUE;
      $this->limitMax = (int)$max;
      $this->limitOffset = (isset($offset)) ? (int)$offset : NULL;
    } else {
      $this->hasLimit = FALSE;
      unset($this->limitMax);
      unset($this->limitOffset);
    }
    return 0;
  }

  /**
  * Get an a database explain if possible
  *
  * @access public
  * @return NULL|\PapayaMessageContextInterface
  */
  public function getExplain() {
    return NULL;
  }
}
