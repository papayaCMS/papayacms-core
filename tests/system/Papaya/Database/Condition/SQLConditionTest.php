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
        ["\"col\" != ''", ['col' => ''], SQLCondition::NOT_EQUAL],
        ["\"col\" IN ('1', '2')", ['col' => [1, 2]]],
        ["NOT(\"col\" IN ('1', '2'))", ['col' => [1, 2]], SQLCondition::NOT_EQUAL],
        ["\"col\" = '0'", ['col' => FALSE]],
        ["\"col\" = ''", ['col' => '']],
        ['"col" IS NULL', ['col' => NULL]],
        ["\"col\" > '42'", ['col' => 42], '>'],
        ["(\"col\" > '21' OR \"col\" > '42')", ['col'=> [21, 42]], '>'],
        ["(\"col\" < '21' OR \"col\" < '42')", ['col'=> [21, 42]], '<'],
        ['1 = 0', ['col' => []]],
        ['"field1" = \'1\' OR "field2" = \'2\'', ['field1' => 1, 'or', 'field2' => 2]],
        ['"field" = \'or\'', ['field' => 'or']],
        ['1=1', NULL],
        ['1=0', []]
      ];
    }
  }
}
