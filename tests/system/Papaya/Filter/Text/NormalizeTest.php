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

namespace Papaya\Filter\Text;
require_once __DIR__.'/../../../../bootstrap.php';

class NormalizeTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Filter\Text\Normalize
   */
  public function testValidateExpectingTrue() {
    $filter = new Normalize();
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
   * @covers \Papaya\Filter\Text\Normalize
   */
  public function testValidateWithEmptyValueExpectingException() {
    $filter = new Normalize();
    $this->expectException(\Papaya\Filter\Exception\IsEmpty::class);
    $filter->validate('');
  }

  /**
   * @covers \Papaya\Filter\Text\Normalize
   */
  public function testValidateWithArrayValueExpectingException() {
    $filter = new Normalize();
    $this->expectException(\Papaya\Filter\Exception\UnexpectedType::class);
    $filter->validate(['foo']);
  }

  /**
   * @covers \Papaya\Filter\Text\Normalize
   * @dataProvider provideValuesToNormalize
   * @param string|NULL $expected
   * @param mixed $provided
   * @param int $options
   */
  public function testFilter($expected, $provided, $options = 0) {
    $filter = new Normalize($options);
    $this->assertSame($expected, $filter->filter($provided));
  }

  public static function provideValuesToNormalize() {
    return [
      [NULL, ''],
      [NULL, []],
      ['trim', ' trim '],
      ['123', ' 123 '],
      ['Keep UpperCase', ' Keep   UpperCase '],
      ['to lowercase', ' To   LowerCase ', Normalize::OPTION_LOWERCASE]
    ];
  }
}
