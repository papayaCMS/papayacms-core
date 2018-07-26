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

class PapayaObjectStringValuesTest extends PapayaTestCase {

  /**
   * @covers \PapayaObjectStringValues
   */
  public function testConstructor() {
    $values = new \PapayaObjectStringValues();
    $this->assertCount(0, $values);
  }

  public function testConstructorWithScalar() {
    $values = new \PapayaObjectStringValues(1);
    $this->assertCount(1, $values);
  }

  /**
   * @covers \PapayaObjectStringValues
   */
  public function testConstructorWithArray() {
    $values = new \PapayaObjectStringValues(array(1, 2));
    $this->assertCount(2, $values);
  }

  /**
   * @covers \PapayaObjectStringValues
   */
  public function testConstructorWithScalarAndOffset() {
    $values = new \PapayaObjectStringValues(42, 'foo');
    $this->assertEquals(42, $values['foo']);
  }

  /**
   * @covers \PapayaObjectStringValues
   */
  public function testToStringWithDefaultOffsetZero() {
    $values = new \PapayaObjectStringValues(array('foo', 'bar'));
    $this->assertEquals('foo', (string)$values);
  }

  /**
   * @covers \PapayaObjectStringValues
   */
  public function testToStringWithDefaultOffsetOne() {
    $values = new \PapayaObjectStringValues(array('foo', 'bar'), 1);
    $this->assertEquals('bar', (string)$values);
  }

  /**
   * @covers \PapayaObjectStringValues
   */
  public function testToStringWithInvalidDefaultOffset() {
    $values = new \PapayaObjectStringValues(array('foo', 'bar'), 23);
    $this->assertEquals('', (string)$values);
  }

  /**
   * @covers \PapayaObjectStringValues
   */
  public function testOffsetExistsExpectingTrue() {
    $values = new \PapayaObjectStringValues(array('foo' => 21, 'bar' => 42));
    $this->assertTrue(isset($values['foo']));
  }

  /**
   * @covers \PapayaObjectStringValues
   */
  public function testOffsetExistsExpectingFalse() {
    $values = new \PapayaObjectStringValues(array('foo' => 21, 'bar' => 42));
    $this->assertFalse(isset($values['non-existing']));
  }

  /**
   * @covers \PapayaObjectStringValues
   */
  public function testOffsetGetAfterSet() {
    $values = new \PapayaObjectStringValues();
    $values[42] = 'success';
    $this->assertEquals('success', $values[42]);
  }

  /**
   * @covers \PapayaObjectStringValues
   */
  public function testOffsetGetAfterAppend() {
    $values = new \PapayaObjectStringValues(array());
    $values[] = 'success';
    $this->assertEquals('success', $values[0]);
  }

  /**
   * @covers \PapayaObjectStringValues
   */
  public function testOffsetUnset() {
    $values = new \PapayaObjectStringValues(array('foo' => 21, 'bar' => 42));
    unset($values['foo']);
    $this->assertFalse(isset($values['foo']));
  }

  /**
   * @covers \PapayaObjectStringValues
   */
  public function testGetIterator() {
    $values = new \PapayaObjectStringValues(array('foo', 'bar'));
    $this->assertEquals(
      array('foo', 'bar'),
      iterator_to_array($values, TRUE)
    );
  }

  /**
   * @covers \PapayaObjectStringValues
   */
  public function testGetIteratorWithAssociativeArray() {
    $values = new \PapayaObjectStringValues(array('foo' => 21, 'bar' => 42));
    $this->assertEquals(
      array('foo' => 21, 'bar' => 42),
      iterator_to_array($values, TRUE)
    );
  }
}
