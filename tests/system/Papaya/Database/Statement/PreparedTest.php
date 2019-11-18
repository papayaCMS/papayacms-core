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

namespace Papaya\Database\Statement {

  use Papaya\Database\Connection;
  use Papaya\Database\Syntax;
  use Papaya\TestCase;
  use Papaya\Text\UTF8String;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\Database\Statement\Prepared
   */
  class PreparedTest extends TestCase {

    public function testGetSQLWithTableAndStringParameter() {
      $connection = $this->mockPapaya()->databaseAccess();
      $connection
        ->expects($this->once())
        ->method('quoteString')
        ->with('ab123')
        ->willReturn("'ab123'");

      $statement = new Prepared(
        $connection,
        'SELECT * FROM :a_table WHERE id = :id'
      );
      $statement
        ->addTableName('a_table', 'test')
        ->addString('id', 'ab123');

      $this->assertEquals(
        "SELECT * FROM table_test WHERE id = 'ab123'",
        $statement->getSQLString(FALSE)
      );
    }

    public function testGetPreparedSQLWithTableAndStringParameter() {
      $connection = $this->mockPapaya()->databaseAccess();
      $connection
        ->expects($this->never())
        ->method('quoteString');

      $statement = new Prepared(
        $connection,
        'SELECT * FROM :a_table WHERE id = :id'
      );
      $statement
        ->addTableName('a_table', 'test')
        ->addString('id', 'ab123');

      $this->assertEquals(
        'SELECT * FROM table_test WHERE id = ?',
        $statement->getSQLString()
      );
      $this->assertEquals(
        ['ab123'],
        $statement->getSQLParameters()
      );
    }

    public function testGetSQLWithTableAndStringListParameter() {
      $connection = $this->mockPapaya()->databaseAccess();
      $connection
        ->expects($this->exactly(2))
        ->method('quoteString')
        ->willReturnMap(
          [
            ['ab123', "'ab123'"],
            ['ef456', "'ef456'"]
          ]
        );

      $statement = new Prepared(
        $connection,
        'SELECT * FROM :a_table WHERE id IN :id'
      );
      $statement
        ->addTableName('a_table', 'test')
        ->addStringList('id', ['ab123', 'ef456']);

      $this->assertEquals(
        "SELECT * FROM table_test WHERE id IN ('ab123', 'ef456')",
        (string)$statement
      );
    }

    public function testGetSQLStringWithTableAndStringListParameter() {
      $connection = $this->mockPapaya()->databaseAccess();
      $connection
        ->expects($this->never())
        ->method('quoteString');

      $statement = new Prepared(
        $connection,
        'SELECT * FROM :a_table WHERE id IN :id'
      );
      $statement
        ->addTableName('a_table', 'test')
        ->addStringList('id', ['ab123', 'ef456']);

      $this->assertEquals(
        'SELECT * FROM table_test WHERE id IN (?, ?)',
        $statement->getSQLString()
      );
      $this->assertEquals(
        ['ab123', 'ef456'],
        $statement->getSQLParameters()
      );
    }

    public function testGetSQLWithFloatParameter() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Connection $connection */
      $connection = $this->createMock(Connection::class);

      $statement = new Prepared(
        $connection,
        'SELECT * FROM test WHERE field > :number'
      );
      $statement->addFloat('number', 42.21, 4);

      $this->assertEquals(
        'SELECT * FROM test WHERE field > 42.2100',
        (string)$statement
      );
    }

    public function testGetSQLStringWithFloatParameter() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Connection $connection */
      $connection = $this->createMock(Connection::class);

      $statement = new Prepared(
        $connection,
        'SELECT * FROM test WHERE field > :number'
      );
      $statement->addFloat('number', 42.21, 4);

      $this->assertEquals(
        'SELECT * FROM test WHERE field > ?',
        $statement->getSQLString()
      );
      $this->assertEquals(
        [42.2100],
        $statement->getSQLParameters()
      );
    }

    public function testGetSQLWithIntParameter() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Connection $connection */
      $connection = $this->createMock(Connection::class);

      $statement = new Prepared(
        $connection,
        'SELECT * FROM test WHERE field > :number'
      );
      $statement->addInt('number', 42);

      $this->assertEquals(
        'SELECT * FROM test WHERE field > 42',
        (string)$statement
      );
    }

    public function testGetSQLStringWithIntParameter() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Connection $connection */
      $connection = $this->createMock(Connection::class);

      $statement = new Prepared(
        $connection,
        'SELECT * FROM test WHERE field > :number'
      );
      $statement->addInt('number', 42);

      $this->assertEquals(
        'SELECT * FROM test WHERE field > ?',
        $statement->getSQLString()
      );
      $this->assertEquals(
        [42],
        $statement->getSQLParameters()
      );
    }

    public function testGetSQLWithIntListParameter() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Connection $connection */
      $connection = $this->createMock(Connection::class);

      $statement = new Prepared(
        $connection,
        'SELECT * FROM test WHERE id IN :IDs'
      );
      $statement->addIntList('IDs', [21, 42]);

      $this->assertEquals(
        'SELECT * FROM test WHERE id IN (21, 42)',
        (string)$statement
      );
    }

    public function testGetSQLWithIntListParameterFromTraversable() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Connection $connection */
      $connection = $this->createMock(Connection::class);

      $statement = new Prepared(
        $connection,
        'SELECT * FROM test WHERE id IN :IDs'
      );
      $statement->addIntList('IDs', new \ArrayIterator([21, 42]));

      $this->assertEquals(
        'SELECT * FROM test WHERE id IN (21, 42)',
        (string)$statement
      );
    }

    public function testGetSQLStringWithIntListParameter() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Connection $connection */
      $connection = $this->createMock(Connection::class);

      $statement = new Prepared(
        $connection,
        'SELECT * FROM test WHERE id IN :IDs'
      );
      $statement->addIntList('IDs', [21, 42]);

      $this->assertEquals(
        'SELECT * FROM test WHERE id IN (?, ?)',
        $statement->getSQLString()
      );
      $this->assertEquals(
        [21, 42],
        $statement->getSQLParameters()
      );
    }

    public function testGetSQLWithBoolParameter() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Connection $connection */
      $connection = $this->createMock(Connection::class);

      $statement = new Prepared(
        $connection,
        'SELECT * FROM test WHERE field IS :bool'
      );
      $statement->addBool('bool', TRUE);

      $this->assertEquals(
        'SELECT * FROM test WHERE field IS true',
        (string)$statement
      );
    }

    public function testGetSQLWithNULLParameter() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Connection $connection */
      $connection = $this->createMock(Connection::class);

      $statement = new Prepared(
        $connection,
        'INSERT INTO test VALUES (:field1, :field2)'
      );
      $statement
        ->addInt('field1', 21)
        ->addNull('field2');

      $this->assertEquals(
        'INSERT INTO test VALUES (21, NULL)',
        (string)$statement
      );
    }

    public function testGetSQLStringWithNULLParameter() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Connection $connection */
      $connection = $this->createMock(Connection::class);

      $statement = new Prepared(
        $connection,
        'INSERT INTO test VALUES (:field1, :field2)'
      );
      $statement
        ->addInt('field1', 21)
        ->addNull('field2');

      $this->assertEquals(
        'INSERT INTO test VALUES (?, NULL)',
        $statement->getSQLString()
      );
      $this->assertEquals(
        [21],
        $statement->getSQLParameters()
      );
    }

    public function testGetSQLWithLimitParameter() {
      $syntax = $this->createMock(Syntax::class);
      $syntax
        ->expects($this->once())
        ->method('limit')
        ->with(12, 23)
        ->willReturn(' LIMIT 23,12');
      /** @var \PHPUnit_Framework_MockObject_MockObject|Connection $connection */
      $connection = $this->createMock(Connection::class);
      $connection
        ->expects($this->once())
        ->method('syntax')
        ->willReturn($syntax);

      $statement = new Prepared(
        $connection,
        'SELECT * FROM tablename ORDER BY fieldname :limit'
      );
      $statement
        ->addLimit('limit', 12, 23);

      $this->assertEquals(
        'SELECT * FROM tablename ORDER BY fieldname  LIMIT 23,12',
        (string)$statement
      );
    }

    public function testPlaceholdersInsideStringLiteralAreNotReplaced() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Connection $connection */
      $connection = $this->createMock(Connection::class);

      $statement = new Prepared(
        $connection,
        "INSERT INTO test VALUES (:number, ':number')"
      );
      $statement->addInt('number', 21);

      $this->assertEquals(
        "INSERT INTO test VALUES (21, ':number')",
        (string)$statement
      );
    }

    public function testUnknownParameterThrowsException() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Connection $connection */
      $connection = $this->createMock(Connection::class);

      $statement = new Prepared(
        $connection,
        ':unknown'
      );

      $this->expectException(\LogicException::class);
      $this->expectExceptionMessage('Unknown parameter name: unknown');
      $statement->getSQLString();
    }

    public function testNULLInListIsIgnored() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Connection $connection */
      $connection = $this->createMock(Connection::class);

      $statement = new Prepared($connection, ':numbers');
      $statement->addIntList('numbers', [21, NULL, 42]);

      $this->assertEquals(
        '(21, 42)',
        (string)$statement
      );
    }

    public function testArrayInListThrowsException() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Connection $connection */
      $connection = $this->createMock(Connection::class);

      $statement = new Prepared($connection, ':numbers');
      $statement->addIntList('numbers', [21, [42]]);

      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Parameter list values need to be scalars or string castable.');
      $statement->getSQLString(FALSE);
    }

    public function testObjectInListIsCastToString() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Connection $connection */
      $connection = $this->createMock(Connection::class);
      $connection
        ->expects($this->atLeastOnce())
        ->method('quoteString')
        ->willReturnMap(
          [
            ['21', "'21'"],
            ['string_value', "'string_value'"],
          ]
        );

      $stringObject = $this->createMock(UTF8String::class);
      $stringObject
        ->expects($this->once())
        ->method('__toString')
        ->willReturn('string_value');

      $statement = new Prepared($connection, ':ids');
      $statement->addStringList('ids', [21, $stringObject]);

      $this->assertEquals(
        "('21', 'string_value')",
        (string)$statement
      );
    }

    public function testHasExpectingTrue() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Connection $connection */
      $connection = $this->createMock(Connection::class);

      $statement = new Prepared($connection, '');
      $statement->addInt('number', 21);

      $this->assertTrue($statement->has('number'));
      $this->assertTrue($statement->has('NUMBER'));
    }

    public function testHasAfterRemoveExpectingFalse() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Connection $connection */
      $connection = $this->createMock(Connection::class);

      $statement = new Prepared($connection, '');
      $statement->addInt('number', 21);
      $statement->remove('Number');

      $this->assertFalse($statement->has('number'));
    }

    /**
     * @param string $parameterName
     * @testWith
     *  [""]
     *  ["42"]
     *  ["foo bar"]
     */
    public function testInvalidParameterNameThrowsException($parameterName) {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Connection $connection */
      $connection = $this->createMock(Connection::class);
      $statement = new Prepared($connection, '');

      $this->expectException(\InvalidArgumentException::class);
      $this->expectExceptionMessage(
        'Parameter name has to start with a letter and can only contain letters, digits and underscores.'
      );

      $statement->addInt($parameterName, 0);
    }

    public function testDuplicateParameterNameThrowsException() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Connection $connection */
      $connection = $this->createMock(Connection::class);
      $statement = new Prepared($connection, '');
      $statement->addInt('field', 0);

      $this->expectException(\LogicException::class);
      $this->expectExceptionMessage(
        'Duplicate parameter name: field'
      );

      $statement->addInt('field', 0);
    }

    public function testToStringReturnsEmptyStringOnInternalError() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Connection $connection */
      $connection = $this->createMock(Connection::class);
      $statement = new Prepared($connection, ':trigger');
      $this->assertEmpty((string)$statement);
    }
  }
}
