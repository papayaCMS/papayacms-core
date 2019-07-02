<?php

namespace Papaya\Database\Syntax;


use Papaya\Database\Connection;

/**
 * @covers Papaya\Database\Syntax\MassInsert
 */
class MassInsertTest extends \PHPUnit_Framework_TestCase {

  public function testMassInsertExpectingSingleQuery() {
    $values = [
      ['foo' => 21],
      ['foo' => 42]
    ];

    $connection = $this->createConnectionFixture(
      [
        'INSERT INTO "test_table" ("foo") VALUES (\'21\'), (\'42\')'
      ]
    );

    $insert = new MassInsert($connection, 'test_table', $values);
    $this->assertTrue($insert());
  }

  public function testMassInsertWithDifferentColumnsExpectingTwoQueries() {
    $values = [
      ['foo' => 21],
      ['bar' => 42]
    ];

    $connection = $this->createConnectionFixture(
      [
        'INSERT INTO "test_table" ("foo") VALUES (\'21\')',
        'INSERT INTO "test_table" ("bar") VALUES (\'42\')'
      ]
    );

    $insert = new MassInsert($connection, 'test_table', $values);
    $this->assertTrue($insert());
  }

  public function testMassInsertWithMultipleFields() {
    $values = [
      ['foo' => 21, 'bar' => 42]
    ];

    $connection = $this->createConnectionFixture(
      [
        'INSERT INTO "test_table" ("foo","bar") VALUES (\'21\',\'42\')'
      ]
    );

    $insert = new MassInsert($connection, 'test_table', $values);
    $this->assertTrue($insert());
  }

  public function testWithLimitedQuerySizeExpectingTwoQueries() {
    $values = [
      ['foo' => 1], ['foo' => 2], ['foo' => 3], ['foo' => 4], ['foo' => 5],
      ['foo' => 6], ['foo' => 7], ['foo' => 8], ['foo' => 9], ['foo' => 10]
    ];

    $connection = $this->createConnectionFixture(
      [
        'INSERT INTO "test_table" ("foo") VALUES (\'1\'), (\'2\'), (\'3\'), (\'4\'), (\'5\'), (\'6\')',
        'INSERT INTO "test_table" ("foo") VALUES (\'7\'), (\'8\'), (\'9\'), (\'10\')'
      ]
    );

    $insert = new MassInsert($connection, 'test_table', $values);
    $insert->setMaximumQuerySize(70);
    $this->assertTrue($insert());
  }

  public function testWithSQLFailureExpectingFalse() {
    $values = [
      ['foo' => 21], ['foo' => 42]
    ];

    $connection = $this->createConnectionFixture([]);

    $insert = new MassInsert($connection, 'test_table', $values);
    $this->assertFalse($insert());
  }

  public function testWithDifferentColumnAndSQLFailureExpectingFalse() {
    $values = [
      ['foo' => 21], ['bar' => 42]
    ];

    $connection = $this->createConnectionFixture([]);

    $insert = new MassInsert($connection, 'test_table', $values);
    $this->assertFalse($insert());
  }

  public function testWithLimitedQuerySizeAndSQLFailureExpectingFalse() {
    $values = [
      ['foo' => 1], ['foo' => 2], ['foo' => 3], ['foo' => 4], ['foo' => 5],
      ['foo' => 6], ['foo' => 7], ['foo' => 8], ['foo' => 9], ['foo' => 10]
    ];

    $connection = $this->createConnectionFixture([]);
    $insert = new MassInsert($connection, 'test_table', $values);
    $insert->setMaximumQuerySize(70);
    $this->assertFalse($insert());
  }

  private function createConnectionFixture(array $expectedQueries) {
    $connection = $this->createMock(Connection::class);
    $connection
      ->expects($this->any())
      ->method('quoteIdentifier')
      ->willReturnCallback(
        static function($identifier) { return '"'.$identifier.'"'; }
      );
    $connection
      ->expects($this->any())
      ->method('quoteString')
      ->willReturnCallback(
        static function($literal) { return "'".$literal."'"; }
      );
    if (count($expectedQueries) > 0) {
      $connection
        ->expects($this->exactly(count($expectedQueries)))
        ->method('execute')
        ->withConsecutive(
          ...array_map(
               static function ($query) {
                 return [$query];
               },
               $expectedQueries
             )
        )
        ->willReturn(count($expectedQueries));
    } else {
      $connection
        ->expects($this->atLeastOnce())
        ->method('execute')
        ->with($this->isType('string'))
        ->willReturn(FALSE);
    }
    return $connection;
  }

}
