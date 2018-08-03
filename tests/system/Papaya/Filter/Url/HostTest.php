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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaFilterUrlHostTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Filter\URL\Host
   * @dataProvider provideHostNameValues
   * @param mixed $value
   * @throws \Papaya\Filter\Exception
   */
  public function testValidate($value) {
    $filter = new \Papaya\Filter\URL\Host();
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @covers \Papaya\Filter\URL\Host
   * @dataProvider provideInvalidValues
   * @param mixed $value
   * @throws \Papaya\Filter\Exception
   */
  public function testValidateExpectingException($value) {
    $filter = new \Papaya\Filter\URL\Host();
    $this->expectException(\Papaya\Filter\Exception::class);
    $filter->validate($value);
  }

  /**
  * @covers \Papaya\Filter\URL\Host
  */
  public function testFilterExpectingNull() {
    $filter = new \Papaya\Filter\URL\Host();
    $this->assertNull($filter->filter(''));
  }

  /**
  * @covers \Papaya\Filter\URL\Host
  */
  public function testFilterExpectingValue() {
    $filter = new \Papaya\Filter\URL\Host();
    $this->assertEquals('localhost', $filter->filter('localhost'));
  }

  /************************
  * Data Provider
  ************************/

  public static function provideHostNameValues() {
    return array(
      array('localhost'),
      array('example.tld'),
      array('www.example.tld'),
      array('kölsch.köln.de'),
      array('kölsch.köln.de:8080')
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
