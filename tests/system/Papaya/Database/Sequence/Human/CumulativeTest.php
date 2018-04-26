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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaDatabaseSequenceHumanCumulativeTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseSequenceHumanCumulative::__construct
  */
  public function testConstructor() {
    $sequence = new PapayaDatabaseSequenceHumanCumulative('table', 'field');
    $this->assertAttributeEquals(2, '_minimumLength', $sequence);
    $this->assertAttributeEquals(32, '_maximumLength', $sequence);
    $this->assertAttributeEquals(32, '_cumulativeLength', $sequence);
  }

  /**
  * @covers PapayaDatabaseSequenceHumanCumulative::__construct
  */
  public function testConstructorWithParameters() {
    $sequence = new PapayaDatabaseSequenceHumanCumulative('table', 'field', 21, 42);
    $this->assertAttributeEquals(21, '_minimumLength', $sequence);
    $this->assertAttributeEquals(42, '_maximumLength', $sequence);
    $this->assertAttributeEquals(42, '_cumulativeLength', $sequence);
  }

  /**
  * @covers PapayaDatabaseSequenceHumanCumulative::__construct
  */
  public function testConstructorWithInvalidLengthLimits() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Minimum length can not be greater then maximum length.');
    new PapayaDatabaseSequenceHumanCumulative('table', 'field', 42, 21);
  }

  /**
  * @covers PapayaDatabaseSequenceHumanCumulative::create
  */
  public function testCreate() {
    $sequence = new PapayaDatabaseSequenceHumanCumulative('table', 'field', 4, 7);
    $this->assertStringLength(7, $sequence->create());
  }

  /**
  * @covers PapayaDatabaseSequenceHumanCumulative::create
  * @covers PapayaDatabaseSequenceHumanCumulative::createIdentifiers
  */
  public function testCreateIdentifiersHaveIncreasingLength() {
    $sequence = new PapayaDatabaseSequenceHumanCumulative_TestProxy('table', 'field', 4, 6);
    $results = $sequence->createIdentifiers(6);
    $this->assertStringLength(4, $results[0]);
    $this->assertStringLength(4, $results[1]);
    $this->assertStringLength(5, $results[2]);
    $this->assertStringLength(5, $results[3]);
    $this->assertStringLength(6, $results[4]);
    $this->assertStringLength(6, $results[5]);
  }

  /**
  * @covers PapayaDatabaseSequenceHumanCumulative::create
  * @covers PapayaDatabaseSequenceHumanCumulative::createIdentifiers
  */
  public function testCreateIdentifiersHaveReachMaximumLength() {
    $sequence = new PapayaDatabaseSequenceHumanCumulative_TestProxy('table', 'field', 4, 32);
    $results = $sequence->createIdentifiers(2);
    $this->assertStringLength(4, $results[0]);
    $this->assertStringLength(32, $results[1]);
  }

  /**
  * @covers PapayaDatabaseSequenceHumanCumulative::create
  * @covers PapayaDatabaseSequenceHumanCumulative::createIdentifiers
  */
  public function testCreateIdentifiersWhileMinimumEqualsMaximum() {
    $sequence = new PapayaDatabaseSequenceHumanCumulative_TestProxy('table', 'field', 10, 10);
    $results = $sequence->createIdentifiers(2);
    $this->assertStringLength(10, $results[0]);
    $this->assertStringLength(10, $results[1]);
  }

  /**
  * @covers PapayaDatabaseSequenceHumanCumulative::create
  * @covers PapayaDatabaseSequenceHumanCumulative::createIdentifiers
  */
  public function testCreateSingleIdentifierWhileMinimumDiffersMaximum() {
    $sequence = new PapayaDatabaseSequenceHumanCumulative_TestProxy('table', 'field', 2, 10);
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

class PapayaDatabaseSequenceHumanCumulative_TestProxy
  extends PapayaDatabaseSequenceHumanCumulative {

  public function createIdentifiers($count) {
    return parent::createIdentifiers($count);
  }

}
