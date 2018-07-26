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

class PapayaFilterStringNormalizeTest extends PapayaTestCase {

  /**
   * @covers \PapayaFilterStringNormalize
   */
  public function testValidateExpectingTrue() {
    $filter = new \PapayaFilterStringNormalize();
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
   * @covers \PapayaFilterStringNormalize
   */
  public function testValidateWithEmptyValueExpectingException() {
    $filter = new \PapayaFilterStringNormalize();
    $this->expectException(PapayaFilterExceptionEmpty::class);
    $filter->validate('');
  }

  /**
   * @covers \PapayaFilterStringNormalize
   */
  public function testValidateWithArrayValueExpectingException() {
    $filter = new \PapayaFilterStringNormalize();
    $this->expectException(PapayaFilterExceptionType::class);
    $filter->validate(['foo']);
  }

  /**
   * @covers \PapayaFilterStringNormalize
   * @dataProvider provideValuesToNormalize
   * @param string|NULL $expected
   * @param mixed $provided
   * @param int $options
   */
  public function testFilter($expected, $provided, $options = 0) {
    $filter = new \PapayaFilterStringNormalize($options);
    $this->assertSame($expected, $filter->filter($provided));
  }

  public static function provideValuesToNormalize() {
    return [
      [NULL, ''],
      [NULL, []],
      ['trim', ' trim '],
      ['123', ' 123 '],
      ['Keep UpperCase', ' Keep   UpperCase '],
      ['to lowercase', ' To   LowerCase ', \PapayaFilterStringNormalize::OPTION_LOWERCASE]
    ];
  }
}
