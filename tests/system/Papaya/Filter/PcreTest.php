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

class PcreTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Filter\Pcre::__construct
   */
  public function testConstructor() {
    $filter = new Pcre('(foo)');
    $this->assertAttributeEquals(
      '(foo)', '_pattern', $filter
    );
  }

  /**
   * @covers \Papaya\Filter\Pcre::__construct
   */
  public function testConstructorWithSubMatch() {
    $filter = new Pcre('(foo)', 1);
    $this->assertAttributeEquals(
      1, '_subMatch', $filter
    );
  }

  /**
   * @covers \Papaya\Filter\Pcre::validate
   */
  public function testValidate() {
    $filter = new Pcre('(^foo$)');
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
   * @covers \Papaya\Filter\Pcre::validate
   */
  public function testValidateExpectingException() {
    $filter = new Pcre('(^foo$)');
    $this->expectException(Exception\RegEx\NoMatch::class);
    $filter->validate('bar');
  }

  /**
   * @covers \Papaya\Filter\Pcre::filter
   */
  public function testFilter() {
    $filter = new Pcre('(^foo$)');
    $this->assertEquals(
      'foo', $filter->filter('foo')
    );
  }

  /**
   * @covers \Papaya\Filter\Password::filter
   */
  public function testFilterExpectingNull() {
    $filter = new Pcre('(^foo$)');
    $this->assertNull(
      $filter->filter('bar')
    );
  }

  /**
   * @covers \Papaya\Filter\Pcre::filter
   */
  public function testFilterExpectingSubMatch() {
    $filter = new Pcre('(^f(oo)$)', 1);
    $this->assertEquals(
      'oo', $filter->filter('foo')
    );
  }

  /**
   * @covers \Papaya\Filter\Pcre::filter
   */
  public function testFilterExpectingNamedSubMatch() {
    $filter = new Pcre(/** @lang Text */
      '(^f(?P<part>oo)$)', 'part');
    $this->assertEquals(
      'oo', $filter->filter('foo')
    );
  }

  /**
   * @covers \Papaya\Filter\Pcre::filter
   */
  public function testFilterWithInvalidSubMatchExpectingNull() {
    $filter = new Pcre('(^f(oo)$)', 'part');
    $this->assertNull(
      $filter->filter('foo')
    );
  }
}
