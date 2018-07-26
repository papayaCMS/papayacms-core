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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterBitmaskTest extends PapayaTestCase {

  /**
  * @covers \PapayaFilterBitmask::__construct
  */
  public function testConstructor() {
    $filter = new \PapayaFilterBitmask(array(1, 2, 4));
    $this->assertAttributeEquals(
      array(1, 2, 4), '_bits', $filter
    );
  }

  /**
   * @covers \PapayaFilterBitmask::validate
   * @dataProvider provideValidBitmasks
   * @param mixed $bitmask
   * @throws PapayaFilterException
   */
  public function testValidateExpectingTrue($bitmask) {
    $filter = new \PapayaFilterBitmask(array(1, 2, 4, 16));
    $this->assertTrue(
      $filter->validate($bitmask)
    );
  }

  /**
   * @covers \PapayaFilterBitmask::validate
   * @dataProvider provideInvalidBitmasks
   * @param mixed $bitmask
   * @throws PapayaFilterException
   */
  public function testValidateExpectingInvalidValueException($bitmask) {
    $filter = new \PapayaFilterBitmask(array(1, 2, 4, 16));
    $this->expectException(PapayaFilterExceptionInvalid::class);
    $filter->validate($bitmask);
  }

  /**
  * @covers \PapayaFilterBitmask::validate
  */
  public function testValidateExpectingInvalidValueTypeException() {
    $filter = new \PapayaFilterBitmask(array(1, 2, 4, 16));
    $this->expectException(PapayaFilterExceptionType::class);
    $filter->validate('fail');
  }

  /**
   * @covers \PapayaFilterBitmask::filter
   * @dataProvider provideValidBitmasks
   * @param mixed $bitmask
   */
  public function testFilterWithValidBitmasks($bitmask) {
    $filter = new \PapayaFilterBitmask(array(1, 2, 4, 16));
    $this->assertEquals(
      $bitmask, $filter->filter($bitmask)
    );
  }

  /**
   * @covers \PapayaFilterBitmask::filter
   * @dataProvider provideInvalidBitmasks
   * @param mixed $bitmask
   */
  public function testFilterWithInvalidBitmasks($bitmask) {
    $filter = new \PapayaFilterBitmask(array(1, 2, 4, 16));
    $this->assertNull(
      $filter->filter($bitmask)
    );
  }

  public static function provideValidBitmasks() {
    return array(
      array(0),
      array(1 | 2),
      array(1 | 16),
      array(1 | 2 | 4 | 16)
    );
  }
  public static function provideInvalidBitmasks() {
    return array(
      array(-1),
      array(32),
      array(1 | 8),
      array(8)
    );
  }
}
