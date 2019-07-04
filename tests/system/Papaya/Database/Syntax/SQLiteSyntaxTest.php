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

  use Papaya\Database\Connection as DatabaseConnection;


  /**
   * @covers \Papaya\Database\Syntax\SQLiteSyntax
   */
  class SQLiteSyntaxTest extends \PHPUnit_Framework_TestCase {

    public function testLength() {
      $syntax = new SQLiteSyntax($this->createConnectionFixture());
      $this->assertSame("LENGTH('text')", $syntax->length('text'));
    }

    public function testLocate() {
      $connection = $this->createConnectionFixture();
      $connection
        ->expects($this->once())
        ->method('registerFunction')
        ->with('LOCATE', $this->isType('callable'));

      $syntax = new SQLiteSyntax($connection);
      $this->assertSame(
        "LOCATE('haystack', 'needle', '0')",
        $syntax->locate('haystack', 'needle')
      );
    }

    public function testLocateWithOffset() {
      $connection = $this->createConnectionFixture();
      $connection
        ->expects($this->once())
        ->method('registerFunction')
        ->with('LOCATE', $this->isType('callable'));

      $syntax = new SQLiteSyntax($connection);
      $this->assertSame(
        "LOCATE('haystack', 'needle', '10')",
        $syntax->locate('haystack', 'needle', 10)
      );
    }

    public function testConcat() {
      $syntax = new SQLiteSyntax($this->createConnectionFixture());
      $this->assertSame(
        "('one' || 'two' || 'three' || 'four')",
        $syntax->concat('one', 'two', 'three', 'four')
      );
    }

    public function testSubstring() {
      $syntax = new SQLiteSyntax($this->createConnectionFixture());
      $this->assertSame(
        "SUBSTRING('Hello World!', '6')",
        $syntax->substring('Hello World!', 6)
      );
    }

    public function testSubstringWithLengthArgument() {
      $syntax = new SQLiteSyntax($this->createConnectionFixture());
      $this->assertSame(
        "SUBSTRING('Hello World!', '6', '2')",
        $syntax->substring('Hello World!', 6, 2)
      );
    }

    public function testLike() {
      $syntax = new SQLiteSyntax($this->createConnectionFixture());
      $this->assertSame(
        "LIKE 'value' ESCAPE '\\\\'",
        $syntax->like('value')
      );
    }

    public function testLikeWithIdentifier() {
      $syntax = new SQLiteSyntax($this->createConnectionFixture());
      $this->assertSame(
        'LIKE `field` ESCAPE \'\\\\\'',
        $syntax->like($syntax->identifier('field'))
      );
    }

    public function testLower() {
      $syntax = new SQLiteSyntax($this->createConnectionFixture());
      $this->assertSame(
        'LOWER(`field`)',
        $syntax->lower($syntax->identifier('field'))
      );
    }

    public function testUpper() {
      $syntax = new SQLiteSyntax($this->createConnectionFixture());
      $this->assertSame(
        'UPPER(`field`)',
        $syntax->upper($syntax->identifier('field'))
      );
    }

    public function testLimit() {
      $syntax = new SQLiteSyntax($this->createConnectionFixture());
      $this->assertSame(
        ' LIMIT 10',
        $syntax->limit(10)
      );
    }

    public function testLimitWithLimitIsZero() {
      $syntax = new SQLiteSyntax($this->createConnectionFixture());
      $this->assertSame(
        '',
        $syntax->limit(0)
      );
    }

    public function testLimitWithOffset() {
      $syntax = new SQLiteSyntax($this->createConnectionFixture());
      $this->assertSame(
        ' LIMIT 20,10',
        $syntax->limit(10, 20)
      );
    }

    public function testRandom() {
      $syntax = new SQLiteSyntax($this->createConnectionFixture());
      $this->assertSame(
        'RANDOM()',
        $syntax->random()
      );

    }

    public function testGetDialect() {
      $syntax = new SQLiteSyntax($this->createConnectionFixture());
      $this->assertSame('sqlite', $syntax->getDialect());
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
