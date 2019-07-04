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
 * @package Papaya
 * @subpackage Database
 * @deprecated
 */
class base_db extends base_object {

  /**
   * Database access object
   *
   * @var \Papaya\Database\Access $_databaseAccessObject
   */
  private $_databaseAccessObject;

  /**
   * Database URI, default value ist the option PAPAYA_DB_URI
   *
   * @var string $databaseURI
   */
  protected $databaseURI;
  /**
   * Database URI for insert/update/..., default value ist the option PAPAYA_DB_URI_WRITE
   *
   * @var string $databaseURIWrite
   */
  protected $databaseURIWrite;

  /**
   * Set database access object
   *
   * @param \Papaya\Database\Access $databaseAccessObject
   */
  public function setDatabaseAccess(\Papaya\Database\Access $databaseAccessObject) {
    $this->_databaseAccessObject = $databaseAccessObject;
  }

  /**
   * Get database access object
   *
   * @return \Papaya\Database\Access
   */
  public function getDatabaseAccess() {
    if (!isset($this->_databaseAccessObject)) {
      $this->_databaseAccessObject = new \Papaya\Database\Access(
        $this->databaseURI, $this->databaseURIWrite
      );
      $this->_databaseAccessObject->papaya($this->papaya());
    }
    return $this->_databaseAccessObject;
  }

  /**
   * Old function name for backwards compatibility
   *
   * @param mixed $value Value to escape
   * @access public
   * @return string escaped value.
   * @deprecated
   */
  public function escapeStr($value) {
    return $this->databaseEscapeString($value);
  }

  /**
   * Compare new values with current values (from db)
   *
   * @param array $newValues
   * @param array $dbValues
   * @access public
   * @return boolean
   */
  public function checkDataModified($newValues, $dbValues) {
    if (isset($newValues) && is_array($newValues)) {
      foreach ($newValues as $key => $val) {
        if (isset($dbValues[$key]) && $dbValues[$key] !== $newValues[$key]) {
          return TRUE;
        }
        if (!isset($dbValues[$key])) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  public function databaseGetTableName($tableName, $usePrefix = TRUE) {
    return $this->getDatabaseAccess()->getTableName($tableName, $usePrefix);
  }

  public function databaseQuery($sql, $limit = NULL, $offset = NULL, $readOnly = TRUE) {
    return $this->getDatabaseAccess()->query($sql, $limit, $offset, $readOnly);
  }

  public function databaseQueryFmt($sql, $values, $limit = NULL, $offset = NULL, $readOnly = TRUE) {
    return $this->getDatabaseAccess()->queryFmt($sql, $values, $limit, $offset, $readOnly);
  }

  public function databaseQueryFmtWrite($sql, $values) {
    return $this->databaseQueryFmt($sql, $values, NULL, NULL, FALSE);
  }

  public function databaseQueryWrite($sql) {
    return $this->databaseQuery($sql, NULL, NULL, FALSE);
  }

  public function databaseClose() {
    $this->getDatabaseAccess()->disconnect();
  }

  public function databaseDebugNextQuery($count = 1) {
    $this->getDatabaseAccess()->debugNextQuery($count);
  }

  public function databaseEnableAbsoluteCount() {
    $this->getDatabaseAccess()->enableAbsoluteCount();
  }

  public function databaseDeleteRecord($tableName, $filter, $filterValue = NULL) {
    return $this->getDatabaseAccess()->deleteRecord($tableName, $filter, $filterValue);
  }

  public function databaseEmptyTable($tableName) {
    return $this->getDatabaseAccess()->emptyTable($tableName);
  }

  public function databaseEscapeString($literal) {
    return $this->getDatabaseAccess()->escapeString($literal);
  }

  public function databaseGetProtocol() {
    return $this->getDatabaseAccess()->getProtocol();
  }

  public function databaseGetSQLSource($functionName, array $parameters = NULL) {
    return $this->getDatabaseAccess()->getSQLSource($functionName, $parameters);
  }

  public function databaseGetSQLCondition($filter, $value = NULL) {
    return $this->getDatabaseAccess()->getSQLCondition($filter, $value);
  }

  public function databaseInsertRecord($tableName, $idField, array $values = NULL) {
    return $this->getDatabaseAccess()->insertRecord($tableName, $idField, $values);
  }

  public function databaseInsertRecords($tableName, array $values) {
    return $this->getDatabaseAccess()->insertRecords($tableName, $values);
  }

  public function databaseUpdateRecord($tableName, array $values, $filter, $filterValue = NULL) {
    return $this->getDatabaseAccess()->updateRecord($tableName, $values, $filter, $filterValue);
  }
}
