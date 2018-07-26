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

class PapayaFilterPhoneTest extends \PapayaTestCase {

  /**
   * @covers \PapayaFilterPhone::validate
   * @dataProvider provideValidPhoneNumbers
   * @param string $phoneNumber
   * @throws \PapayaFilterExceptionType
   */
  public function testValidateExpectingTrue($phoneNumber) {
    $filter = new \PapayaFilterPhone();
    $this->assertTrue($filter->validate($phoneNumber));
  }

  /**
   * @covers \PapayaFilterPhone::validate
   * @dataProvider provideInvalidData
   * @param mixed $value
   * @throws \PapayaFilterExceptionType
   */
  public function testValidateExpectingException($value) {
    $filter = new \PapayaFilterPhone();
    $this->expectException(\PapayaFilterExceptionType::class);
    $filter->validate($value);
  }

  /**
   * @covers \PapayaFilterPhone::filter
   * @dataProvider provideFilterData
   * @param string|NULL $expected
   * @param mixed $input
   */
  public function testFilter($expected, $input) {
    $filter = new \PapayaFilterPhone();
    $this->assertEquals($expected, $filter->filter($input));
  }

  /**********************
  * Data Provider
  **********************/

  public static function provideValidPhoneNumbers() {
    return array(
      array('022157438070'),
      array('0221-5743-8070'),
      array('+49 221 5743-8070'),
      array('0049 221 5743-8070'),
      array('(0221) 5743-8070'),
      array('0221 5743-8070'),
      array('5743-8070'),
      array('5743 8070')
    );
  }

  public static function provideInvalidData() {
    return array(
      array('-49 0221 5743-8070'),
      array('no phone number'),
      array('24   53'),
    );
  }

  public static function provideFilterData() {
    return array(
      'valid' => array('1234567890', '1234567890'),
      'invalid signs' => array(NULL, '7389ksjdhu')
    );
  }
}
