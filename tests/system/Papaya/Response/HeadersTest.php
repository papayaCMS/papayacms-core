<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaResponseHeadersTest extends PapayaTestCase {

  /**
  * @covers PapayaResponseHeaders::getIterator
  */
  public function testGetIterator() {
    $headers = new PapayaResponseHeaders();
    $iterator = $headers->getIterator();
    $this->assertInstanceOf('IteratorAggregate', $headers);
    $this->assertInstanceOf('ArrayIterator', $iterator);
  }

  /**
  * @covers PapayaResponseHeaders::count
  */
  public function testCountExpectingZero() {
    $headers = new PapayaResponseHeaders();
    $this->assertEquals(0, count($headers));
  }

  /**
  * @covers PapayaResponseHeaders::count
  */
  public function testCountExpectingOne() {
    $headers = new PapayaResponseHeaders();
    $headers['X-Sample'] = 'success';
    $this->assertEquals(1, count($headers));
  }

  /**
  * @covers PapayaResponseHeaders::set
  * @covers PapayaResponseHeaders::_normalize
  */
  public function testSet() {
    $headers = new PapayaResponseHeaders();
    $headers->set('X-sample', 'success');
    $this->assertAttributeEquals(
      array('X-Sample' => 'success'), '_headers', $headers
    );
  }

  /**
  * @covers PapayaResponseHeaders::set
  * @covers PapayaResponseHeaders::_normalize
  */
  public function testSetTwoHeaders() {
    $headers = new PapayaResponseHeaders();
    $headers->set('X-sample-1', 'success');
    $headers->set('x-Sample-2', 'success');
    $this->assertAttributeEquals(
      array('X-Sample-1' => 'success', 'X-Sample-2' => 'success'), '_headers', $headers
    );
  }

  /**
  * @covers PapayaResponseHeaders::set
  * @covers PapayaResponseHeaders::_normalize
  */
  public function testSetReplaceFirst() {
    $headers = new PapayaResponseHeaders();
    $headers->set('X-Sample', 'failed');
    $headers->set('x-sample', 'success');
    $this->assertAttributeEquals(
      array('X-Sample' => 'success'), '_headers', $headers
    );
  }

  /**
  * @covers PapayaResponseHeaders::set
  * @covers PapayaResponseHeaders::_normalize
  */
  public function testSetTwoValues() {
    $headers = new PapayaResponseHeaders();
    $headers->set('X-Sample', 21);
    $headers->set('x-sample', 42, FALSE);
    $this->assertAttributeEquals(
      array('X-Sample' => array(21, 42)), '_headers', $headers
    );
  }

  /**
  * @covers PapayaResponseHeaders::set
  * @covers PapayaResponseHeaders::_normalize
  */
  public function testSetThreeValues() {
    $headers = new PapayaResponseHeaders();
    $headers->set('X-Sample', 21);
    $headers->set('x-sample', 42, FALSE);
    $headers->set('x-sample', 23, FALSE);
    $this->assertAttributeEquals(
      array('X-Sample' => array(21, 42, 23)), '_headers', $headers
    );
  }

  /**
  * @covers PapayaResponseHeaders::remove
  * @covers PapayaResponseHeaders::_normalize
  */
  public function testRemove() {
    $headers = new PapayaResponseHeaders();
    $headers->set('X-Sample', 'failed');
    $headers->remove('x-sample');
    $this->assertAttributeEquals(
      array(), '_headers', $headers
    );
  }

  /**
  * @covers PapayaResponseHeaders::offsetSet
  */
  public function testOffsetSet() {
    $headers = new PapayaResponseHeaders();
    $headers['X-Sample'] = 'success';
    $this->assertAttributeEquals(
      array('X-Sample' => 'success'), '_headers', $headers
    );
  }

  /**
  * @covers PapayaResponseHeaders::offsetExists
  */
  public function testOffsetExists() {
    $headers = new PapayaResponseHeaders();
    $this->assertFalse(isset($headers['X-Sample']));
    $headers['X-Sample'] = 'success';
    $this->assertTrue(isset($headers['X-Sample']));
  }

  /**
  * @covers PapayaResponseHeaders::offsetUnset
  */
  public function testOffsetUnset() {
    $headers = new PapayaResponseHeaders();
    $headers['X-Sample'] = 'success';
    unset($headers['x-sample']);
    $this->assertAttributeEquals(
      array(), '_headers', $headers
    );
  }

  /**
  * @covers PapayaResponseHeaders::offsetGet
  */
  public function testOffsetGet() {
    $headers = new PapayaResponseHeaders();
    $headers['X-Sample'] = 'success';
    $this->assertEquals(
      'success', $headers['x-sample']
    );
  }
}