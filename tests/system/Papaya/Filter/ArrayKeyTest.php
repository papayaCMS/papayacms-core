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

namespace Papaya\Filter {

  require_once __DIR__.'/../../../bootstrap.php';

  /**
   * @covers \Papaya\Filter\ArrayKey
   */
  class ArrayKeyTest extends \Papaya\TestFramework\TestCase {

    /**
     * @dataProvider provideValidValidateData
     * @param mixed $value
     * @param array|\Traversable $validValues
     * @throws \Papaya\Filter\Exception
     */
    public function testValidateExpectingTrue($value, $validValues) {
      $filter = new ArrayKey($validValues);
      $this->assertTrue($filter->validate($value));
    }

    /**
     * @dataProvider provideInvalidValidateData
     * @param mixed $value
     * @param array|\Traversable $validValues
     * @throws \Papaya\Filter\Exception
     */
    public function testValidateExpectingException($value, $validValues) {
      $filter = new ArrayKey($validValues);
      $this->expectException(\Papaya\Filter\Exception::class);
      $filter->validate($value);
    }

    /**
     * @dataProvider provideValidFilterData
     * @param mixed $expected
     * @param mixed $value
     * @param array|\Traversable $validValues
     */
    public function testFilter($expected, $value, $validValues) {
      $filter = new ArrayKey($validValues);
      $this->assertEquals($expected, $filter->filter($value));
    }

    /**
     * @dataProvider provideInvalidValidateData
     * @param mixed $value
     * @param array|\Traversable $validValues
     */
    public function testFilterExpectingNull($value, $validValues) {
      $filter = new ArrayKey($validValues);
      $this->assertNull($filter->filter($value));
    }

    /**************************
     * Data Provider
     **************************/

    public static function provideValidValidateData(): array {
      return array(
        array('21', array(21 => 'half', 42 => 'truth')),
        array('21', array('21' => 'half', '42' => 'truth')),
        array('21', new \ArrayIterator(array('21' => 'half', '42' => 'truth'))),
        array('21', new Iterator_TestStubForFilterListKeys(array('21' => 'half', '42' => 'truth'))),
      );
    }

    public static function provideInvalidValidateData(): array {
      return array(
        array('', array(21 => 'half', 42 => 'truth')),
        array(array(), array(21 => 'half', 42 => 'truth')),
        array('23', array(21 => 'half', 42 => 'truth')),
        array('23', new \ArrayIterator(array('21' => 'half', '42' => 'truth'))),
        array('23', new Iterator_TestStubForFilterListKeys(array('21' => 'half', '42' => 'truth'))),
      );
    }

    public static function provideValidFilterData(): array {
      return array(
        array(21, '21', array(21 => 'half', 42 => 'truth')),
        array('#21', '#21', array('#21' => 'half', '#42' => 'truth')),
        array(21, '21', new \ArrayIterator(array(21 => 'half', 42 => 'truth'))),
        array(21, '21', new Iterator_TestStubForFilterListKeys(array(21 => 'half', 42 => 'truth'))),
      );
    }
  }

  class Iterator_TestStubForFilterListKeys implements \Iterator {

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
}
