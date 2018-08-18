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

class NotNullTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Filter\NotNull::validate
   * @dataProvider provideValues
   * @param mixed $value
   * @throws Exception\IsUndefined
   */
  public function testCheck($value) {
    $filter = new NotNull();
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @covers \Papaya\Filter\NotNull::validate
   */
  public function testCheckExpectingException() {
    $filter = new NotNull();
    $this->expectException(Exception\IsUndefined::class);
    $filter->validate(NULL);
  }

  /**
   * @covers \Papaya\Filter\NotNull::filter
   * @dataProvider provideValues
   * @param mixed $value
   */
  public function testFilter($value) {
    $filter = new NotNull();
    $this->assertSame($value, $filter->filter($value));
  }

  /************************
   * Data Provider
   ************************/

  public static function provideValues() {
    return array(
      array(''),
      array(' '),
      array('0'),
      array(array()),
      array('some'),
      array('0'),
      array(array('0'))
    );
  }
}
