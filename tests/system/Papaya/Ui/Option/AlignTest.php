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

class PapayaUiOptionAlignTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\UI\Option\Align::getString
  */
  public function testGetString() {
    $this->assertEquals(
      'center',
      \Papaya\UI\Option\Align::getString(\Papaya\UI\Option\Align::CENTER)
    );
  }

  /**
  * @covers \Papaya\UI\Option\Align::getString
  */
  public function testGetStringWithInvalidValueExpectingLeft() {
    $this->assertEquals(
      'left',
      \Papaya\UI\Option\Align::getString(-42)
    );
  }

  /**
  * @covers \Papaya\UI\Option\Align::validate
  */
  public function testValidate() {
    $this->assertTrue(
      \Papaya\UI\Option\Align::validate(\Papaya\UI\Option\Align::CENTER)
    );
  }

  /**
  * @covers \Papaya\UI\Option\Align::validate
  */
  public function testValidateWithInvalidValue() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('InvalidArgumentException: Invalid align value "-42".');
    \Papaya\UI\Option\Align::validate(-42);
  }

  /**
  * @covers \Papaya\UI\Option\Align::validate
  */
  public function testValidateWithInvalidValueAndIndividualMessage() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Failed.');
    \Papaya\UI\Option\Align::validate(-42, 'Failed.');
  }
}
