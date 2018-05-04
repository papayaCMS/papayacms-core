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
* Papaya Database Object, superclass for classes with database access
* @package Papaya-Library
* @subpackage Database
*
* @method boolean databaseAddField() databaseAddField(string $table, array $fieldData)
* @method boolean databaseAddIndex() databaseAddIndex(string $table, array $index)
* @method boolean databaseChangeField() databaseChangeField(string $table, array $fieldData)
* @method boolean databaseChangeIndex() databaseChangeIndex(string $table, array $index)
* @method void databaseClose() databaseClose()
* @method true databaseCompareFieldStructure() databaseCompareFieldStructure(array $xmlField, array $databaseField)
* @method boolean databaseCompareKeyStructure() databaseCompareKeyStructure()
* @method boolean databaseCreateTable() databaseCreateTable(string $tableData, string $tablePrefix)
* @method void databaseDebugNextQuery() databaseDebugNextQuery(integer $count = 1)
* @method int databaseDeleteRecord() databaseDeleteRecord(string $table, mixed $filter, mixed $value = NULL)
* @method bool databaseDropField() databaseDropField(string $table, string $field)
* @method bool databaseDropIndex() databaseDropIndex(string $table, string $name)
* @method void databaseEnableAbsoluteCount() databaseEnableAbsoluteCount()
* @method void databaseEmptyTable() databaseEmptyTable(string $table)
* @method string databaseEscapeString() databaseEscapeString(mixed $value)
* @method string databaseQuoteString() databaseQuoteString(mixed $value)
* @method string databaseGetProtocol() databaseGetProtocol()
* @method string databaseGetSqlSource() databaseGetSqlSource(string $function, array $params)
* @method string databaseGetSqlCondition() databaseGetSqlCondition(array $filter, $value = NULL, $operator = '=')
* @method int|string|FALSE databaseInsertRecord() databaseInsertRecord(string $table, mixed $idField, array $values = NULL)
* @method int|string|FALSE databaseInsertRecords() databaseInsertRecords(string $table, array $values)
* @method PapayaDatabaseResult|int|FALSE  databaseQuery() databaseQuery(string $sql, integer $max = NULL, integer $offset = NULL, boolean $readOnly = TRUE)
* @method PapayaDatabaseResult|int|FALSE databaseQueryFmt() databaseQueryFmt(string $sql, array $values, integer $max = NULL, integer $offset = NULL, boolean $readOnly = TRUE)
* @method PapayaDatabaseResult|int|FALSE databaseQueryFmtWrite() databaseQueryFmtWrite(string $sql, array $values)
* @method PapayaDatabaseResult|int|FALSE databaseQueryWrite() databaseQueryWrite(string $sql)
* @method int|FALSE databaseUpdateRecord() databaseUpdateRecord(string $table, array $values, mixed $filter, mixed $value = NULL)
* @method array databaseQueryTableNames() databaseQueryTableNames()
* @method array databaseQueryTableStructure() databaseQueryTableStructure(string $tableName)
* @method string databaseGetTableName() databaseGetTableName($tableIdentifier, $usePrefix = TRUE)
* @method int databaseGetTimestamp() databaseGetTimestamp()
* @method int|string|NULL databaseLastInsertId() databaseLastInsertId(string $table, string $idField)
*/
class PapayaDatabaseObject
  extends PapayaObject
  implements PapayaDatabaseInterfaceAccess {

  /**
  * Database read uri
  * @var string|NULL
  */
  protected $databaseURI = NULL;

  /**
  * database write uri
  * @var string|NULL
  */
  protected $databaseURIWrite = NULL;

  /**
  * Stored database access object
  * @var PapayaDatabaseAccess
  */
  protected $_databaseAccessObject = NULL;

  /**
  * Set database access object
  * @param PapayaDatabaseAccess $databaseAccessObject
  */
  public function setDatabaseAccess(PapayaDatabaseAccess $databaseAccessObject) {
    $this->_databaseAccessObject = $databaseAccessObject;
  }

  /**
  * Get database access object
  * @return PapayaDatabaseAccess
  */
  public function getDatabaseAccess() {
    if (!isset($this->_databaseAccessObject)) {
      $this->_databaseAccessObject = new \PapayaDatabaseAccess(
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
      throw new \BadMethodCallException(
        sprintf(
          'Invalid function call. Method %s::%s does not exist.',
          get_class($this),
          $functionName
        )
      );
    }
  }
}
