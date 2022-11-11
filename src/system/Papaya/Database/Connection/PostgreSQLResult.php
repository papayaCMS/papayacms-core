<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Database\Connection {

  use Papaya\Database\Connection as DatabaseConnection;
  use Papaya\Database\SQLStatement;
  use Papaya\Database\Statement as DatabaseStatement;
  use Papaya\Message\Context as MessageContext;

  /**
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

    /**
     * @return bool
     */
    public function isValid() {
      return isset($this->_postgreSQL) && is_resource($this->_postgreSQL);
    }

    public function free() {
      if ($this->isValid()) {
        pg_free_result($this->_postgreSQL);
      }
      $this->_postgreSQL = NULL;
    }

    /**
     * Fetch next row of result
     *
     * @param integer $mode
     * @return array|NULL
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
      return NULL;
    }

    /**
     * @return int
     */
    public function count(): int {
      if ($this->isValid()) {
        return pg_num_rows($this->_postgreSQL);
      }
      return 0;
    }

    /**
     * Move record pointer to given index
     * next call of pg_fetch_row() returns wanted value
     *
     * @param int $index
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
     * @return NULL|MessageContext\Data
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
          return new MessageContext\Items('Explain', $explain);
        }
      }
      return NULL;
    }
  }
}
