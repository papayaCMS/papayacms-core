<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
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

  use BadMethodCallException;
  use Papaya\Database\Connection as DatabaseConnection;
  use Papaya\Database\Statement as DatabaseStatement;
  use Papaya\TestFramework\TestCase;

  /**
   * @covers \Papaya\Database\Statement\Count
   */
  class CountTest extends TestCase {

    public function testForStaticStatement() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseStatement $original */
      $original = $this->createMock(DatabaseStatement::class);
      $original
        ->expects($this->once())
        ->method('getSQLString')
        ->with(FALSE)
        ->willReturn('SELECT field_name FROM table_name');
      $original
        ->expects($this->once())
        ->method('getSQLParameters')
        ->with(FALSE)
        ->willReturn([]);

      $statement = new Count($connection, $original);
      $this->assertSame('SELECT COUNT(*) FROM table_name', $statement->getSQLString(FALSE));
      $this->assertSame([], $statement->getSQLParameters(FALSE));
    }

    public function testForStaticStatementWithOrderBy() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseStatement $original */
      $original = $this->createMock(DatabaseStatement::class);
      $original
        ->expects($this->once())
        ->method('getSQLString')
        ->with(FALSE)
        ->willReturn('SELECT field_name FROM table_name ORDER BY field_name');
      $original
        ->expects($this->once())
        ->method('getSQLParameters')
        ->with(FALSE)
        ->willReturn([]);

      $statement = new Count($connection, $original);
      $this->assertSame('SELECT COUNT(*) FROM table_name', $statement->getSQLString(FALSE));
      $this->assertSame([], $statement->getSQLParameters(FALSE));
    }

    public function testForPreparedStatement() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseStatement $original */
      $original = $this->createMock(DatabaseStatement::class);
      $original
        ->expects($this->once())
        ->method('getSQLString')
        ->with(TRUE)
        ->willReturn('SELECT field_name FROM table_name WHERE field_name = ?');
      $original
        ->expects($this->once())
        ->method('getSQLParameters')
        ->with(TRUE)
        ->willReturn([42]);

      $statement = new Count($connection, $original);
      $this->assertSame('SELECT COUNT(*) FROM table_name WHERE field_name = ?', $statement->getSQLString());
      $this->assertSame([42], $statement->getSQLParameters());
    }

    public function testForPreparedStatementResultsAreCached() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseStatement $original */
      $original = $this->createMock(DatabaseStatement::class);
      $original
        ->expects($this->once())
        ->method('getSQLString')
        ->with(TRUE)
        ->willReturn('SELECT field_name FROM table_name WHERE field_name = ?');

      $statement = new Count($connection, $original);
      $this->assertSame('SELECT COUNT(*) FROM table_name WHERE field_name = ?', $statement->getSQLString());
      $this->assertSame('SELECT COUNT(*) FROM table_name WHERE field_name = ?', $statement->getSQLString());
    }

    public function testForUpdateStatementExpectingException() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseStatement $original */
      $original = $this->createMock(DatabaseStatement::class);
      $original
        ->expects($this->once())
        ->method('getSQLString')
        ->with(FALSE)
        ->willReturn('UPDATE table_name SET field_name = 42');
      $original
        ->expects($this->never())
        ->method('getSQLParameters');

      $statement = new Count($connection, $original);
      $this->expectException(BadMethodCallException::class);
      $this->expectExceptionMessage('Can not rewrite SQL statement to count records.');
      $this->assertSame('SELECT COUNT(*) FROM table_name', $statement->getSQLString(FALSE));
      $this->assertSame([], $statement->getSQLParameters(FALSE));
    }

  }

}
