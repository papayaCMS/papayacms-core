<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaFilterListKeysTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterListKeys::__construct
   */
  public function testConstructor() {
    $filter = new PapayaFilterListKeys(array(21 => 'half', 42 => 'truth'));
    $this->assertAttributeEquals(
      array(21 => 'half', 42 => 'truth'), '_list', $filter
    );
  }

  /**
   * @covers PapayaFilterListKeys::__construct
   */
  public function testConstructorWithTraversable() {
    $filter = new PapayaFilterListKeys($iterator = new ArrayIterator(array()));
    $this->assertAttributeSame(
      $iterator, '_list', $filter
    );
  }

  /**
   * @covers PapayaFilterListKeys::validate
   * @dataProvider provideValidValidateData
   */
  public function testValidateExpectingTrue($value, $validValues) {
    $filter = new PapayaFilterListKeys($validValues);
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @covers PapayaFilterListKeys::validate
   * @dataProvider provideInvalidValidateData
   */
  public function testValidateExpectingException($value, $validValues) {
    $filter = new PapayaFilterListKeys($validValues);
    $this->setExpectedException('PapayaFilterException');
    $filter->validate($value);
  }

  /**
   * @covers PapayaFilterListKeys::filter
   * @dataProvider provideValidFilterData
   */
  public function testFilter($expected, $value, $validValues) {
    $filter = new PapayaFilterListKeys($validValues);
    $this->assertEquals($expected, $filter->filter($value));
  }

  /**
   * @covers PapayaFilterListKeys::filter
   * @dataProvider provideInvalidValidateData
   */
  public function testFilterExpectingNull($value, $validValues) {
    $filter = new PapayaFilterListKeys($validValues);
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

  private $_array = array();

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
    return ($key !== NULL && $key !== FALSE);
  }
}
