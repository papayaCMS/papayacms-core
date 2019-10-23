<?php

namespace Papaya\Database\Condition {

  use Papaya\BaseObject\Interfaces\StringCastable;
  use Papaya\Database\Connection;

  /**
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
   */
  class SQLCondition implements StringCastable {

    const EQUAL = '=';
    const ISNULL = 'ISNULL';
    const LIKE = 'LIKE';
    const GREATER = '>';
    const LESS = '<';
    const GREATER_OR_EQUAL = '>=';
    const LESS_OR_EQUAL = '<=';
    const NOT_EQUAL = '!=';

    /**
     * @var Connection
     */
    private $_connection;
    /**
     * @var array|string
     */
    private $_filter;
    /**
     * @var string
     */
    private $_operator;

    /**
     * @param Connection $connection
     * @param NULL|array $filter
     * @param string $operator
     */
    public function __construct(Connection $connection, array $filter = NULL, $operator = self::EQUAL) {
      $this->_connection = $connection;
      $this->_filter = $filter;
      $this->_operator = $operator;
    }

    public function __toString() {
      return $this->getCondition($this->_filter, $this->_operator);
    }


    /**
     * @param array $filter
     * @param string $operator
     * @return string
     */
    private function getCondition(array $filter = NULL, $operator = self::EQUAL) {
      if (NULL !== $filter) {
        $str = '';
        if (count($filter) > 0) {
          $op = '';
          foreach ($filter as $field => $value) {
           if (empty($value) || is_array($value) || strlen($value) >= 10) {
              $conditionValue = '';
            } else {
              $conditionValue = strtoupper(trim($value));
            }
            if (is_int($field) && ($conditionValue === 'OR' || $conditionValue === 'AND')) {
              if ($str !== '') {
                $op = ' '.$conditionValue.' ';
              }
            } else {
              if (isset($value)) {
                $str .= $op.$this->getConditionElement($field, $value, $operator);
              } else {
                $str .= $op.$this->getConditionElement($field, $value, 'ISNULL');
              }
              $op = ' AND ';
            }
          }
        }
        if ($str !== '') {
          return $str;
        }
        return '1=0';
      }
      return '1=1';
    }

    protected function getConditionElement($field, $value, $operator) {
      if (is_array($value)) {
        if (empty($value)) {
          return '1 = 0';
        }
        $quoted = [];
        foreach ($value as $subValue) {
          $quoted[] = $this->_connection->quoteString($subValue);
        }
      } elseif (is_bool($value)) {
        $quoted = $this->_connection->quoteString($value ? '1' : '0');
      } else {
        $quoted = $this->_connection->quoteString($value);
      }
      $escapedField = $this->_connection->quoteIdentifier($field);
      switch ($operator) {
      case self::ISNULL :
        return $escapedField.' IS NULL';
      case self::LIKE :
      case self::GREATER :
      case self::LESS :
      case self::GREATER_OR_EQUAL :
      case self::LESS_OR_EQUAL :
        if (is_array($quoted)) {
          $result = '';
          foreach ($quoted as $quotedValue) {
            $result .= ' OR '.$escapedField.' '.$operator.' '.$quotedValue;
          }
          return '('.substr($result, 4).')';
        }
        return $escapedField.' '.$operator.' '.$quoted;
      case self::NOT_EQUAL :
        if (is_array($quoted)) {
          return 'NOT('.$escapedField.' IN ('.implode(', ', $quoted).'))';
        }
        return $escapedField.' != '.$quoted;
        break;
      case self::EQUAL :
      default :
        if (is_array($quoted)) {
          return $escapedField.' IN ('.implode(', ', $quoted).')';
        }
        return $escapedField.' = '.$quoted;
      }
    }
  }
}
