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

namespace Papaya\UI\Option;
require_once __DIR__.'/../../../../bootstrap.php';

class AlignTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\UI\Option\Align::getString
   */
  public function testGetString() {
    $this->assertEquals(
      'center',
      Align::getString(Align::CENTER)
    );
  }

  /**
   * @covers \Papaya\UI\Option\Align::getString
   */
  public function testGetStringWithInvalidValueExpectingLeft() {
    $this->assertEquals(
      'left',
      Align::getString(-42)
    );
  }

  /**
   * @covers \Papaya\UI\Option\Align::validate
   */
  public function testValidate() {
    $this->assertTrue(
      Align::validate(Align::CENTER)
    );
  }

  /**
   * @covers \Papaya\UI\Option\Align::validate
   */
  public function testValidateWithInvalidValue() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('InvalidArgumentException: Invalid align value "-42".');
    Align::validate(-42);
  }

  /**
   * @covers \Papaya\UI\Option\Align::validate
   */
  public function testValidateWithInvalidValueAndIndividualMessage() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Failed.');
    Align::validate(-42, 'Failed.');
  }
}
