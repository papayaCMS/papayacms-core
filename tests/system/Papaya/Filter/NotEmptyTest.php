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

/**
 * @covers \Papaya\Filter\NotEmpty
 */
class NotEmptyTest extends \Papaya\TestFramework\TestCase {

  /**
   * @dataProvider provideNonEmptyValues
   * @param mixed $value
   * @param bool $ignoreSpaces
   * @throws \Papaya\Filter\Exception
   */
  public function testValidate($value, $ignoreSpaces) {
    $filter = new NotEmpty($ignoreSpaces);
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @dataProvider provideEmptyValues
   * @param mixed $value
   * @param bool $ignoreSpaces
   * @throws \Papaya\Filter\Exception
   */
  public function testValidateExpectingException($value, $ignoreSpaces) {
    $filter = new NotEmpty($ignoreSpaces);
    $this->expectException(\Papaya\Filter\Exception\IsEmpty::class);
    $filter->validate($value);
  }

  public function testFilterExpectingNull() {
    $filter = new NotEmpty();
    $this->assertNull($filter->filter(''));
  }

  public function testFilterWithEmptyArrayExpectingNull() {
    $filter = new NotEmpty();
    $this->assertNull($filter->filter(array()));
  }

  public function testFilterExpectingValue() {
    $filter = new NotEmpty();
    $this->assertEquals('some', $filter->filter('some'));
  }

  public function testFilterWithArrayExpectingValue() {
    $filter = new NotEmpty();
    $this->assertEquals(array('some'), $filter->filter(array('some')));
  }

  public function testFilterExpectingTrimmedValue() {
    $filter = new NotEmpty();
    $this->assertEquals('some', $filter->filter(' some '));
  }

  public function testFilterExpectingWhitespaceValue() {
    $filter = new NotEmpty(FALSE);
    $this->assertEquals(' ', $filter->filter(' '));
  }

  /************************
   * Data Provider
   ************************/

  public static function provideNonEmptyValues() {
    return array(
      array('some', FALSE),
      array('some', TRUE),
      array(' ', FALSE),
      array(array('some'), FALSE)
    );
  }

  public static function provideEmptyValues() {
    return array(
      array('', TRUE),
      array('', FALSE),
      array(' ', TRUE),
      array(array(), TRUE)
    );
  }
}
