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

class PapayaResponseHeadersTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Response\Headers::getIterator
  */
  public function testGetIterator() {
    $headers = new \Papaya\Response\Headers();
    $iterator = $headers->getIterator();
    $this->assertInstanceOf('IteratorAggregate', $headers);
    $this->assertInstanceOf('ArrayIterator', $iterator);
  }

  /**
  * @covers \Papaya\Response\Headers::count
  */
  public function testCountExpectingZero() {
    $headers = new \Papaya\Response\Headers();
    $this->assertCount(0, $headers);
  }

  /**
  * @covers \Papaya\Response\Headers::count
  */
  public function testCountExpectingOne() {
    $headers = new \Papaya\Response\Headers();
    $headers['X-Sample'] = 'success';
    $this->assertCount(1, $headers);
  }

  /**
  * @covers \Papaya\Response\Headers::set
  * @covers \Papaya\Response\Headers::_normalize
  */
  public function testSet() {
    $headers = new \Papaya\Response\Headers();
    $headers->set('X-sample', 'success');
    $this->assertAttributeEquals(
      array('X-Sample' => 'success'), '_headers', $headers
    );
  }

  /**
  * @covers \Papaya\Response\Headers::set
  * @covers \Papaya\Response\Headers::_normalize
  */
  public function testSetTwoHeaders() {
    $headers = new \Papaya\Response\Headers();
    $headers->set('X-sample-1', 'success');
    $headers->set('x-Sample-2', 'success');
    $this->assertAttributeEquals(
      array('X-Sample-1' => 'success', 'X-Sample-2' => 'success'), '_headers', $headers
    );
  }

  /**
  * @covers \Papaya\Response\Headers::set
  * @covers \Papaya\Response\Headers::_normalize
  */
  public function testSetReplaceFirst() {
    $headers = new \Papaya\Response\Headers();
    $headers->set('X-Sample', 'failed');
    $headers->set('x-sample', 'success');
    $this->assertAttributeEquals(
      array('X-Sample' => 'success'), '_headers', $headers
    );
  }

  /**
  * @covers \Papaya\Response\Headers::set
  * @covers \Papaya\Response\Headers::_normalize
  */
  public function testSetTwoValues() {
    $headers = new \Papaya\Response\Headers();
    $headers->set('X-Sample', 21);
    $headers->set('x-sample', 42, FALSE);
    $this->assertAttributeEquals(
      array('X-Sample' => array(21, 42)), '_headers', $headers
    );
  }

  /**
  * @covers \Papaya\Response\Headers::set
  * @covers \Papaya\Response\Headers::_normalize
  */
  public function testSetThreeValues() {
    $headers = new \Papaya\Response\Headers();
    $headers->set('X-Sample', 21);
    $headers->set('x-sample', 42, FALSE);
    $headers->set('x-sample', 23, FALSE);
    $this->assertAttributeEquals(
      array('X-Sample' => array(21, 42, 23)), '_headers', $headers
    );
  }

  /**
  * @covers \Papaya\Response\Headers::remove
  * @covers \Papaya\Response\Headers::_normalize
  */
  public function testRemove() {
    $headers = new \Papaya\Response\Headers();
    $headers->set('X-Sample', 'failed');
    $headers->remove('x-sample');
    $this->assertAttributeEquals(
      array(), '_headers', $headers
    );
  }

  /**
  * @covers \Papaya\Response\Headers::offsetSet
  */
  public function testOffsetSet() {
    $headers = new \Papaya\Response\Headers();
    $headers['X-Sample'] = 'success';
    $this->assertAttributeEquals(
      array('X-Sample' => 'success'), '_headers', $headers
    );
  }

  /**
  * @covers \Papaya\Response\Headers::offsetExists
  */
  public function testOffsetExists() {
    $headers = new \Papaya\Response\Headers();
    $this->assertFalse(isset($headers['X-Sample']));
    $headers['X-Sample'] = 'success';
    $this->assertTrue(isset($headers['X-Sample']));
  }

  /**
  * @covers \Papaya\Response\Headers::offsetUnset
  */
  public function testOffsetUnset() {
    $headers = new \Papaya\Response\Headers();
    $headers['X-Sample'] = 'success';
    unset($headers['x-sample']);
    $this->assertAttributeEquals(
      array(), '_headers', $headers
    );
  }

  /**
  * @covers \Papaya\Response\Headers::offsetGet
  */
  public function testOffsetGet() {
    $headers = new \Papaya\Response\Headers();
    $headers['X-Sample'] = 'success';
    $this->assertEquals(
      'success', $headers['x-sample']
    );
  }
}
