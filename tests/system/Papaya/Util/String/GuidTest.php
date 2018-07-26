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

class PapayaUtilStringGuidTest extends PapayaTestCase {

  /**
  * @covers \PapayaUtilStringGuid::validate
  */
  public function testValidateExpectingTrue() {
    $this->assertTrue(
      PapayaUtilStringGuid::validate('aB123456789012345678901234567890')
    );
  }

  /**
  * @covers \PapayaUtilStringGuid::validate
  */
  public function testValidateExpectingFalse() {
    $this->assertFalse(
      PapayaUtilStringGuid::validate('invalid', TRUE)
    );
  }

  /**
  * @covers \PapayaUtilStringGuid::validate
  */
  public function testValidateExpectingException() {
    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('Invalid guid: "invalid".');
    PapayaUtilStringGuid::validate('invalid');
  }

  /**
  * @covers \PapayaUtilStringGuid::toLower
  */
  public function testToLower() {
    $this->assertEquals(
      'ab123456789012345678901234567890',
      PapayaUtilStringGuid::toLower('aB123456789012345678901234567890')
    );
  }

  /**
  * @covers \PapayaUtilStringGuid::toLower
  */
  public function testToLowerWithInvalidValueSilentExpectingEmptyString() {
    $this->assertEquals(
      '', PapayaUtilStringGuid::toLower('invalid', TRUE)
    );
  }

  /**
  * @covers \PapayaUtilStringGuid::toUpper
  */
  public function testToUpper() {
    $this->assertEquals(
      'AB123456789012345678901234567890',
      PapayaUtilStringGuid::toUpper('aB123456789012345678901234567890')
    );
  }

  /**
  * @covers \PapayaUtilStringGuid::toUpper
  */
  public function testToUpperWithInvalidValueSilentExpectingEmptyString() {
    $this->assertEquals(
      '', PapayaUtilStringGuid::toUpper('invalid', TRUE)
    );
  }
}
