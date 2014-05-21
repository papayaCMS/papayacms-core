<?php
require_once(dirname(__FILE__).'/../bootstrap.php');

class db_simpleTest extends PapayaTestCase {

  /**
   * @dataProvider getConditionData
   */
  public function testGetSqlCondition($expected, $condition, $value = NULL, $operator = '=') {
    $dsn = $this
      ->getMockBuilder('PapayaDatabaseSourceName')
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
      $db->getSQLCondition($this->getMock('base_db'), $condition, $value, $operator)
    );
  }

  public static function getConditionData() {
    return array(
      array("col = ''", array('col' => '')),
      array("col = ''", 'col', ''),
      array("col = '0'", array('col' => FALSE)),
      array("col = '0'", 'col', FALSE),
      array("col = ''", array('col' => '')),
      array("col IS NULL", array('col' => NULL)),
      array("col IS NULL", 'col', NULL),
      array("col > '42'", 'col', 42, '>'),
      array("(col > '21' OR col > '42')", 'col', array(21, 42), '>'),
      array("(col < '21' OR col < '42')", 'col', array(21, 42), '<'),
      array("1 = 0", 'col', array())
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

  public function extensionFound() {
    return TRUE;
  }

  function escapeStr($string) {
    return $string;
  }

  function executeQuery($sql) {
    return FALSE;
  }

  function close() {
    return FALSE;
  }
}