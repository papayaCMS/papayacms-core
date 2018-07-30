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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaMessageContextRuntimeTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Message\Context\Runtime::__construct
  */
  public function testConstructorWithoutParameters() {
    \Papaya\Message\Context\Runtime::setStartTime(0);
    $context = new \Papaya\Message\Context\Runtime();
    $this->assertAttributeGreaterThan(
      0,
      '_startTime',
      \Papaya\Message\Context\Runtime::class
    );
    $this->assertAttributeGreaterThan(
      0,
      '_previousTime',
      \Papaya\Message\Context\Runtime::class
    );
    $this->assertAttributeEquals(
      \Papaya\Message\Context\Runtime::MODE_GLOBAL,
      '_mode',
      $context
    );
  }

  /**
  * @covers \Papaya\Message\Context\Runtime::__construct
  */
  public function testConstructorWithParameters() {
    $context = new \Papaya\Message\Context\Runtime(23, 42);
    $this->assertAttributeEquals(
      19,
      '_neededTime',
      $context
    );
    $this->assertAttributeEquals(
      42,
      '_currentTime',
      $context
    );
    $this->assertAttributeEquals(
      \Papaya\Message\Context\Runtime::MODE_SINGLE,
      '_mode',
      $context
    );
  }

  /**
  * @covers \Papaya\Message\Context\Runtime::setTimeValues
  * @covers \Papaya\Message\Context\Runtime::_prepareTimeValue
  * @dataProvider setTimeValuesDataProvider
  *
   * @param float $expectedDiff
   * @param float $expectedStop
  * @param float|integer|string $start
  * @param float|integer|string $stop
  */
  public function testSetTimeValues($expectedDiff, $expectedStop, $start, $stop) {
    $context = new \Papaya\Message\Context\Runtime();
    $context->setTimeValues($start, $stop);
    $this->assertAttributeEquals(
      $expectedDiff,
      '_neededTime',
      $context,
      '',
      0.000001
    );
    $this->assertAttributeEquals(
      $expectedStop,
      '_currentTime',
      $context,
      '',
      0.000001
    );
  }

  /**
  * @covers \Papaya\Message\Context\Runtime::setStartTime
  */
  public function testSetStartTime() {
    \Papaya\Message\Context\Runtime::setStartTime(42);
    $this->assertAttributeEquals(
      42,
      '_startTime',
      \Papaya\Message\Context\Runtime::class
    );
    $this->assertAttributeEquals(
      42,
      '_previousTime',
      \Papaya\Message\Context\Runtime::class
    );
  }

  /**
  * @covers \Papaya\Message\Context\Runtime::rememberTime
  */
  public function testRememberTime() {
    \Papaya\Message\Context\Runtime::rememberTime(42);
    $this->assertAttributeEquals(
      42,
      '_previousTime',
      \Papaya\Message\Context\Runtime::class
    );
  }

  /**
  * @covers \Papaya\Message\Context\Runtime::asString
  */
  public function testAsStringInGlobalMode() {
    $context = new \Papaya\Message\Context\Runtime();
    \Papaya\Message\Context\Runtime::setStartTime(23);
    $context->setTimeValues(42, 77);
    $this->assertEquals(
      'Time: 54s 0ms (+35s 0ms)',
      $context->asString()
    );
  }

  /**
  * @covers \Papaya\Message\Context\Runtime::asString
  */
  public function testAsStringInSingleMode() {
    $context = new \Papaya\Message\Context\Runtime(42, 77);
    $this->assertEquals(
      'Time needed: 35s 0ms',
      $context->asString()
    );
  }

  /*************************************
  * Data Provider
  *************************************/

  public static function setTimeValuesDataProvider() {
    return array(
      'integers' => array(19, 42, 23, 42),
      'strings' => array(19.5, 42.7, '0.2 23', '0.7 42'),
      'floats' => array(19.5, 42.7, 23.2, 42.7)
    );
  }
}
