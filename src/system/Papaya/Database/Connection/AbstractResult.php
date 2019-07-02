<?php

namespace Papaya\Database\Connection {

  use Papaya\Database\Connection as DatabaseConnection;
  use Papaya\Database\Result as DatabaseResult;
  use Papaya\Database\Result\Iterator as DatabaseResultIterator;
  use Papaya\Database\Statement as DatabaseStatement;
  use Papaya\Database\Statement\Count as CountStatement;

  abstract class AbstractResult implements DatabaseResult {

    /**
     * @var DatabaseConnection
     */
    private $_connection;

    /**
     * @var DatabaseStatement
     */
    private $_statement;

    /**
     * @var int
     */
    private $_absoluteCount = -1;

    public function __construct(DatabaseConnection $connection, DatabaseStatement $statement) {
      $this->_connection = $connection;
      $this->_statement = $statement;
    }

    /**
     * @return DatabaseConnection
     */
    public function getConnection() {
      return $this->_connection;
    }

    /**
     * @return DatabaseStatement
     */
    public function getStatement() {
      return $this->_statement;
    }

    /**
     * Return and Iterator for the result, allowing to use foreach on it.
     *
     * @return \Iterator
     */
    public function getIterator() {
      return new DatabaseResultIterator($this);
    }

    /**
     * data record as array
     *
     * @access public
     * @return array data record
     */
    public function fetchAssoc() {
      return $this->fetchRow(self::FETCH_ASSOC);
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
        $data = $this->fetchRow();
        return $data[$fieldIndex];
      }
      if (is_string($fieldIndex)) {
        $data = $this->fetchRow(self::FETCH_ASSOC);
        return $data[$fieldIndex];
      }
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
     * @param int $absoluteCount
     */
    public function setAbsoluteCount($absoluteCount) {
      $this->_absoluteCount = (int)$absoluteCount;
    }

    /**
     * Acquire absolute number of database records
     *
     * @return integer|FALSE
     * @access public
     */
    public function absCount() {
      if ($this->_absoluteCount === -1) {
        $absoluteCount = $this->queryRecordCount();
        $this->_absoluteCount = (FALSE === $absoluteCount) ? FALSE : (int)$absoluteCount;
      }
      return $this->_absoluteCount;
    }

    /**
     * Rewrite query to get record count of a limited query and execute it.
     *
     * @return integer | FALSE record count or failure
     */
    private function queryRecordCount() {
      if (
        ($connection = $this->getConnection()) &&
        ($countSql = new CountStatement($connection, $this->getStatement())) &&
        ($dbmsResult = $connection->execute($countSql))
      ) {
        $result = $dbmsResult->fetchField();
        $dbmsResult->free();
        return $result;
      }
      return FALSE;
    }
  }
}