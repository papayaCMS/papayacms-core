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
namespace Papaya\Database\Interfaces\Access;

/**
 * @method bool databaseAddField(string $table, array $fieldData)
 * @method bool databaseAddIndex(string $table, array $index)
 * @method bool databaseChangeField(string $table, array $fieldData)
 * @method bool databaseChangeIndex(string $table, array $index)
 * @method void databaseClose()
 * @method true databaseCompareFieldStructure(array $xmlField, array $databaseField)
 * @method bool databaseCompareKeyStructure()
 * @method bool databaseCreateTable(string $tableData, string $tablePrefix)
 * @method void databaseDebugNextQuery(integer $count = 1)
 * @method int databaseDeleteRecord(string $table, mixed $filter, mixed $value = NULL)
 * @method bool databaseDropField(string $table, string $field)
 * @method bool databaseDropIndex(string $table, string $name)
 * @method void databaseEnableAbsoluteCount()
 * @method void databaseEmptyTable(string $table)
 * @method string databaseEscapeString(mixed $value)
 * @method string databaseQuoteString(mixed $value)
 * @method string databaseGetProtocol()
 * @method string databaseGetSqlSource(string $function, array $params)
 * @method string databaseGetSqlCondition(array|string $filter, $value = NULL, $operator = '=')
 * @method int|string|false databaseInsertRecord(string $table, mixed $idField, array $values = NULL)
 * @method int|string|false databaseInsertRecords(string $table, array $values)
 * @method \Papaya\Database\Result|int|false databaseQuery(string $sql, integer $max = NULL, integer $offset = NULL, boolean $readOnly = TRUE)
 * @method \Papaya\Database\Result|int|false databaseQueryFmt(string $sql, array $values, integer $max = NULL, integer $offset = NULL, boolean $readOnly = TRUE)
 * @method \Papaya\Database\Result|int|false databaseQueryFmtWrite(string $sql, array $values)
 * @method \Papaya\Database\Result|int|false databaseQueryWrite(string $sql)
 * @method int|false databaseUpdateRecord(string $table, array $values, mixed $filter, mixed $value = NULL)
 * @method array databaseQueryTableNames()
 * @method array databaseQueryTableStructure(string $tableName)
 * @method string databaseGetTableName($tableIdentifier, $usePrefix = TRUE)
 * @method int databaseGetTimestamp()
 * @method int|string|null databaseLastInsertId(string $table, string $idField)
 */
trait Delegation {
  use Aggregation;

  /**
   * Delegate calls to "database*" methods to the database access object
   *
   * @param string $functionName
   * @param array $arguments
   * @throws \BadMethodCallException
   * @return mixed
   */
  public function __call($functionName, $arguments) {
    if (0 === \strpos($functionName, 'database')) {
      $delegateFunction = \strtolower($functionName[8]).\substr($functionName, 9);
      $access = $this->getDatabaseAccess();
      return \call_user_func_array([$access, $delegateFunction], $arguments);
    }
    throw new \BadMethodCallException(
      \sprintf(
        'Invalid function call. Method %s::%s does not exist.',
        \get_class($this),
        $functionName
      )
    );
  }
}
