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

class PapayaFilterBooleanStringTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Filter\BooleanString
   * @dataProvider provideValidBooleanStrings
   * @param mixed $expected
   * @param mixed $value
   * @throws PapayaFilterException
   */
  public function testValidateExpectingTrue(
    /** @noinspection PhpUnusedParameterInspection */
    $expected, $value
  ) {
    $filter = new \Papaya\Filter\BooleanString();
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @covers \Papaya\Filter\BooleanString
   * @dataProvider provideInvalidBooleanStrings
   * @param mixed $value
   * @throws PapayaFilterException
   */
  public function testValidateExpectingException($value) {
    $filter = new \Papaya\Filter\BooleanString();
    $this->expectException(\PapayaFilterException::class);
    $filter->validate($value);
  }

  /**
   * @covers \Papaya\Filter\BooleanString
   * @dataProvider provideValidBooleanStrings
   * @param mixed $expected
   * @param mixed $value
   */
  public function testFilter($expected, $value) {
    $filter = new \Papaya\Filter\BooleanString();
    $this->assertSame($expected, $filter->filter($value));
  }

  /**
   * @covers \Papaya\Filter\BooleanString
   * @dataProvider provideInvalidBooleanStrings
   * @param mixed $value
   */
  public function testFilterExpectingNull($value) {
    $filter = new \Papaya\Filter\BooleanString();
    $this->assertNull($filter->filter($value));
  }


  /**
   * @covers \Papaya\Filter\BooleanString
   */
  public function testFilterWithoutCastingEmptyStringExpectingNull() {
    $filter = new \Papaya\Filter\BooleanString(FALSE);
    $this->assertNull($filter->filter(''));
  }


  public static function provideValidBooleanStrings() {
    return array(
      array(TRUE, TRUE),
      array(TRUE, 1),
      array(TRUE, 42),
      array(TRUE, 'yes'),
      array(TRUE, 'YES'),
      array(TRUE, 'Yes'),
      array(TRUE, 'true'),
      array(TRUE, 'TRUE'),
      array(TRUE, 'True'),
      array(TRUE, 'TruE'),
      array(TRUE, 'abc'),
      array(TRUE, '012'),
      array(TRUE, '0ab'),
      array(FALSE, FALSE),
      array(FALSE, 0),
      array(FALSE, 'no'),
      array(FALSE, 'NO'),
      array(FALSE, 'No'),
      array(FALSE, 'false'),
      array(FALSE, 'FALSE'),
      array(FALSE, 'False'),
      array(FALSE, '0'),
      array(FALSE, ''),
      array(FALSE, '   ')
    );
  }

  public static function provideInvalidBooleanStrings() {
    return array(
      array(NULL)
    );
  }
}
