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
  * @covers \Papaya\Filter\NotEmpty::__construct
  */
  public function testConstructor() {
    $filter = new \Papaya\Filter\NotEmpty();
    $this->assertAttributeEquals(
      TRUE, '_ignoreSpaces', $filter
    );
  }

  /**
  * @covers \Papaya\Filter\NotEmpty::__construct
  */
  public function testConstructorWithArguments() {
    $filter = new \Papaya\Filter\NotEmpty(FALSE);
    $this->assertAttributeEquals(
      FALSE, '_ignoreSpaces', $filter
    );
  }

  /**
   * @covers \Papaya\Filter\NotEmpty::validate
   * @dataProvider provideNonEmptyValues
   * @param mixed $value
   * @param bool $ignoreSpaces
   * @throws \PapayaFilterException
   */
  public function testValidate($value, $ignoreSpaces) {
    $filter = new \Papaya\Filter\NotEmpty($ignoreSpaces);
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @covers \Papaya\Filter\NotEmpty::validate
   * @dataProvider provideEmptyValues
   * @param mixed $value
   * @param bool $ignoreSpaces
   * @throws \PapayaFilterException
   */
  public function testValidateExpectingException($value, $ignoreSpaces) {
    $filter = new \Papaya\Filter\NotEmpty($ignoreSpaces);
    $this->expectException(\Papaya\Filter\Exception\IsEmpty::class);
    $filter->validate($value);
  }

  /**
  * @covers \Papaya\Filter\NotEmpty::filter
  */
  public function testFilterExpectingNull() {
    $filter = new \Papaya\Filter\NotEmpty();
    $this->assertNull($filter->filter(''));
  }

  /**
  * @covers \Papaya\Filter\NotEmpty::filter
  */
  public function testFilterWithEmptyArrayExpectingNull() {
    $filter = new \Papaya\Filter\NotEmpty();
    $this->assertNull($filter->filter(array()));
  }

  /**
  * @covers \Papaya\Filter\NotEmpty::filter
  */
  public function testFilterExpectingValue() {
    $filter = new \Papaya\Filter\NotEmpty();
    $this->assertEquals('some', $filter->filter('some'));
  }

  /**
  * @covers \Papaya\Filter\NotEmpty::filter
  */
  public function testFilterWithArrayExpectingValue() {
    $filter = new \Papaya\Filter\NotEmpty();
    $this->assertEquals(array('some'), $filter->filter(array('some')));
  }

  /**
  * @covers \Papaya\Filter\NotEmpty::filter
  */
  public function testFilterExpectingTrimmedValue() {
    $filter = new \Papaya\Filter\NotEmpty();
    $this->assertEquals('some', $filter->filter(' some '));
  }

  /**
  * @covers \Papaya\Filter\NotEmpty::filter
  */
  public function testFilterExpectingWhitespaceValue() {
    $filter = new \Papaya\Filter\NotEmpty(FALSE);
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
