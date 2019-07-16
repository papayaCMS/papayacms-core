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

namespace Papaya\Database\Schema {

  use Papaya\Database\Connection as DatabaseConnection;
  use Papaya\Database\Result as DatabaseResult;
  use Papaya\TestCase;

  require_once __DIR__.'/../../../../bootstrap.php';

  class MySQLSchemaTest extends TestCase {

    public function testGetTables() {
      $result = $this->createMock(DatabaseResult::class);
      $result
        ->expects($this->atLeastOnce())
        ->method('fetchField')
        ->willReturnOnConsecutiveCalls(
          'table_one', 'table_two'
        );
      /** @var DatabaseConnection|\PHPUnit_Framework_MockObject_MockObject $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      $connection
        ->expects($this->once())
        ->method('execute')
        ->with('SHOW TABLES')
        ->willReturn($result);

      $schema = new MySQLSchema($connection);
      $this->assertSame(
        ['table_one', 'table_two'],
        $schema->getTables()
      );
    }
  }

}
