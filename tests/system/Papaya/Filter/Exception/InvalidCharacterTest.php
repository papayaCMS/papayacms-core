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

namespace Papaya\Filter\Exception;

require_once __DIR__.'/../../../../bootstrap.php';

class InvalidCharacterTest extends \PapayaTestCase {

  /**
   * @covers       \Papaya\Filter\Exception\InvalidCharacter::__construct
   * @dataProvider provideExceptionDataAndMessage
   * @param string $expected
   * @param string $value
   * @param int $offset
   */
  public function testConstructor($expected, $value, $offset) {
    $e = new InvalidCharacter($value, $offset);
    $this->assertAttributeEquals(
      $offset, '_characterPosition', $e
    );
    $this->assertEquals(
      $expected, $e->getMessage()
    );
  }

  /**
   * @covers \Papaya\Filter\Exception\InvalidCharacter::getCharacterPosition
   */
  public function testGetCharacterPosition() {
    $e = new InvalidCharacter('', 42);
    $this->assertEquals(
      42, $e->getCharacterPosition()
    );
  }

  /**************************
   * Data Provider
   **************************/

  public static function provideExceptionDataAndMessage() {
    return array(
      'short' => array('Invalid character in value "Invalid" at offset #5.', 'Invalid', 5),
      'large' => array(
        'Invalid character at offset #34 near "------------------------------Inva".',
        str_repeat('-', 30).'Invalid'.str_repeat('-', 30), 34
      ),
      'offset > 50' => array(
        'Invalid character at offset #54 near'.
        ' "----------------------------------------------Inva".',
        str_repeat('-', 50).'Invalid'.str_repeat('-', 30), 54
      )
    );
  }
}
