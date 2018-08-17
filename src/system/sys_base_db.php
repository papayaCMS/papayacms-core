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
* Object class for database access
*
* all other classes which have database access must be inherited from this one
*
* @package Papaya
* @subpackage Database
*
* @method boolean databaseAddField(string $table, array $fieldData)
* @method boolean databaseAddIndex(string $table, array $index)
* @method boolean databaseChangeField(string $table, array $fieldData)
* @method boolean databaseChangeIndex(string $table, array $index)
* @method void databaseClose()
* @method true databaseCompareFieldStructure(array $xmlField, array $databaseField)
* @method boolean databaseCompareKeyStructure()
* @method boolean databaseCreateTable(string $tableData, string $tablePrefix)
* @method void databaseDebugNextQuery(integer $count = 1)
* @method integer databaseDeleteRecord(string $table, $filter, mixed $value = NULL)
* @method boolean databaseDropField(string $table, string $field)
* @method boolean databaseDropIndex(string $table, string $name)
* @method void databaseEnableAbsoluteCount()
* @method mixed databaseEmptyTable(string $table)
* @method string databaseEscapeString(mixed $value)
* @method string databaseGetProtocol()
* @method string databaseGetSqlSource(string $function, array $params = NULL)
* @method string databaseGetSqlCondition(array $filter, $value = NULL)
* @method integer|NULL databaseInsertRecord(string $table, $idField, array $values = NULL)
* @method integer|boolean databaseInsertRecords(string $table, array $values)
* @method boolean|integer|\Papaya\Database\Result databaseQuery(string $sql, integer $max = NULL, integer $offset = NULL, boolean $readOnly = TRUE)
* @method boolean|integer|\Papaya\Database\Result databaseQueryFmt(string $sql, array $values, integer $max = NULL, integer $offset = NULL, boolean $readOnly = TRUE)
* @method boolean|integer|\Papaya\Database\Result databaseQueryFmtWrite(string $sql, array $values)
* @method boolean|integer|\Papaya\Database\Result databaseQueryWrite(string $sql)
* @method integer|boolean databaseUpdateRecord(string $table, array $values, $filter, mixed $value = NULL)
* @method array databaseQueryTableNames()
* @method array databaseQueryTableStructure(string $tableName)
* @method string databaseGetTableName(string $tablename, $usePrefix = TRUE)
*/
class base_db extends base_object {

  /**
  * Database access object
  *
  * @var \Papaya\Database\Access $_databaseAccessObject
  */
  var $_databaseAccessObject = NULL;

  /**
  * Database URI, default value ist the option PAPAYA_DB_URI
  * @var string $databaseURI
  */
  var $databaseURI = NULL;
  /**
  * Database URI for insert/update/..., default value ist the option PAPAYA_DB_URI_WRITE
  * @var string $databaseURIWrite
  */
  var $databaseURIWrite = NULL;

  /**
   * Set database access object
   *
   * @param \Papaya\Database\Access $databaseAccessObject
   * @return \Papaya\Database\Access
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
        $this, $this->databaseURI, $this->databaseURIWrite
      );
      $this->_databaseAccessObject->papaya($this->papaya());
    }
    return $this->_databaseAccessObject;
  }

  /**
   * Delegate calls to "database*" methods to the database access object
   *
   * @param string $functionName
   * @param array $arguments
   * @throws BadMethodCallException
   * @return mixed
   */
  public function __call($functionName, $arguments) {
    if (substr($functionName, 0, 8) == 'database') {
      $delegateFunction = strtolower($functionName[8]).substr($functionName, 9);
      $access = $this->getDatabaseAccess();
      return call_user_func_array(array($access, $delegateFunction), $arguments);
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
  * Old function name for backwards compatibility
  *
  * @param mixed $value Value to escape
  * @access public
  * @return string escaped value.
  */
  function escapeStr($value) {
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
  function checkDataModified($newValues, $dbValues) {
    if (isset($newValues) && is_array($newValues)) {
      foreach ($newValues as $key => $val) {
        if (isset($dbValues[$key]) && $dbValues[$key] != $newValues[$key]) {
          return TRUE;
        } elseif (!isset($dbValues[$key])) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }
}
