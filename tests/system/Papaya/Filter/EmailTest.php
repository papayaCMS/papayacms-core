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

namespace Papaya\Filter;
require_once __DIR__.'/../../../bootstrap.php';

class EmailTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Filter\Email::validate
   */
  public function testValidateExpectingTrue() {
    $filter = new Email();
    $this->assertTrue($filter->validate('info@papaya-cms.com'));
  }

  /**
   * @covers \Papaya\Filter\Email::validate
   */
  public function testValidateExpectingException() {
    $filter = new Email();
    $this->expectException(Exception\UnexpectedType::class);
    $filter->validate('invalid email @dress');
  }

  /**
   * @covers \Papaya\Filter\Email::filter
   * @dataProvider provideFilterData
   * @param string|NULL $expected
   * @param mixed $input
   */
  public function testFilter($expected, $input) {
    $filter = new Email();
    $this->assertEquals($expected, $filter->filter($input));
  }

  /**********************
   * Data Provider
   **********************/

  public static function provideFilterData() {
    return array(
      'valid' => array('info@papaya-cms.com', 'info@papaya-cms.com'),
      'invalid domain' => array(NULL, 'info@papaya cms.com'),
      'invalid prefix' => array(NULL, 'i n f o@papaya-cms.com'),
      'invalid tld' => array(NULL, 'info@papaya-cms.')
    );
  }
}
