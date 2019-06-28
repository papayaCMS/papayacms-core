<?php

namespace Papaya\Database\Connection {

  use Papaya\Database\Connection as DatabaseConnection;
  use Papaya\Database\Statement as DatabaseStatement;

  /**
 * DB-Abstractionslayer - result object - SQLite
 * @package Papaya-Library
 * @subpackage Database
 */
class SQLite3Result extends AbstractResult {

  /**
   * @var \SQLite3Result
   */
  private $_sqlite3;

  /**
   * @var
   */
  private $_recordNumber = 0;

  private $_recordCount = -1;

  public function __construct(
    DatabaseConnection $connection,
    DatabaseStatement $statement,
    \SQLite3Result $dbmsResult
  ) {
    parent::__construct($connection, $statement);
    $this->_sqlite3 = $dbmsResult;
  }

  /**
   * destructor
   *
   * Free memory, unset self and resultID
   *
   * @access public
   */
  public function free() {
    if ($this->isValid()) {
      try {
        $this->_sqlite3->finalize();
      } catch (\Exception $e) {
      }
    }
    $this->_sqlite3 = NULL;
    $this->_recordCount = -1;
  }

  public function isValid() {
    return isset($this->_sqlite3) && ($this->_sqlite3 instanceof \SQLite3Result);
  }

  /**
   * Fetch next row of result
   *
   * @param integer $mode line return modus
   * @access public
   * @return mixed FALSE or next line
   */
  public function fetchRow($mode = self::FETCH_ORDERED) {
    if ($this->isValid()) {
      if ($mode === self::FETCH_ASSOC) {
        $result = $this->_sqlite3->fetchArray(SQLITE3_ASSOC);
        if (isset($result) && is_array($result)) {
          $data = array();
          foreach ($result as $key => $val) {
            if (strpos($key, '.') !== FALSE) {
              $field = substr($key, strpos($key, '.') + 1);
            } else {
              $field = $key;
            }
            $data[$field] = $val;
          }
          $result = $data;
        }
      } else {
        $result = $this->_sqlite3->fetchArray(SQLITE3_NUM);
      }
      if (isset($result) && is_array($result)) {
        $this->_recordNumber++;
      }
      return $result;
    }
    return FALSE;
  }

  /**
   * Number rows affected by query
   *
   * @access public
   * @return int
   */
  public function count() {
    if ($this->_recordCount >= 0) {
      return $this->_recordCount;
    }
    if ($this->isValid()) {
      $this->_recordCount = 0;
      while ($this->fetchRow()) {
        $this->_recordCount++;
      }
      $this->_sqlite3->reset();
    }
    return $this->_recordCount;
  }

  /**
   * Search index
   *
   * Move record pointer to given index
   * next call of pg_fetch_row() returns wanted value
   *
   * @param integer $index
   * @access public
   * @return boolean
   */
  public function seek($index) {
    if ($this->isValid()) {
      if ($index < $this->_recordNumber) {
        $this->_sqlite3->reset();
        $this->_recordNumber = 0;
      }
      while ($this->_recordNumber < $index) {
        $this->_sqlite3->fetchArray(SQLITE3_NUM);
        ++$this->_recordNumber;
      }
      return ($this->_recordNumber === $index);
    }
    return FALSE;
  }

  /**
   * Compile database explain for SELECT query
   *
   * @return NULL|\Papaya\Message\Context\Data
   */
  public function getExplain() {
    $explainQuery = 'EXPLAIN '.$this->query;
    $result = $this->connection->execute(
      $explainQuery, \Papaya\Database\Connection::DISABLE_RESULT_CLEANUP
    );
    if ($result && count($result) > 0) {
      $explain = new \Papaya\Message\Context\Table('Explain');
      while ($row = $result->fetchRow(self::FETCH_ORDERED)) {
        $explain->addRow($row);
      }
      return $explain;
    }
    return NULL;
  }
}

}
