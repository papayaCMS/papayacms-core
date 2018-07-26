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

class PapayaFilterEmptyTest extends \PapayaTestCase {

  /**
  * @covers \PapayaFilterEmpty::__construct
  */
  public function testConstructor() {
    $filter = new \PapayaFilterEmpty();
    $this->assertAttributeEquals(
      TRUE, '_ignoreZero', $filter
    );
    $this->assertAttributeEquals(
      TRUE, '_ignoreSpaces', $filter
    );
  }

  /**
  * @covers \PapayaFilterEmpty::__construct
  */
  public function testConstructorWithArguments() {
    $filter = new \PapayaFilterEmpty(FALSE, FALSE);
    $this->assertAttributeEquals(
      FALSE, '_ignoreZero', $filter
    );
    $this->assertAttributeEquals(
      FALSE, '_ignoreSpaces', $filter
    );
  }

  /**
   * @covers \PapayaFilterEmpty::validate
   * @dataProvider provideEmptyValues
   * @param mixed $value
   * @param bool $ignoreZero
   * @param bool $ignoreSpaces
   * @throws PapayaFilterExceptionNotEmpty
   */
  public function testCheck($value, $ignoreZero, $ignoreSpaces) {
    $filter = new \PapayaFilterEmpty($ignoreZero, $ignoreSpaces);
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @covers \PapayaFilterEmpty::validate
   * @dataProvider provideNonEmptyValues
   * @param mixed $value
   * @param bool $ignoreZero
   * @param bool $ignoreSpaces
   * @throws PapayaFilterExceptionNotEmpty
   */
  public function testCheckExpectingException($value, $ignoreZero, $ignoreSpaces) {
    $filter = new \PapayaFilterEmpty($ignoreZero, $ignoreSpaces);
    $this->expectException(\PapayaFilterExceptionNotEmpty::class);
    $filter->validate($value);
  }

  /**
  * @covers \PapayaFilterEmpty::filter
  */
  public function testFilter() {
    $filter = new \PapayaFilterEmpty();
    $this->assertNull($filter->filter(''));
  }

  /************************
  * Data Provider
  ************************/

  public static function provideEmptyValues() {
    return array(
      array('', FALSE, FALSE),
      array(' ', FALSE, TRUE),
      array('0', TRUE, FALSE),
      array(array(), TRUE, FALSE)
    );
  }

  public static function provideNonEmptyValues() {
    return array(
      array('some', FALSE, FALSE),
      array(' ', FALSE, FALSE),
      array('0', FALSE, FALSE),
      array(array('0'), FALSE, FALSE)
    );
  }
}
