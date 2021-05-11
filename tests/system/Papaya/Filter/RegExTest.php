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
 * @covers \Papaya\Filter\RegEx
 */
class RegExTest extends \Papaya\TestCase {

  public function testValidate() {
    $filter = new RegEx('(^foo$)');
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  public function testValidateExpectingException() {
    $filter = new RegEx('(^foo$)');
    $this->expectException(Exception\RegEx\NoMatch::class);
    $filter->validate('bar');
  }

  public function testFilter() {
    $filter = new RegEx('(^foo$)');
    $this->assertEquals(
      'foo', $filter->filter('foo')
    );
  }

  public function testFilterExpectingNull() {
    $filter = new RegEx('(^foo$)');
    $this->assertNull(
      $filter->filter('bar')
    );
  }

  public function testFilterExpectingSubMatch() {
    $filter = new RegEx('(^f(oo)$)', 1);
    $this->assertEquals(
      'oo', $filter->filter('foo')
    );
  }

  public function testFilterExpectingNamedSubMatch() {
    $filter = new RegEx(/** @lang Text */
      '(^f(?P<part>oo)$)', 'part');
    $this->assertEquals(
      'oo', $filter->filter('foo')
    );
  }

  public function testFilterWithInvalidSubMatchExpectingNull() {
    $filter = new RegEx('(^f(oo)$)', 'part');
    $this->assertNull(
      $filter->filter('foo')
    );
  }
}
