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

class PapayaObjectStringValuesTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\BaseObject\Text\Values
   */
  public function testConstructor() {
    $values = new \Papaya\BaseObject\Text\Values();
    $this->assertCount(0, $values);
  }

  public function testConstructorWithScalar() {
    $values = new \Papaya\BaseObject\Text\Values(1);
    $this->assertCount(1, $values);
  }

  /**
   * @covers \Papaya\BaseObject\Text\Values
   */
  public function testConstructorWithArray() {
    $values = new \Papaya\BaseObject\Text\Values(array(1, 2));
    $this->assertCount(2, $values);
  }

  /**
   * @covers \Papaya\BaseObject\Text\Values
   */
  public function testConstructorWithScalarAndOffset() {
    $values = new \Papaya\BaseObject\Text\Values(42, 'foo');
    $this->assertEquals(42, $values['foo']);
  }

  /**
   * @covers \Papaya\BaseObject\Text\Values
   */
  public function testToStringWithDefaultOffsetZero() {
    $values = new \Papaya\BaseObject\Text\Values(array('foo', 'bar'));
    $this->assertEquals('foo', (string)$values);
  }

  /**
   * @covers \Papaya\BaseObject\Text\Values
   */
  public function testToStringWithDefaultOffsetOne() {
    $values = new \Papaya\BaseObject\Text\Values(array('foo', 'bar'), 1);
    $this->assertEquals('bar', (string)$values);
  }

  /**
   * @covers \Papaya\BaseObject\Text\Values
   */
  public function testToStringWithInvalidDefaultOffset() {
    $values = new \Papaya\BaseObject\Text\Values(array('foo', 'bar'), 23);
    $this->assertEquals('', (string)$values);
  }

  /**
   * @covers \Papaya\BaseObject\Text\Values
   */
  public function testOffsetExistsExpectingTrue() {
    $values = new \Papaya\BaseObject\Text\Values(array('foo' => 21, 'bar' => 42));
    $this->assertTrue(isset($values['foo']));
  }

  /**
   * @covers \Papaya\BaseObject\Text\Values
   */
  public function testOffsetExistsExpectingFalse() {
    $values = new \Papaya\BaseObject\Text\Values(array('foo' => 21, 'bar' => 42));
    $this->assertFalse(isset($values['non-existing']));
  }

  /**
   * @covers \Papaya\BaseObject\Text\Values
   */
  public function testOffsetGetAfterSet() {
    $values = new \Papaya\BaseObject\Text\Values();
    $values[42] = 'success';
    $this->assertEquals('success', $values[42]);
  }

  /**
   * @covers \Papaya\BaseObject\Text\Values
   */
  public function testOffsetGetAfterAppend() {
    $values = new \Papaya\BaseObject\Text\Values(array());
    $values[] = 'success';
    $this->assertEquals('success', $values[0]);
  }

  /**
   * @covers \Papaya\BaseObject\Text\Values
   */
  public function testOffsetUnset() {
    $values = new \Papaya\BaseObject\Text\Values(array('foo' => 21, 'bar' => 42));
    unset($values['foo']);
    $this->assertFalse(isset($values['foo']));
  }

  /**
   * @covers \Papaya\BaseObject\Text\Values
   */
  public function testGetIterator() {
    $values = new \Papaya\BaseObject\Text\Values(array('foo', 'bar'));
    $this->assertEquals(
      array('foo', 'bar'),
      iterator_to_array($values, TRUE)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Text\Values
   */
  public function testGetIteratorWithAssociativeArray() {
    $values = new \Papaya\BaseObject\Text\Values(array('foo' => 21, 'bar' => 42));
    $this->assertEquals(
      array('foo' => 21, 'bar' => 42),
      iterator_to_array($values, TRUE)
    );
  }
}
