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

class PapayaFilterPcreTest extends PapayaTestCase {

  /**
  * @covers \PapayaFilterPcre::__construct
  */
  public function testConstructor() {
    $filter = new \PapayaFilterPcre('(foo)');
    $this->assertAttributeEquals(
      '(foo)', '_pattern', $filter
    );
  }

  /**
  * @covers \PapayaFilterPcre::__construct
  */
  public function testConstructorWithSubMatch() {
    $filter = new \PapayaFilterPcre('(foo)', 1);
    $this->assertAttributeEquals(
      1, '_subMatch', $filter
    );
  }

  /**
  * @covers \PapayaFilterPcre::validate
  */
  public function testValidate() {
    $filter = new \PapayaFilterPcre('(^foo$)');
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
  * @covers \PapayaFilterPcre::validate
  */
  public function testValidateExpectingException() {
    $filter = new \PapayaFilterPcre('(^foo$)');
    $this->expectException(PapayaFilterExceptionPcre::class);
    $filter->validate('bar');
  }

  /**
  * @covers \PapayaFilterPcre::filter
  */
  public function testFilter() {
    $filter = new \PapayaFilterPcre('(^foo$)');
    $this->assertEquals(
      'foo', $filter->filter('foo')
    );
  }

  /**
  * @covers \PapayaFilterPassword::filter
  */
  public function testFilterExpectingNull() {
    $filter = new \PapayaFilterPcre('(^foo$)');
    $this->assertNull(
      $filter->filter('bar')
    );
  }

  /**
  * @covers \PapayaFilterPcre::filter
  */
  public function testFilterExpectingSubMatch() {
    $filter = new \PapayaFilterPcre('(^f(oo)$)', 1);
    $this->assertEquals(
      'oo', $filter->filter('foo')
    );
  }

  /**
  * @covers \PapayaFilterPcre::filter
  */
  public function testFilterExpectingNamedSubMatch() {
    $filter = new \PapayaFilterPcre(/** @lang Text */'(^f(?P<part>oo)$)', 'part');
    $this->assertEquals(
      'oo', $filter->filter('foo')
    );
  }

  /**
  * @covers \PapayaFilterPcre::filter
  */
  public function testFilterWithInvalidSubMatchExpectingNull() {
    $filter = new \PapayaFilterPcre('(^f(oo)$)', 'part');
    $this->assertNull(
      $filter->filter('foo')
    );
  }
}
