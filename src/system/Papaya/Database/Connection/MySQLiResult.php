<?php

namespace Papaya\Database\Connection {

  use Papaya\Database\Connection as DatabaseConnection;
  use Papaya\Database\SQLStatement;
  use Papaya\Database\Statement as DatabaseStatement;

  /**
   * database result for mysqli
   *
   * @package Papaya-Library
   * @subpackage Database
   */
  class MySQLiResult extends AbstractResult {

    private $_mysqli;
    private $_recordNumber;

    public function __construct(
      DatabaseConnection $connection,
      DatabaseStatement $statement,
      \mysqli_result $dbmsResult
    ) {
      parent::__construct($connection, $statement);
      $this->_mysqli = $dbmsResult;
    }

    /**
     * destructor
     *
     * Free memory, unset self and resultID
     *
     * @access public
     */
    public function free() {
      if ($this->_mysqli instanceof \mysqli_result) {
        $this->_mysqli->free();
        $this->_mysqli = NULL;
      }
    }

    /**
     * @return bool
     */
    public function isValid() {
      return ($this->_mysqli instanceof \mysqli_result);
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
          $result = $this->_mysqli->fetch_assoc();
        } elseif ($mode === self::FETCH_ORDERED) {
          $result = $this->_mysqli->fetch_row();
        } else {
          $result = $this->_mysqli->fetch_array();
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
        return $this->_mysqli->num_rows;
      }
      return FALSE;
    }

    /**
     * Search index
     *
     * Move record pointer to given index
     * next call of mysqli->fetch_row() returns wanted value
     *
     * @param integer $index
     * @access public
     * @return boolean
     */
    public function seek($index) {
      if ($this->isValid() && $this->_mysqli->data_seek($index)) {
        $this->_recordNumber = $index;
        return TRUE;
      }
      return FALSE;
    }

    /**
     * Compile database explain for SELECT query
     *
     * @access public
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
      if ($dbmsResult && count($dbmsResult) > 0) {
        $explain = new \Papaya\Message\Context\Table('Explain');
        $explain->setColumns(
          [
            'id' => 'Id',
            'select_type' => 'Select Type',
            'table' => 'Table',
            'type' => 'Type',
            'possible_keys' => 'Possible Keys',
            'key' => 'Key',
            'key_len' => 'Key Length',
            'ref' => 'Reference',
            'rows' => 'Rows',
            'Extra' => 'Extra'
          ]
        );
        while ($row = $dbmsResult->fetchAssoc()) {
          $explain->addRow($row);
        }
        $dbmsResult->free();
        return $explain;
      }
      return NULL;
    }
  }
}
