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
   * @covers \Papaya\Database\Statement\Formatted
   */
  class FormattedTest extends \Papaya\TestCase {

    public function testToString() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('escapeString')
        ->with('ab123')
        ->willReturn('ab123');

      $statement = new Formatted(
        $databaseAccess,
        "SELECT * FROM test WHERE id = '%s'",
        ['ab123']
      );
      $this->assertSame(
        "SELECT * FROM test WHERE id = 'ab123'",
        (string)$statement
      );
    }

    public function testGetSQLInsertsEscapedParameters() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('escapeString')
        ->with('ab123')
        ->willReturn('ab123');

      $statement = new Formatted(
        $databaseAccess,
        "SELECT * FROM test WHERE id = '%s'",
        ['ab123']
      );
      $this->assertSame(
        "SELECT * FROM test WHERE id = 'ab123'",
        $statement->getSQLString(TRUE)
      );
      $this->assertSame(
        [],
        $statement->getSQLParameters(TRUE)
      );
    }
  }
}
