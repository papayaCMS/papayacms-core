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

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\Database\Statement\Prepared
   */
  class PreparedTest extends \Papaya\TestCase {

    public function testGetSQLWithTableAndStringParameter() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('quoteString')
        ->with('ab123')
        ->willReturn("'ab123'");

      $statement = new Prepared(
        $databaseAccess,
        'SELECT * FROM :a_table WHERE id = :id'
      );
      $statement
        ->addTableName('a_table', 'test')
        ->addString('id', 'ab123');

      $this->assertEquals(
        "SELECT * FROM table_test WHERE id = 'ab123'",
        (string)$statement
      );
    }

    public function testGetSQLWithTableAndStringListParameter() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->exactly(2))
        ->method('quoteString')
        ->will(
          $this->returnValueMap(
            [
              ['ab123', "'ab123'"],
              ['ef456', "'ef456'"]
            ]
          )
        );

      $statement = new Prepared(
        $databaseAccess,
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

    public function testGetSQLWithFloatParameter() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();

      $statement = new Prepared(
        $databaseAccess,
        'SELECT * FROM test WHERE field > :number'
      );
      $statement->addFloat('number', 42.21, 4);

      $this->assertEquals(
        'SELECT * FROM test WHERE field > 42.2100',
        (string)$statement
      );
    }

    public function testGetSQLWithIntParameter() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();

      $statement = new Prepared(
        $databaseAccess,
        'SELECT * FROM test WHERE field > :number'
      );
      $statement->addInt('number', 42);

      $this->assertEquals(
        'SELECT * FROM test WHERE field > 42',
        (string)$statement
      );
    }

    public function testGetSQLWithIntListParameter() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();

      $statement = new Prepared(
        $databaseAccess,
        'SELECT * FROM test WHERE id IN :IDs'
      );
      $statement->addIntList('IDs', [21, 42]);

      $this->assertEquals(
        'SELECT * FROM test WHERE id IN (21, 42)',
        $statement->getSQL()
      );
    }

    public function testGetSQLWithBoolParameter() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();

      $statement = new Prepared(
        $databaseAccess,
        'SELECT * FROM test WHERE field IS :bool'
      );
      $statement->addBool('bool', true);

      $this->assertEquals(
        'SELECT * FROM test WHERE field IS true',
        (string)$statement
      );
    }

    public function testGetSQLWithNULLParameter() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();

      $statement = new Prepared(
        $databaseAccess,
        'INSERT INTO test VALUES (:field1, :field2)'
      );
      $statement
        ->addInt('field1', 21)
        ->addNull('field2');

      $this->assertEquals(
        'INSERT INTO test VALUES (21, NULL)',
        $statement->getSQL()
      );
    }

    public function testGetSQLWithLimitParameter() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('getSqlSource')
        ->with('LIMIT', [12, 23])
        ->willReturn(' LIMIT 23,12');

      $statement = new Prepared(
        $databaseAccess,
        'SELECT * FROM tablename ORDER BY fieldname :limit'
      );
      $statement
        ->addLimit('limit', 12, 23);

      $this->assertEquals(
        'SELECT * FROM tablename ORDER BY fieldname  LIMIT 23,12',
        $statement->getSQL()
      );
    }

    public function testPlaceholdersInsideStringLiteralAreNotReplaced() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();

      $statement = new Prepared(
        $databaseAccess,
        "INSERT INTO test VALUES (:number, ':number')"
      );
      $statement->addInt('number', 21);

      $this->assertEquals(
        "INSERT INTO test VALUES (21, ':number')",
        $statement->getSQL()
      );
    }

    public function testUnknownParameterThrowsException() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();

      $statement = new Prepared(
        $databaseAccess,
        ':unknown'
      );

      $this->expectException(\LogicException::class);
      $this->expectExceptionMessage('Unknown parameter name: unknown');
      $statement->getSQL();
    }

    public function testNULLInListIsIgnored() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();

      $statement = new Prepared($databaseAccess, ':numbers');
      $statement->addIntList('numbers', [21, NULL, 42]);

      $this->assertEquals(
        '(21, 42)',
        $statement->getSQL()
      );
    }

    public function testArrayInListThrowsException() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();

      $statement = new Prepared($databaseAccess, ':numbers');
      $statement->addIntList('numbers', [21, [42]]);

      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('Parameter list values need to be scalars or string castable.');
      $statement->getSQL();
    }

    public function testObjectInListIsCastToString() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->atLeastOnce())
        ->method('quoteString')
        ->will(
          $this->returnValueMap(
             [
               ['21', "'21'"],
               ['string_value', "'string_value'"],
             ]
          )
        );

      $stringObject = $this->createMock(\Papaya\Text\UTF8String::class);
      $stringObject
        ->expects($this->once())
        ->method('__toString')
        ->willReturn('string_value');

      $statement = new Prepared($databaseAccess, ':ids');
      $statement->addStringList('ids', [21, $stringObject]);

      $this->assertEquals(
        "('21', 'string_value')",
        $statement->getSQL()
      );
    }

    public function testHasExpectingTrue() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();

      $statement = new Prepared($databaseAccess, '');
      $statement->addInt('number', 21);

      $this->assertTrue($statement->has('number'));
      $this->assertTrue($statement->has('NUMBER'));
    }

    public function testHasAfterRemoveExpectingFalse() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();

      $statement = new Prepared($databaseAccess, '');
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
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $statement = new Prepared($databaseAccess, '');

      $this->expectException(\InvalidArgumentException::class);
      $this->expectExceptionMessage(
        'Parameter name has to start with a letter and can only contain letters, digits and underscores.'
      );

      $statement->addInt($parameterName, 0);
    }

    public function testDuplicateParameterNameThrowsException() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $statement = new Prepared($databaseAccess, '');
      $statement->addInt('field', 0);

      $this->expectException(\LogicException::class);
      $this->expectExceptionMessage(
        'Duplicate parameter name: field'
      );

      $statement->addInt('field', 0);
    }

    public function testToStringReturnsEmptyStringOnInternalError() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $statement = new Prepared($databaseAccess, ':trigger');
      $this->assertEmpty((string)$statement);
    }
  }
}
