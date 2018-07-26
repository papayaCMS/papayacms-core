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

require_once __DIR__.'/../../../../../../../../bootstrap.php';

class PapayaUiDialogFieldInputMediaImageResizedTest extends \PapayaTestCase {

  /**
   * @covers \PapayaUiDialogFieldInputMediaImageResized::__construct
   * @dataProvider provideValuesForFilterValidation
   * @param string $value
   * @throws \PapayaFilterException
   */
  public function testConstructorInitializesFilter($value) {
    $field = new \PapayaUiDialogFieldInputMediaImageResized('caption', 'name', TRUE);
    $this->assertTrue($field->getFilter()->validate($value));
  }

  /**
   * @covers \PapayaUiDialogFieldInputMediaImageResized::__construct
   * @dataProvider provideInvalidValuesForFilterValidation
   * @param string $value
   * @throws \PapayaFilterException
   */
  public function testConstructorInitializesFilterExpectingExceptionForInvalidValues($value) {
    $field = new \PapayaUiDialogFieldInputMediaImageResized('caption', 'name', TRUE);
    $this->expectException(\PapayaFilterException::class);
    $field->getFilter()->validate($value);
  }

  public static function provideValuesForFilterValidation() {
    return array(
      array('123456789012345678901234567890ab'),
      array('123456789012345678901234567890ab,320'),
      array('123456789012345678901234567890ab,320,240'),
      array('123456789012345678901234567890ab,320,240,max')
    );
  }

  public static function provideInvalidValuesForFilterValidation() {
    return array(
      array(''),
      array('foo'),
      array('123456789012345678901234567890ab,foo'),
      array('123456789012345678901234567890ab,320,foo'),
      array('123456789012345678901234567890ab,320,240,foo')
    );
  }
}
