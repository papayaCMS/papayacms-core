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

namespace Papaya\Database\Sequence\Human {

  require_once __DIR__.'/../../../../../bootstrap.php';

  class CumulativeTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers \Papaya\Database\Sequence\Human\Cumulative::__construct
     */
    public function testConstructor() {
      $sequence = new Cumulative('table', 'field');
      $this->assertEquals(2,  $sequence->getMinimumLength());
      $this->assertEquals(32, $sequence->getMaximumLength());
      $this->assertEquals(32, $sequence->getCumulativeLength());
    }

    /**
     * @covers \Papaya\Database\Sequence\Human\Cumulative::__construct
     */
    public function testConstructorWithParameters() {
      $sequence = new Cumulative('table', 'field', 21, 42);
      $this->assertEquals(21, $sequence->getMinimumLength());
      $this->assertEquals(42, $sequence->getMaximumLength());
      $this->assertEquals(42, $sequence->getCumulativeLength());
    }

    /**
     * @covers \Papaya\Database\Sequence\Human\Cumulative::__construct
     */
    public function testConstructorWithInvalidLengthLimits() {
      $this->expectException(\InvalidArgumentException::class);
      $this->expectExceptionMessage('Minimum length can not be greater then maximum length.');
      new Cumulative('table', 'field', 42, 21);
    }

    /**
     * @covers \Papaya\Database\Sequence\Human\Cumulative::create
     */
    public function testCreate() {
      $sequence = new Cumulative('table', 'field', 4, 7);
      $this->assertStringLength(7, $sequence->create());
    }

    /**
     * @covers \Papaya\Database\Sequence\Human\Cumulative::create
     * @covers \Papaya\Database\Sequence\Human\Cumulative::createIdentifiers
     */
    public function testCreateIdentifiersHaveIncreasingLength() {
      $sequence = new Cumulative_TestProxy('table', 'field', 4, 6);
      $results = $sequence->createIdentifiers(6);
      $this->assertStringLength(4, $results[0]);
      $this->assertStringLength(4, $results[1]);
      $this->assertStringLength(5, $results[2]);
      $this->assertStringLength(5, $results[3]);
      $this->assertStringLength(6, $results[4]);
      $this->assertStringLength(6, $results[5]);
    }

    /**
     * @covers \Papaya\Database\Sequence\Human\Cumulative::create
     * @covers \Papaya\Database\Sequence\Human\Cumulative::createIdentifiers
     */
    public function testCreateIdentifiersHaveReachMaximumLength() {
      $sequence = new Cumulative_TestProxy('table', 'field', 4, 32);
      $results = $sequence->createIdentifiers(2);
      $this->assertStringLength(4, $results[0]);
      $this->assertStringLength(32, $results[1]);
    }

    /**
     * @covers \Papaya\Database\Sequence\Human\Cumulative::create
     * @covers \Papaya\Database\Sequence\Human\Cumulative::createIdentifiers
     */
    public function testCreateIdentifiersWhileMinimumEqualsMaximum() {
      $sequence = new Cumulative_TestProxy('table', 'field', 10, 10);
      $results = $sequence->createIdentifiers(2);
      $this->assertStringLength(10, $results[0]);
      $this->assertStringLength(10, $results[1]);
    }

    /**
     * @covers \Papaya\Database\Sequence\Human\Cumulative::create
     * @covers \Papaya\Database\Sequence\Human\Cumulative::createIdentifiers
     */
    public function testCreateSingleIdentifierWhileMinimumDiffersMaximum() {
      $sequence = new Cumulative_TestProxy('table', 'field', 2, 10);
      $results = $sequence->createIdentifiers(1);
      $this->assertStringLength(10, $results[0]);
    }

    private function assertStringLength($expectedLength, $string) {
      $actualLength = strlen($string);
      $this->assertEquals(
        $expectedLength,
        $actualLength,
        sprintf(
          'Failed asserting that string length "%d" is equal to "%d"',
          $actualLength,
          $expectedLength
        )
      );
    }
  }

  class Cumulative_TestProxy
    extends Cumulative {

    public function createIdentifiers($count) {
      return parent::createIdentifiers($count);
    }

  }
}
