<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaObjectStringValuesTest extends PapayaTestCase {

  /**
   * @covers PapayaObjectStringValues
   */
  public function testConstructor() {
    $values = new PapayaObjectStringValues();
    $this->assertCount(0, $values);
  }

  public function testConstructorWithScalar() {
    $values = new PapayaObjectStringValues(1);
    $this->assertCount(1, $values);
  }

  /**
   * @covers PapayaObjectStringValues
   */
  public function testConstructorWithArray() {
    $values = new PapayaObjectStringValues(array(1, 2));
    $this->assertCount(2, $values);
  }

  /**
   * @covers PapayaObjectStringValues
   */
  public function testConstructorWithScalarAndOffset() {
    $values = new PapayaObjectStringValues(42, 'foo');
    $this->assertEquals(42, $values['foo']);
  }

  /**
   * @covers PapayaObjectStringValues
   */
  public function testToStringWithDefaultOffsetZero() {
    $values = new PapayaObjectStringValues(array('foo', 'bar'));
    $this->assertEquals('foo', (string)$values);
  }

  /**
   * @covers PapayaObjectStringValues
   */
  public function testToStringWithDefaultOffsetOne() {
    $values = new PapayaObjectStringValues(array('foo', 'bar'), 1);
    $this->assertEquals('bar', (string)$values);
  }

  /**
   * @covers PapayaObjectStringValues
   */
  public function testToStringWithInvalidDefaultOffset() {
    $values = new PapayaObjectStringValues(array('foo', 'bar'), 23);
    $this->assertEquals('', (string)$values);
  }

  /**
   * @covers PapayaObjectStringValues
   */
  public function testOffsetExistsExpectingTrue() {
    $values = new PapayaObjectStringValues(array('foo' => 21, 'bar' => 42));
    $this->assertTrue(isset($values['foo']));
  }

  /**
   * @covers PapayaObjectStringValues
   */
  public function testOffsetExistsExpectingFalse() {
    $values = new PapayaObjectStringValues(array('foo' => 21, 'bar' => 42));
    $this->assertFalse(isset($values['non-existing']));
  }

  /**
   * @covers PapayaObjectStringValues
   */
  public function testOffsetGetAfterSet() {
    $values = new PapayaObjectStringValues();
    $values[42] = 'success';
    $this->assertEquals('success', $values[42]);
  }

  /**
   * @covers PapayaObjectStringValues
   */
  public function testOffsetGetAfterAppend() {
    $values = new PapayaObjectStringValues(array());
    $values[] = 'success';
    $this->assertEquals('success', $values[0]);
  }

  /**
   * @covers PapayaObjectStringValues
   */
  public function testOffsetUnset() {
    $values = new PapayaObjectStringValues(array('foo' => 21, 'bar' => 42));
    unset($values['foo']);
    $this->assertFalse(isset($values['foo']));
  }

  /**
   * @covers PapayaObjectStringValues
   */
  public function testGetIterator() {
    $values = new PapayaObjectStringValues(array('foo', 'bar'));
    $this->assertEquals(
      array('foo', 'bar'),
      iterator_to_array($values, TRUE)
    );
  }

  /**
   * @covers PapayaObjectStringValues
   */
  public function testGetIteratorWithAssociativeArray() {
    $values = new PapayaObjectStringValues(array('foo' => 21, 'bar' => 42));
    $this->assertEquals(
      array('foo' => 21, 'bar' => 42),
      iterator_to_array($values, TRUE)
    );
  }
}
