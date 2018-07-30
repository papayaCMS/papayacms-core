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

class PapayaHttpHeadersTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Http\Headers::__construct
  */
  public function testConstructor() {
    $headers = new \Papaya\Http\Headers(
      array('X-Hello' => 'World')
    );
    $this->assertAttributeEquals(
      array('X-Hello' => 'World'), '_headers', $headers
    );
  }

  /**
  * @covers \Papaya\Http\Headers::toArray
  */
  public function testToArray() {
    $headers = new \Papaya\Http\Headers(
      array('X-Hello' => 'World')
    );
    $this->assertEquals(
      array('X-Hello' => 'World'), $headers->toArray()
    );
  }

  /**
  * @covers \Papaya\Http\Headers::getIterator
  */
  public function testGetIterator() {
    $headers = new \Papaya\Http\Headers(
      array('X-Hello' => 'World')
    );
    $iterator = $headers->getIterator();
    $this->assertInstanceOf('ArrayIterator', $iterator);
    $this->assertEquals(
      array('X-Hello' => 'World'), $iterator->getArrayCopy()
    );
  }

  /**
  * @covers \Papaya\Http\Headers::count
  */
  public function testCount() {
    $headers = new \Papaya\Http\Headers(
      array('X-Hello' => 'World', 'X-World' => 'Hello')
    );
    $this->assertCount(2, $headers);
  }

  /**
   * @covers \Papaya\Http\Headers::get
   * @covers \Papaya\Http\Headers::normalizeName
   * @dataProvider provideValidHeaderVariants
   * @param string $name
   */
  public function testGet($name) {
    $headers = new \Papaya\Http\Headers(
      array('X-Hello' => 'World')
    );
    $this->assertEquals(
      'World', $headers->get($name)
    );
  }

  /**
  * @covers \Papaya\Http\Headers::get
  */
  public function testGetWithInvalidNameExpectingNull() {
    $headers = new \Papaya\Http\Headers(
      array('X-Hello' => 'World')
    );
    $this->assertNull(
      $headers->get('Invalid')
    );
  }

  /**
  * @covers \Papaya\Http\Headers::set
  */
  public function testSet() {
    $headers = new \Papaya\Http\Headers();
    $headers->set('X-Hello', 'World');
    $this->assertAttributeEquals(
      array('X-Hello' => 'World'), '_headers', $headers
    );
  }

  /**
  * @covers \Papaya\Http\Headers::set
  */
  public function testSetReplacesFirst() {
    $headers = new \Papaya\Http\Headers();
    $headers->set('X-Hello', 'World');
    $headers->set('X-Hello', 'Moon');
    $this->assertAttributeEquals(
      array('X-Hello' => 'Moon'), '_headers', $headers
    );
  }

  /**
  * @covers \Papaya\Http\Headers::set
  */
  public function testSetAllowsDuplicates() {
    $headers = new \Papaya\Http\Headers();
    $headers->set('X-Hello', 'World');
    $headers->set('X-Hello', 'Moon', TRUE);
    $this->assertAttributeEquals(
      array('X-Hello' => array('World', 'Moon')), '_headers', $headers
    );
  }

  /**
  * @covers \Papaya\Http\Headers::set
  */
  public function testSetEmptyValueRemovesHeader() {
    $headers = new \Papaya\Http\Headers();
    $headers->set('X-Hello', 'World');
    $headers->set('X-Hello', '');
    $this->assertAttributeEquals(
      array(), '_headers', $headers
    );
  }

  /**
  * @covers \Papaya\Http\Headers::set
  */
  public function testSetEmptyValueOnNoneExistingHeader() {
    $headers = new \Papaya\Http\Headers();
    $headers->set('X-Hello', '');
    $this->assertAttributeEquals(
      array(), '_headers', $headers
    );
  }

  /**
  * @covers \Papaya\Http\Headers::set
  */
  public function testSetEmptyNameReturnsFalse() {
    $headers = new \Papaya\Http\Headers();
    $this->assertFalse(
      $headers->set('', '')
    );
  }

  /**
  * @covers \Papaya\Http\Headers::offsetExists
  */
  public function testOffsetExistsExpectingTrue() {
    $headers = new \Papaya\Http\Headers();
    $headers->set('X-Hello', 'World');
    $this->assertTrue(isset($headers['X-Hello']));
  }

  /**
  * @covers \Papaya\Http\Headers::offsetExists
  */
  public function testOffsetExistsExpectingFalse() {
    $headers = new \Papaya\Http\Headers();
    $this->assertFalse(isset($headers['X-Hello']));
  }

  /**
  * @covers \Papaya\Http\Headers::offsetGet
  */
  public function testOffsetGet() {
    $headers = new \Papaya\Http\Headers();
    $headers->set('X-Hello', 'World');
    $this->assertEquals('World', $headers['X-Hello']);
  }

  /**
  * @covers \Papaya\Http\Headers::offsetSet
  */
  public function testOffsetSet() {
    $headers = new \Papaya\Http\Headers();
    $headers['X-Hello'] = 'World';
    $this->assertAttributeEquals(
      array('X-Hello' => 'World'), '_headers', $headers
    );
  }

  /**
  * @covers \Papaya\Http\Headers::offsetUnset
  */
  public function testOffsetUnset() {
    $headers = new \Papaya\Http\Headers();
    $headers->set('X-Hello', 'World');
    unset($headers['X-Hello']);
    $this->assertAttributeEquals(
      array(), '_headers', $headers
    );
  }

  /**
  * @covers \Papaya\Http\Headers::offsetUnset
  */
  public function testOffetUnsetOnNoneExistingHeader() {
    $headers = new \Papaya\Http\Headers();
    unset($headers['X-Hello']);
    $this->assertAttributeEquals(
      array(), '_headers', $headers
    );
  }

  /**
  * @covers \Papaya\Http\Headers::__toString
  */
  public function testMagicMethodToString() {
    $headers = new \Papaya\Http\Headers();
    $headers->set('X-Simple', 1);
    $headers->set('X-List', 2);
    $headers->set('X-List', 3, TRUE);
    $this->assertEquals(
      "X-Simple: 1\r\nX-List: 2\r\nX-List: 3\r\n",
      (string)$headers
    );
  }

  /*********************************
  * Data Provider
  *********************************/

  public static function provideValidHeaderVariants() {
    return array(
      'normalized' => array('X-Hello'),
      'lowercase' => array('x-hello'),
      'uppercase' => array('X-HELLO'),
      'mixed case' => array('X-helLO'),
    );
  }
}
