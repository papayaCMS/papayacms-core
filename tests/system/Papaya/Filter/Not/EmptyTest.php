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

class PapayaFilterNotEmptyTest extends \PapayaTestCase {

  /**
  * @covers \PapayaFilterNotEmpty::__construct
  */
  public function testConstructor() {
    $filter = new \PapayaFilterNotEmpty();
    $this->assertAttributeEquals(
      TRUE, '_ignoreSpaces', $filter
    );
  }

  /**
  * @covers \PapayaFilterNotEmpty::__construct
  */
  public function testConstructorWithArguments() {
    $filter = new \PapayaFilterNotEmpty(FALSE);
    $this->assertAttributeEquals(
      FALSE, '_ignoreSpaces', $filter
    );
  }

  /**
   * @covers \PapayaFilterNotEmpty::validate
   * @dataProvider provideNonEmptyValues
   * @param mixed $value
   * @param bool $ignoreSpaces
   * @throws PapayaFilterException
   */
  public function testValidate($value, $ignoreSpaces) {
    $filter = new \PapayaFilterNotEmpty($ignoreSpaces);
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @covers \PapayaFilterNotEmpty::validate
   * @dataProvider provideEmptyValues
   * @param mixed $value
   * @param bool $ignoreSpaces
   * @throws PapayaFilterException
   */
  public function testValidateExpectingException($value, $ignoreSpaces) {
    $filter = new \PapayaFilterNotEmpty($ignoreSpaces);
    $this->expectException(\PapayaFilterExceptionEmpty::class);
    $filter->validate($value);
  }

  /**
  * @covers \PapayaFilterNotEmpty::filter
  */
  public function testFilterExpectingNull() {
    $filter = new \PapayaFilterNotEmpty();
    $this->assertNull($filter->filter(''));
  }

  /**
  * @covers \PapayaFilterNotEmpty::filter
  */
  public function testFilterWithEmptyArrayExpectingNull() {
    $filter = new \PapayaFilterNotEmpty();
    $this->assertNull($filter->filter(array()));
  }

  /**
  * @covers \PapayaFilterNotEmpty::filter
  */
  public function testFilterExpectingValue() {
    $filter = new \PapayaFilterNotEmpty();
    $this->assertEquals('some', $filter->filter('some'));
  }

  /**
  * @covers \PapayaFilterNotEmpty::filter
  */
  public function testFilterWithArrayExpectingValue() {
    $filter = new \PapayaFilterNotEmpty();
    $this->assertEquals(array('some'), $filter->filter(array('some')));
  }

  /**
  * @covers \PapayaFilterNotEmpty::filter
  */
  public function testFilterExpectingTrimmedValue() {
    $filter = new \PapayaFilterNotEmpty();
    $this->assertEquals('some', $filter->filter(' some '));
  }

  /**
  * @covers \PapayaFilterNotEmpty::filter
  */
  public function testFilterExpectingWhitespaceValue() {
    $filter = new \PapayaFilterNotEmpty(FALSE);
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
