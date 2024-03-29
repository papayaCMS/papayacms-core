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

namespace Papaya\HTTP;
require_once __DIR__.'/../../../bootstrap.php';

class HeadersTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\HTTP\Headers::__construct
   * @covers \Papaya\HTTP\Headers::toArray
   */
  public function testConstructor() {
    $headers = new Headers(
      array('X-Hello' => 'World')
    );
    $this->assertEquals(
      array('X-Hello' => 'World'), $headers->toArray()
    );
  }

  /**
   * @covers \Papaya\HTTP\Headers::getIterator
   */
  public function testGetIterator() {
    $headers = new Headers(
      array('X-Hello' => 'World')
    );
    $iterator = $headers->getIterator();
    $this->assertInstanceOf('ArrayIterator', $iterator);
    $this->assertEquals(
      array('X-Hello' => 'World'), $iterator->getArrayCopy()
    );
  }

  /**
   * @covers \Papaya\HTTP\Headers::count
   */
  public function testCount() {
    $headers = new Headers(
      array('X-Hello' => 'World', 'X-World' => 'Hello')
    );
    $this->assertCount(2, $headers);
  }

  /**
   * @covers \Papaya\HTTP\Headers::get
   * @covers \Papaya\HTTP\Headers::normalizeName
   * @dataProvider provideValidHeaderVariants
   * @param string $name
   */
  public function testGet($name) {
    $headers = new Headers(
      array('X-Hello' => 'World')
    );
    $this->assertEquals(
      'World', $headers->get($name)
    );
  }

  /**
   * @covers \Papaya\HTTP\Headers::get
   */
  public function testGetWithInvalidNameExpectingNull() {
    $headers = new Headers(
      array('X-Hello' => 'World')
    );
    $this->assertNull(
      $headers->get('Invalid')
    );
  }

  /**
   * @covers \Papaya\HTTP\Headers::set
   */
  public function testSet() {
    $headers = new Headers();
    $headers->set('X-Hello', 'World');
    $this->assertEquals(
      array('X-Hello' => 'World'), iterator_to_array($headers)
    );
  }

  /**
   * @covers \Papaya\HTTP\Headers::set
   */
  public function testSetReplacesFirst() {
    $headers = new Headers();
    $headers->set('X-Hello', 'World');
    $headers->set('X-Hello', 'Moon');
    $this->assertEquals(
      array('X-Hello' => 'Moon'), iterator_to_array($headers)
    );
  }

  /**
   * @covers \Papaya\HTTP\Headers::set
   */
  public function testSetAllowsDuplicates() {
    $headers = new Headers();
    $headers->set('X-Hello', 'World');
    $headers->set('X-Hello', 'Moon', TRUE);
    $this->assertEquals(
      array('X-Hello' => array('World', 'Moon')), iterator_to_array($headers)
    );
  }

  /**
   * @covers \Papaya\HTTP\Headers::set
   */
  public function testSetEmptyValueRemovesHeader() {
    $headers = new Headers();
    $headers->set('X-Hello', 'World');
    $headers->set('X-Hello', '');
    $this->assertEquals(
      array(), iterator_to_array($headers)
    );
  }

  /**
   * @covers \Papaya\HTTP\Headers::set
   */
  public function testSetEmptyValueOnNoneExistingHeader() {
    $headers = new Headers();
    $headers->set('X-Hello', '');
    $this->assertEquals(
      array(), iterator_to_array($headers)
    );
  }

  /**
   * @covers \Papaya\HTTP\Headers::set
   */
  public function testSetEmptyNameReturnsFalse() {
    $headers = new Headers();
    $this->assertFalse(
      $headers->set('', '')
    );
  }

  /**
   * @covers \Papaya\HTTP\Headers::offsetExists
   */
  public function testOffsetExistsExpectingTrue() {
    $headers = new Headers();
    $headers->set('X-Hello', 'World');
    $this->assertTrue(isset($headers['X-Hello']));
  }

  /**
   * @covers \Papaya\HTTP\Headers::offsetExists
   */
  public function testOffsetExistsExpectingFalse() {
    $headers = new Headers();
    $this->assertFalse(isset($headers['X-Hello']));
  }

  /**
   * @covers \Papaya\HTTP\Headers::offsetGet
   */
  public function testOffsetGet() {
    $headers = new Headers();
    $headers->set('X-Hello', 'World');
    $this->assertEquals('World', $headers['X-Hello']);
  }

  /**
   * @covers \Papaya\HTTP\Headers::offsetSet
   */
  public function testOffsetSet() {
    $headers = new Headers();
    $headers['X-Hello'] = 'World';
    $this->assertEquals(
      array('X-Hello' => 'World'), iterator_to_array($headers)
    );
  }

  /**
   * @covers \Papaya\HTTP\Headers::offsetUnset
   */
  public function testOffsetUnset() {
    $headers = new Headers();
    $headers->set('X-Hello', 'World');
    unset($headers['X-Hello']);
    $this->assertEquals(
      array(), iterator_to_array($headers)
    );
  }

  /**
   * @covers \Papaya\HTTP\Headers::offsetUnset
   */
  public function testOffetUnsetOnNoneExistingHeader() {
    $headers = new Headers();
    unset($headers['X-Hello']);
    $this->assertEquals(
      array(), iterator_to_array($headers)
    );
  }

  /**
   * @covers \Papaya\HTTP\Headers::__toString
   */
  public function testMagicMethodToString() {
    $headers = new Headers();
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
