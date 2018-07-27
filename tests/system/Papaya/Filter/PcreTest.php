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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterPcreTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Filter\Pcre::__construct
  */
  public function testConstructor() {
    $filter = new \Papaya\Filter\Pcre('(foo)');
    $this->assertAttributeEquals(
      '(foo)', '_pattern', $filter
    );
  }

  /**
  * @covers \Papaya\Filter\Pcre::__construct
  */
  public function testConstructorWithSubMatch() {
    $filter = new \Papaya\Filter\Pcre('(foo)', 1);
    $this->assertAttributeEquals(
      1, '_subMatch', $filter
    );
  }

  /**
  * @covers \Papaya\Filter\Pcre::validate
  */
  public function testValidate() {
    $filter = new \Papaya\Filter\Pcre('(^foo$)');
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
  * @covers \Papaya\Filter\Pcre::validate
  */
  public function testValidateExpectingException() {
    $filter = new \Papaya\Filter\Pcre('(^foo$)');
    $this->expectException(\Papaya\Filter\Exception\RegEx\NoMatch::class);
    $filter->validate('bar');
  }

  /**
  * @covers \Papaya\Filter\Pcre::filter
  */
  public function testFilter() {
    $filter = new \Papaya\Filter\Pcre('(^foo$)');
    $this->assertEquals(
      'foo', $filter->filter('foo')
    );
  }

  /**
  * @covers \Papaya\Filter\Password::filter
  */
  public function testFilterExpectingNull() {
    $filter = new \Papaya\Filter\Pcre('(^foo$)');
    $this->assertNull(
      $filter->filter('bar')
    );
  }

  /**
  * @covers \Papaya\Filter\Pcre::filter
  */
  public function testFilterExpectingSubMatch() {
    $filter = new \Papaya\Filter\Pcre('(^f(oo)$)', 1);
    $this->assertEquals(
      'oo', $filter->filter('foo')
    );
  }

  /**
  * @covers \Papaya\Filter\Pcre::filter
  */
  public function testFilterExpectingNamedSubMatch() {
    $filter = new \Papaya\Filter\Pcre(/** @lang Text */'(^f(?P<part>oo)$)', 'part');
    $this->assertEquals(
      'oo', $filter->filter('foo')
    );
  }

  /**
  * @covers \Papaya\Filter\Pcre::filter
  */
  public function testFilterWithInvalidSubMatchExpectingNull() {
    $filter = new \Papaya\Filter\Pcre('(^f(oo)$)', 'part');
    $this->assertNull(
      $filter->filter('foo')
    );
  }
}
