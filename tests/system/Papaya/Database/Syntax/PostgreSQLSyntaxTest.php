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

namespace Papaya\Database\Syntax {

  require_once __DIR__.'/../../../../bootstrap.php';

  use Papaya\Database\Connection as DatabaseConnection;
  use Papaya\TestFramework\TestCase;

  /**
   * @covers \Papaya\Database\Syntax\PostgreSQLSyntax
   */
  class PostgreSQLSyntaxTest extends TestCase {

    public function testLength() {
      $syntax = new PostgreSQLSyntax($this->createConnectionFixture());
      $this->assertSame("LENGTH('text')", (string)$syntax->length('text'));
    }

    public function testLocate() {
      $syntax = new PostgreSQLSyntax($this->createConnectionFixture());
      $this->assertSame(
        "LOCATE('haystack', 'needle', '0')",
        (string)$syntax->locate('haystack', 'needle')
      );
    }

    public function testLocateWithOffset() {
      $syntax = new PostgreSQLSyntax($this->createConnectionFixture());
      $this->assertSame(
        "LOCATE('haystack', 'needle', '10')",
        (string)$syntax->locate('haystack', 'needle', 10)
      );
    }

    public function testConcat() {
      $syntax = new PostgreSQLSyntax($this->createConnectionFixture());
      $this->assertSame(
        "CONCAT('one', 'two', 'three', 'four')",
        (string)$syntax->concat('one', 'two', 'three', 'four')
      );
    }

    public function testSubstring() {
      $syntax = new PostgreSQLSyntax($this->createConnectionFixture());
      $this->assertSame(
        "SUBSTRING('Hello World!', '6')",
        (string)$syntax->substring('Hello World!', 6)
      );
    }

    public function testSubstringWithLengthArgument() {
      $syntax = new PostgreSQLSyntax($this->createConnectionFixture());
      $this->assertSame(
        "SUBSTRING('Hello World!', '6', '2')",
        (string)$syntax->substring('Hello World!', 6, 2)
      );
    }

    public function testLike() {
      $syntax = new PostgreSQLSyntax($this->createConnectionFixture());
      $this->assertSame(
        "LIKE 'value' ESCAPE '\\\\'",
        (string)$syntax->like('value')
      );
    }

    public function testLikeWithIdentifier() {
      $syntax = new PostgreSQLSyntax($this->createConnectionFixture());
      $this->assertSame(
        'LIKE `field` ESCAPE \'\\\\\'',
        (string)$syntax->like($syntax->identifier('field'))
      );
    }

    public function testLower() {
      $syntax = new PostgreSQLSyntax($this->createConnectionFixture());
      $this->assertSame(
        'LOWER(`field`)',
        (string)$syntax->lower($syntax->identifier('field'))
      );
    }

    public function testUpper() {
      $syntax = new PostgreSQLSyntax($this->createConnectionFixture());
      $this->assertSame(
        'UPPER(`field`)',
        (string)$syntax->upper($syntax->identifier('field'))
      );
    }

    public function testLimit() {
      $syntax = new PostgreSQLSyntax($this->createConnectionFixture());
      $this->assertSame(
        ' LIMIT 10',
        (string)$syntax->limit(10)
      );
    }

    public function testLimitWithLimitIsZero() {
      $syntax = new PostgreSQLSyntax($this->createConnectionFixture());
      $this->assertSame(
        '',
        (string)$syntax->limit(0)
      );
    }

    public function testLimitWithOffset() {
      $syntax = new PostgreSQLSyntax($this->createConnectionFixture());
      $this->assertSame(
        ' OFFSET 20 LIMIT 10',
        (string)$syntax->limit(10, 20)
      );
    }

    public function testRandom() {
      $syntax = new PostgreSQLSyntax($this->createConnectionFixture());
      $this->assertSame(
        'RANDOM()',
        (string)$syntax->random()
      );

    }

    public function testGetDialect() {
      $syntax = new PostgreSQLSyntax($this->createConnectionFixture());
      $this->assertSame('pgsql', $syntax->getDialect());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DatabaseConnection
     */
    private function createConnectionFixture() {
      $connection = $this->createMock(DatabaseConnection::class);
      $connection
        ->expects($this->any())
        ->method('quoteString')
        ->willReturnCallback(
          static function($literal) { return "'{$literal}'"; }
        );
      $connection
        ->expects($this->any())
        ->method('quoteIdentifier')
        ->willReturnCallback(
          static function($name) { return "`{$name}`"; }
        );
      return $connection;
    }
  }

}
