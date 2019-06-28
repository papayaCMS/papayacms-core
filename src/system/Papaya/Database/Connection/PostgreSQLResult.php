<?php

namespace Papaya\Database\Connection {

  use Papaya\Database\Connection as DatabaseConnection;
  use Papaya\Database\SQLStatement;
  use Papaya\Database\Statement as DatabaseStatement;
  use Papaya\Message\Context;

  /**
   * DB-Abstractionslayer - result object PostgreSQL
   *
   * @package Papaya-Library
   * @subpackage Database
   */
  class PostgreSQLResult extends AbstractResult {

    private $_postgreSQL;
    private $_recordNumber;

    public function __construct(
      DatabaseConnection $connection,
      DatabaseStatement $statement,
      $dbmsResult
    ) {
      parent::__construct($connection, $statement);
      $this->_postgreSQL = $dbmsResult;
    }

    public function isValid() {
      return isset($this->_postgreSQL) && is_resource($this->_postgreSQL);
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
        pg_free_result($this->_postgreSQL);
      }
      $this->_postgreSQL = NULL;
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
          $result = pg_fetch_assoc($this->_postgreSQL);
        } elseif ($mode === self::FETCH_ORDERED) {
          $result = pg_fetch_row($this->_postgreSQL);
        } else {
          $result = pg_fetch_array($this->_postgreSQL);
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
     * @return mixed number of rows or FALSE
     */
    public function count() {
      if ($this->isValid()) {
        return pg_num_rows($this->_postgreSQL);
      }
      return FALSE;
    }

    /**
     * Search index
     *
     * Move record pointer to given index
     * next call of pg_fetch_row() returns wanted value
     *
     * @param $index
     * @access public
     * @return boolean
     */
    public function seek($index) {
      if ($this->isValid() && pg_result_seek($this->_postgreSQL, $index)) {
        $this->_recordNumber = $index;
        return TRUE;
      }
      return FALSE;
    }

    /**
     * Compile database explain for SELECT query
     *
     * @return NULL|\Papaya\Message\Context\Data
     */
    public function getExplain() {
      $statement = $this->getStatement();
      $explainQuery = new SQLStatement(
        'EXPLAIN '.$statement->getSQLString(),
        $statement->getSQLParameters()
      );
      $dbmsResult = $this->getConnection()->execute(
        $explainQuery, DatabaseConnection::DISABLE_RESULT_CLEANUP
      );
      if ($dbmsResult) {
        $explain = [];
        while ($row = $dbmsResult->fetchRow(self::FETCH_ORDERED)) {
          $explain[] = $row[0];
        }
        if (!empty($explain)) {
          return new Context\Items('Explain', $explain);
        }
      }
      return NULL;
    }
  }
}
