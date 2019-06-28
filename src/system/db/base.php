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

use Papaya\Database;

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
abstract class dbcon_base extends Database\Connection\AbstractConnection {

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
  public function query(
    $sql, $max = NULL, $offset = NULL, $freeLastResult = TRUE, $enableCounter = FALSE
  ) {
    if ($freeLastResult) {
      $this->cleanup();
    }
    return FALSE;
  }

  protected function cleanup() {
    if (
      $this->lastResult instanceof Database\Result
    ) {
      $this->lastResult->free();
    }
  }

  /**
  * Rewrite the given sql query to get a count query.
  *
  * @param string $sql
  * @access public
  * @return string | FALSE
  */
  public function getCountQuerySQL($sql) {
    foreach ($this->sqlSelectPatterns as $pattern) {
      if (preg_match($pattern, $sql, $match)) {
        return 'SELECT COUNT(*) '.$match[1];
      }
    }
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
}

/**
* DB abstraction layer - result object
*
* @package Papaya-Library
* @subpackage Database
* @author Thomas Weinert <info@papaya-cms.com>
*/
class dbresult_base implements \Papaya\Database\Result {
  /**
  * @var dbcon_base $connection connection object
  */
  protected $connection;
  /**
  * @var resource $result result resource
  */
  protected $result;

  /**
  * @var string $query database query string
  */
  protected $query;

  /**
  * @var boolean $hasLimit data record limit
  * @access public
  */
  public $hasLimit = FALSE;

  /**
  * @var integer $limitMax data record limit amount
  */
  public $limitMax;

  /**
  * @var integer $limitOffset data record limit offset
  */
  public $limitOffset;

  /**
  * @var string $_absCount absolute number
  */
  private $_absCount;

  /**
   * @var int|null
   */
  protected $_recordNumber;

  /**
   * Constructor
   *
   * @param dbcon_base $connection connection object
   * @param resource|object $result result resource
   * @param string $query
   * @param int $absCount
   */
  function __construct($connection, $result, $query, $absCount = -1) {
    $this->connection = $connection;
    $this->result = $result;
    $this->query = $query;
    $this->_absCount = $absCount;
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
  * Destructor
  * @access public
  */
  public function free() {
    if (isset($this->result) && is_resource($this->result)) {
      unset($this->result);
    }
  }

  /**
  * data record as array
  *
  * @param integer $mode mode (numeric oder field name)
  * @access public
  * @return array|FALSE data record
  */
  public function fetchRow($mode = DB_FETCHMODE_DEFAULT) {
    return FALSE;
  }


  /**
  * data record as array
  *
  * @access public
  * @return array data record
  */
  public function fetchAssoc() {
    return $this->fetchRow(DB_FETCHMODE_ASSOC);
  }

  /**
  * fetch data from field
  *
  * @param mixed $fieldIndex Index/Name of field
  * @access public
  * @return string
  */
  public function fetchField($fieldIndex = 0) {
    if (is_int($fieldIndex)) {
      $data = $this->fetchRow(DB_FETCHMODE_ORDERED);
      return $data[$fieldIndex];
    }
    if (is_string($fieldIndex)) {
      $data = $this->fetchRow(DB_FETCHMODE_ASSOC);
      return $data[$fieldIndex];
    }
    return FALSE;
  }

  /**
  * Acquire number of database records
  *
  * @return boolean FALSE
  * @access public
  */
  public function count() {
    return FALSE;
  }

  /**
   * @param int $absCount
   */
  public function setAbsCount($absCount) {
    $this->_absCount = (int)$absCount;
  }

  /**
  * Acquire absolute number of database records
  *
  * @return integer|FALSE
  * @access public
  */
  public function absCount() {
    if ($this->_absCount === -1) {
      $absCount = $this->queryRecordCount($this->query);
      $this->_absCount = (FALSE === $absCount) ? FALSE : (int)$absCount;
    }
    return $this->_absCount;
  }

  /**
  * Rewrite query to get record count of a limited query and execute it.
  *
  * @param string $sql SQL string
  * @access public
  * @return integer | FALSE record count or failure
  */
  private function queryRecordCount($sql) {
    if (
      ($countSql = $this->connection->getCountQuerySql($sql)) &&
      ($dbmsResult = $this->connection->execute($countSql))
    ) {
      $result = $dbmsResult->fetchField();
      $dbmsResult->free();
      return $result;
    }
    return FALSE;
  }

  /**
  * Move record pointer
  *
  * @param integer $index new position
  * @access public
  * @return boolean success ?
  */
  public function seek($index) {
    return FALSE;
  }

  /**
  * Move database record pointer to first record
  *
  * @access public
  * @return boolean success ?
  */
  public function seekFirst() {
    return $this->seek(0);
  }

  /**
  * Move record pointer to last record
  *
  * @access public
  * @return boolean success ?
  */
  public function seekLast() {
    if (FALSE !== ($count = $this->count())) {
      return $this->seek($count);
    }
    return FALSE;
  }

  /**
  * Get an a database explain if possible
  *
  * @access public
  * @return NULL|\Papaya\Message\Context\Data
  */
  public function getExplain() {
    return NULL;
  }
}
