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
 * @covers \Papaya\Filter\Number
 */
class NumberTest extends \Papaya\TestFramework\TestCase {

  /**
   * @dataProvider constructFailureProvider
   * @param int $minimumLength
   * @param int $maximumLength
   */
  public function testConstructFailure($minimumLength, $maximumLength) {
    $this->expectException(\UnexpectedValueException::class);
    new Number($minimumLength, $maximumLength);
  }

  /**
   * @dataProvider validateSuccessProvider
   * @param mixed $value
   * @throws Exception\OutOfRange\ToLarge
   * @throws Exception\OutOfRange\ToSmall
   * @throws Exception\UnexpectedType
   */
  public function testValidateSuccess($value) {
    $filter = new Number(3, 4);
    /** @noinspection PhpUnhandledExceptionInspection */
    $this->assertTrue($filter->validate($value));
  }

  public function testValidateFailureFormat() {
    $filter = new Number();
    $this->expectException(Exception\UnexpectedType::class);
    /** @noinspection PhpUnhandledExceptionInspection */
    $filter->validate('I am not a number');
  }

  public function testValidateFailureTooShort() {
    $filter = new Number(3);
    $this->expectException(Exception\OutOfRange\ToSmall::class);
    /** @noinspection PhpUnhandledExceptionInspection */
    $filter->validate('22');
  }

  public function testValidateFailureTooLong() {
    $filter = new Number(NULL, 3);
    $this->expectException(Exception\OutOfRange\ToLarge::class);
    /** @noinspection PhpUnhandledExceptionInspection */
    $filter->validate('2222');
  }

  /**
   * @dataProvider filterSuccessProvider
   * @param mixed $value
   * @param mixed $filtered
   */
  public function testFilterSuccess($value, $filtered) {
    $filter = new Number(3, 4);
    $this->assertEquals($filtered, $filter->filter($value));
  }

  public function testFilterFailure() {
    $filter = new Number();
    $this->assertNull($filter->filter('I am not a number'));
  }

  public static function constructFailureProvider() {
    return array(
      array(-1, NULL),
      array('String', NULL),
      array(NULL, -1),
      array(NULL, 'String'),
      array(5, 4)
    );
  }

  public static function validateSuccessProvider() {
    return array(
      array('100'),
      array('003'),
      array('0001')
    );
  }

  public static function filterSuccessProvider() {
    return array(
      array('0234', '0234'),
      array('    0234   ', '0234'),
      array('123', '123')
    );
  }
}
