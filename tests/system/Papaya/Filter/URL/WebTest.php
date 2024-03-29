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

namespace Papaya\Filter\URL;
require_once __DIR__.'/../../../../bootstrap.php';

class WebTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Filter\URL\Web::validate
   * @covers \Papaya\Filter\URL\Web::prepare
   * @dataProvider provideValidUrls
   * @param mixed $value
   */
  public function testValidate($value) {
    $filter = new Web();
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @covers \Papaya\Filter\URL\Web::validate
   * @covers \Papaya\Filter\URL\Web::prepare
   * @dataProvider provideInvalidValues
   * @param mixed $value
   */
  public function testValidateExpectingException($value) {
    $filter = new Web();
    $this->expectException(\Papaya\Filter\Exception::class);
    $filter->validate($value);
  }

  /**
   * @covers \Papaya\Filter\URL\Web::filter
   * @covers \Papaya\Filter\URL\Web::prepare
   */
  public function testFilterExpectingNull() {
    $filter = new Web();
    $this->assertNull($filter->filter(''));
  }

  /**
   * @covers \Papaya\Filter\URL\Web::filter
   * @covers \Papaya\Filter\URL\Web::prepare
   */
  public function testFilterExpectingValue() {
    $filter = new Web();
    $this->assertEquals('http://www.sample.tld', $filter->filter('http://www.sample.tld'));
  }

  /**
   * @covers \Papaya\Filter\URL\Web::filter
   * @covers \Papaya\Filter\URL\Web::prepare
   */
  public function testFilterExpectingExtendedValue() {
    $filter = new Web();
    $this->assertEquals('http://localhost', $filter->filter('localhost'));
  }

  /************************
   * Data Provider
   ************************/

  public static function provideValidUrls() {
    return array(
      array('localhost'),
      array('example.tld'),
      array('www.example.tld'),
      array('http://localhost'),
      array('https://example.tld')
    );
  }

  public static function provideInvalidValues() {
    return array(
      array('foo.'),
      array(':8080'),
      array(''),
      array(' ')
    );
  }
}
