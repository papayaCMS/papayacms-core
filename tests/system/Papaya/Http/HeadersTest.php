<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaHttpHeadersTest extends PapayaTestCase {

  /**
  * @covers PapayaHttpHeaders::__construct
  */
  public function testConstructor() {
    $headers = new PapayaHttpHeaders(
      array('X-Hello' => 'World')
    );
    $this->assertAttributeEquals(
      array('X-Hello' => 'World'), '_headers', $headers
    );
  }

  /**
  * @covers PapayaHttpHeaders::toArray
  */
  public function testToArray() {
    $headers = new PapayaHttpHeaders(
      array('X-Hello' => 'World')
    );
    $this->assertEquals(
      array('X-Hello' => 'World'), $headers->toArray()
    );
  }

  /**
  * @covers PapayaHttpHeaders::getIterator
  */
  public function testGetIterator() {
    $headers = new PapayaHttpHeaders(
      array('X-Hello' => 'World')
    );
    $iterator = $headers->getIterator();
    $this->assertInstanceOf('ArrayIterator', $iterator);
    $this->assertEquals(
      array('X-Hello' => 'World'), $iterator->getArrayCopy()
    );
  }

  /**
  * @covers PapayaHttpHeaders::count
  */
  public function testCount() {
    $headers = new PapayaHttpHeaders(
      array('X-Hello' => 'World', 'X-World' => 'Hello')
    );
    $this->assertEquals(
      2, count($headers)
    );
  }

  /**
  * @covers PapayaHttpHeaders::get
  * @covers PapayaHttpHeaders::normalizeName
  * @dataProvider provideValidHeaderVariants
  */
  public function testGet($name) {
    $headers = new PapayaHttpHeaders(
      array('X-Hello' => 'World')
    );
    $this->assertEquals(
      'World', $headers->get($name)
    );
  }

  /**
  * @covers PapayaHttpHeaders::get
  */
  public function testGetWithInvalidNameExpectingNull() {
    $headers = new PapayaHttpHeaders(
      array('X-Hello' => 'World')
    );
    $this->assertNull(
      $headers->get('Invalid')
    );
  }

  /**
  * @covers PapayaHttpHeaders::set
  */
  public function testSet() {
    $headers = new PapayaHttpHeaders();
    $headers->set('X-Hello', 'World');
    $this->assertAttributeEquals(
      array('X-Hello' => 'World'), '_headers', $headers
    );
  }

  /**
  * @covers PapayaHttpHeaders::set
  */
  public function testSetReplacesFirst() {
    $headers = new PapayaHttpHeaders();
    $headers->set('X-Hello', 'World');
    $headers->set('X-Hello', 'Moon');
    $this->assertAttributeEquals(
      array('X-Hello' => 'Moon'), '_headers', $headers
    );
  }

  /**
  * @covers PapayaHttpHeaders::set
  */
  public function testSetAllowsDuplicates() {
    $headers = new PapayaHttpHeaders();
    $headers->set('X-Hello', 'World');
    $headers->set('X-Hello', 'Moon', TRUE);
    $this->assertAttributeEquals(
      array('X-Hello' => array('World', 'Moon')), '_headers', $headers
    );
  }

  /**
  * @covers PapayaHttpHeaders::set
  */
  public function testSetEmptyValueRemovesHeader() {
    $headers = new PapayaHttpHeaders();
    $headers->set('X-Hello', 'World');
    $headers->set('X-Hello', '');
    $this->assertAttributeEquals(
      array(), '_headers', $headers
    );
  }

  /**
  * @covers PapayaHttpHeaders::set
  */
  public function testSetEmptyValueOnNoneExistingHeader() {
    $headers = new PapayaHttpHeaders();
    $headers->set('X-Hello', '');
    $this->assertAttributeEquals(
      array(), '_headers', $headers
    );
  }

  /**
  * @covers PapayaHttpHeaders::set
  */
  public function testSetEmptyNameReturnsFalse() {
    $headers = new PapayaHttpHeaders();
    $this->assertFalse(
      $headers->set('', '')
    );
  }

  /**
  * @covers PapayaHttpHeaders::offsetExists
  */
  public function testOffsetExistsExpectingTrue() {
    $headers = new PapayaHttpHeaders();
    $headers->set('X-Hello', 'World');
    $this->assertTrue(isset($headers['X-Hello']));
  }

  /**
  * @covers PapayaHttpHeaders::offsetExists
  */
  public function testOffsetExistsExpectingFalse() {
    $headers = new PapayaHttpHeaders();
    $this->assertFalse(isset($headers['X-Hello']));
  }

  /**
  * @covers PapayaHttpHeaders::offsetGet
  */
  public function testOffsetGet() {
    $headers = new PapayaHttpHeaders();
    $headers->set('X-Hello', 'World');
    $this->assertEquals('World', $headers['X-Hello']);
  }

  /**
  * @covers PapayaHttpHeaders::offsetSet
  */
  public function testOffsetSet() {
    $headers = new PapayaHttpHeaders();
    $headers['X-Hello'] = 'World';
    $this->assertAttributeEquals(
      array('X-Hello' => 'World'), '_headers', $headers
    );
  }

  /**
  * @covers PapayaHttpHeaders::offsetUnset
  */
  public function testOffsetUnset() {
    $headers = new PapayaHttpHeaders();
    $headers->set('X-Hello', 'World');
    unset($headers['X-Hello']);
    $this->assertAttributeEquals(
      array(), '_headers', $headers
    );
  }

  /**
  * @covers PapayaHttpHeaders::offsetUnset
  */
  public function testOffetUnsetOnNoneExistingHeader() {
    $headers = new PapayaHttpHeaders();
    unset($headers['X-Hello']);
    $this->assertAttributeEquals(
      array(), '_headers', $headers
    );
  }

  /**
  * @covers PapayaHttpHeaders::__toString
  */
  public function testMagicMethodToString() {
    $headers = new PapayaHttpHeaders();
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
