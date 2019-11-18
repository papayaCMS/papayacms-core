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
  use Papaya\Database\Exception\ConnectionFailed;
  use Papaya\Test\TestCase;

  /**
   * @covers \Papaya\Database\Statement\ExecutableStatement
   */
  class ExecutableStatementTest extends TestCase {

    public function testGetDatabaseConnectionAfterConstruct() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      $statement = new ExecutableStatement_TestProxy($connection);
      $this->assertSame($connection, $statement->getDatabaseConnection());
    }

    public function testExecuteSendStatementToConnection() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      $statement = new ExecutableStatement_TestProxy($connection);
      $connection
        ->expects($this->once())
        ->method('execute')
        ->with($statement, DatabaseConnection::USE_WRITE_CONNECTION)
        ->willReturn(FALSE);
      $statement->execute(  DatabaseConnection::USE_WRITE_CONNECTION);
    }

    public function testToString() {
      $statement = $this->createPartialMock(
        ExecutableStatement::class, ['getSQLString', 'getSQLParameters']
      );
      $statement
        ->expects($this->once())
        ->method('getSQLString')
        ->with(FALSE)
        ->willReturn('compiled sql');
      $this->assertSame('compiled sql', (string)$statement);
    }

    public function testToStringWithConnectionExceptionReturnEmptyString() {
      $statement = $this->createPartialMock(
        ExecutableStatement::class, ['getSQLString', 'getSQLParameters']
      );
      $statement
        ->expects($this->once())
        ->method('getSQLString')
        ->with(FALSE)
        ->willThrowException(new ConnectionFailed(''));
      $this->assertSame('', (string)$statement);
    }
  }

  class ExecutableStatement_TestProxy extends ExecutableStatement {

    public $SQLString = 'sql string';
    public $SQLParameters = [1, 2, 3];

    /**
     * @param bool $allowPrepared
     * @return string
     */
    public function getSQLString($allowPrepared = TRUE) {
      return $this->SQLString;
    }

    /**
     * @param bool $allowPrepared
     * @return array
     */
    public function getSQLParameters($allowPrepared = TRUE) {
      return $this->SQLParameters;
    }
  }
}
