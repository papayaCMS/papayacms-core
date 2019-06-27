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

require_once __DIR__.'/../bootstrap.php';

class db_simpleTest extends \Papaya\TestCase {

  /**
   * @dataProvider getConditionData
   * @param string $expected
   * @param mixed $condition
   * @param null $value
   * @param string $operator
   */
  public function testGetSqlCondition($expected, $condition, $value = NULL, $operator = '=') {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Source\Name $dsn */
    $dsn = $this
      ->getMockBuilder(\Papaya\Database\Source\Name::class)
      ->disableOriginalConstructor()
      ->getMock();
    $connection = new DbConnection_TestProxy($dsn);

    $db = new db_simple();
    $db->databaseObjects = array(
      'read' => $connection,
      'write' => $connection,
    );
    $this->assertEquals(
      $expected,
      $db->getSQLCondition($this->createMock(base_db::class), $condition, $value, $operator)
    );
  }

  public static function getConditionData() {
    return array(
      array("col = ''", array('col' => '')),
      array("col = ''", 'col', ''),
      array("col = '0'", array('col' => FALSE)),
      array("col = '0'", 'col', FALSE),
      array("col = ''", array('col' => '')),
      array('col IS NULL', array('col' => NULL)),
      array('col IS NULL', 'col', NULL),
      array("col > '42'", 'col', 42, '>'),
      array("(col > '21' OR col > '42')", 'col', array(21, 42), '>'),
      array("(col < '21' OR col < '42')", 'col', array(21, 42), '<'),
      array('1 = 0', 'col', array())
    );
  }
}

class DbConnection_TestProxy extends dbcon_base {

  public function lastInsertId($table, $idField) {
    return NULL;
  }

  public function connect() {
    return TRUE;
  }

  public function isExtensionAvailable() {
    return TRUE;
  }

  /** @noinspection OverridingDeprecatedMethodInspection */
  public function escapeStr($string) {
    return $string;
  }

  public function executeQuery($sql) {
    return FALSE;
  }

  public function disconnect() {
    return FALSE;
  }
}
