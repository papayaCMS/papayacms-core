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

  use Papaya\Database\Connection as DatabaseConnection;
  use Papaya\Database\Syntax as DatabaseSyntax;
  use Papaya\Test\TestCase;

  /**
   * @covers \Papaya\Database\Statement\Limited
   */
  class LimitedTest extends TestCase {

    public function testForServerSidePreparedStatement() {
      $syntax = $this->createMock(DatabaseSyntax::class);
      $syntax
        ->expects($this->once())
        ->method('limit')
        ->with(20, 10)
        ->willReturn(' LIMIT 10, 20');
      $connection = $this->createMock(DatabaseConnection::class);
      $connection->method('syntax')->willReturn($syntax);
      $statement = $this->createMock(Prepared::class);
      $statement
        ->expects($this->once())
        ->method('getSQLString')
        ->with(TRUE)
        ->willReturn('sql statement');
      $statement
        ->expects($this->once())
        ->method('getSQLParameters')
        ->with(TRUE)
        ->willReturn([42]);

      $limited = new Limited($connection, $statement, 20, 10);
      $this->assertSame('sql statement LIMIT 10, 20', $limited->getSQLString(TRUE));
      $this->assertSame([42], $limited->getSQLParameters(TRUE));
    }

    public function testForStaticStatement() {
      $syntax = $this->createMock(DatabaseSyntax::class);
      $syntax
        ->expects($this->once())
        ->method('limit')
        ->with(20, 10)
        ->willReturn(' LIMIT 10, 20');
      $connection = $this->createMock(DatabaseConnection::class);
      $connection->method('syntax')->willReturn($syntax);
      $statement = $this->createMock(Prepared::class);
      $statement
        ->expects($this->once())
        ->method('getSQLString')
        ->with(FALSE)
        ->willReturn('sql statement');
      $statement
        ->expects($this->once())
        ->method('getSQLParameters')
        ->with(FALSE)
        ->willReturn([]);

      $limited = new Limited($connection, $statement, 20, 10);
      $this->assertSame('sql statement LIMIT 10, 20', $limited->getSQLString(FALSE));
      $this->assertSame([], $limited->getSQLParameters(FALSE));
    }
  }

}
