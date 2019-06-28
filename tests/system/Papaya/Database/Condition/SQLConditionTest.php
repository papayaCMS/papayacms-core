<?php

namespace Papaya\Database\Condition {

  use Papaya\Database\Connection;
  use Papaya\Test\TestCase;

  require_once __DIR__.'/../../../../bootstrap.php';

  class SQLConditionTest extends TestCase {

    /**
     * @param $expected
     * @param $filter
     * @param string $operator
     * @dataProvider getConditionData
     */
    public function testConditionToString($expected, $filter, $operator = '') {
      $connection = $this->createMock(Connection::class);
      $connection
        ->expects($this->any())
        ->method('quoteString')
        ->willReturnCallback(
          static function($literal) {
            return "'".$literal."'";
          }
        );$connection
        ->expects($this->any())
        ->method('quoteIdentifier')
        ->willReturnCallback(
          static function($name) {
            return '"'.$name.'"';
          }
        );

      $this->assertSame(
        $expected,
        (string)new SQLCondition($connection, $filter, $operator)
      ) ;
    }

    public static function getConditionData() {
      return [
        ["\"col\" = ''", ['col' => '']],
        ["\"col\" = '0'", ['col' => FALSE]],
        ["\"col\" = ''", ['col' => '']],
        ['"col" IS NULL', ['col' => NULL]],
        ["\"col\" > '42'", ['col' => 42], '>'],
        ["(\"col\" > '21' OR \"col\" > '42')", ['col'=> [21, 42]], '>'],
        ["(\"col\" < '21' OR \"col\" < '42')", ['col'=> [21, 42]], '<'],
        ['1 = 0', ['col' => []]]
      ];
    }
  }
}
