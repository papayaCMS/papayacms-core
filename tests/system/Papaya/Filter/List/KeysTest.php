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

class PapayaFilterListKeysTest extends PapayaTestCase {

  /**
   * @covers \PapayaFilterListKeys::__construct
   */
  public function testConstructor() {
    $filter = new \PapayaFilterListKeys(array(21 => 'half', 42 => 'truth'));
    $this->assertAttributeEquals(
      array(21 => 'half', 42 => 'truth'), '_list', $filter
    );
  }

  /**
   * @covers \PapayaFilterListKeys::__construct
   */
  public function testConstructorWithTraversable() {
    $filter = new \PapayaFilterListKeys($iterator = new ArrayIterator(array()));
    $this->assertAttributeSame(
      $iterator, '_list', $filter
    );
  }

  /**
   * @covers \PapayaFilterListKeys::validate
   * @dataProvider provideValidValidateData
   * @param mixed $value
   * @param array|Traversable $validValues
   * @throws PapayaFilterException
   */
  public function testValidateExpectingTrue($value, $validValues) {
    $filter = new \PapayaFilterListKeys($validValues);
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @covers \PapayaFilterListKeys::validate
   * @dataProvider provideInvalidValidateData
   * @param mixed $value
   * @param array|Traversable $validValues
   * @throws PapayaFilterException
   */
  public function testValidateExpectingException($value, $validValues) {
    $filter = new \PapayaFilterListKeys($validValues);
    $this->expectException(PapayaFilterException::class);
    $filter->validate($value);
  }

  /**
   * @covers \PapayaFilterListKeys::filter
   * @dataProvider provideValidFilterData
   * @param mixed $expected
   * @param mixed $value
   * @param array|Traversable $validValues
   */
  public function testFilter($expected, $value, $validValues) {
    $filter = new \PapayaFilterListKeys($validValues);
    $this->assertEquals($expected, $filter->filter($value));
  }

  /**
   * @covers \PapayaFilterListKeys::filter
   * @dataProvider provideInvalidValidateData
   * @param mixed $value
   * @param array|Traversable $validValues
   */
  public function testFilterExpectingNull($value, $validValues) {
    $filter = new \PapayaFilterListKeys($validValues);
    $this->assertNull($filter->filter($value));
  }

  /**************************
   * Data Provider
   **************************/

  public static function provideValidValidateData() {
    return array(
      array('21', array(21 => 'half', 42 => 'truth')),
      array('21', array('21' => 'half', '42' => 'truth')),
      array('21', new ArrayIterator(array('21' => 'half', '42' => 'truth'))),
      array('21', new Iterator_TestStubForFilterListKeys(array('21' => 'half', '42' => 'truth'))),
    );
  }

  public static function provideInvalidValidateData() {
    return array(
      array('', array(21 => 'half', 42 => 'truth')),
      array(array(), array(21 => 'half', 42 => 'truth')),
      array('23', array(21 => 'half', 42 => 'truth')),
      array('23', new ArrayIterator(array('21' => 'half', '42' => 'truth'))),
      array('23', new Iterator_TestStubForFilterListKeys(array('21' => 'half', '42' => 'truth'))),
    );
  }

  public static function provideValidFilterData() {
    return array(
      array(21, '21', array(21 => 'half', 42 => 'truth')),
      array('#21', '#21', array('#21' => 'half', '#42' => 'truth')),
      array(21, '21', new ArrayIterator(array(21 => 'half', 42 => 'truth'))),
      array(21, '21', new Iterator_TestStubForFilterListKeys(array(21 => 'half', 42 => 'truth'))),
    );
  }
}

class Iterator_TestStubForFilterListKeys implements Iterator {

  private $_array;

  public function __construct(array $array) {
    $this->_array = $array;
  }

  public function rewind() {
    reset($this->_array);
  }

  public function current() {
    return current($this->_array);
  }

  public function key() {
    return key($this->_array);
  }

  public function next() {
    return next($this->_array);
  }

  public function valid() {
    $key = key($this->_array);
    return (NULL !== $key && FALSE !== $key);
  }
}
