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

namespace Papaya\Utility\Text;
require_once __DIR__.'/../../../../bootstrap.php';

class GuidTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Utility\Text\Guid::validate
   */
  public function testValidateExpectingTrue() {
    $this->assertTrue(
      Guid::validate('aB123456789012345678901234567890')
    );
  }

  /**
   * @covers \Papaya\Utility\Text\Guid::validate
   */
  public function testValidateExpectingFalse() {
    $this->assertFalse(
      Guid::validate('invalid', TRUE)
    );
  }

  /**
   * @covers \Papaya\Utility\Text\Guid::validate
   */
  public function testValidateExpectingException() {
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('Invalid guid: "invalid".');
    Guid::validate('invalid');
  }

  /**
   * @covers \Papaya\Utility\Text\Guid::toLower
   */
  public function testToLower() {
    $this->assertEquals(
      'ab123456789012345678901234567890',
      Guid::toLower('aB123456789012345678901234567890')
    );
  }

  /**
   * @covers \Papaya\Utility\Text\Guid::toLower
   */
  public function testToLowerWithInvalidValueSilentExpectingEmptyString() {
    $this->assertEquals(
      '', Guid::toLower('invalid', TRUE)
    );
  }

  /**
   * @covers \Papaya\Utility\Text\Guid::toUpper
   */
  public function testToUpper() {
    $this->assertEquals(
      'AB123456789012345678901234567890',
      Guid::toUpper('aB123456789012345678901234567890')
    );
  }

  /**
   * @covers \Papaya\Utility\Text\Guid::toUpper
   */
  public function testToUpperWithInvalidValueSilentExpectingEmptyString() {
    $this->assertEquals(
      '', Guid::toUpper('invalid', TRUE)
    );
  }
}
