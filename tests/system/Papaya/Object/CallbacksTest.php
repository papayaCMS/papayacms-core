<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaObjectCallbacksTest extends PapayaTestCase {

  /**
  * @covers PapayaObjectCallbacks::__construct
  * @covers PapayaObjectCallbacks::defineCallbacks
  */
  public function testContructor() {
    $list = new PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $this->assertEquals(23, $list->sample->defaultReturn);
  }

  /**
  * @covers PapayaObjectCallbacks::__construct
  * @covers PapayaObjectCallbacks::defineCallbacks
  */
  public function testConstructorWithoutDefinitionsExpectingException() {
    $this->setExpectedException(
      'LogicException',
      'No callback definitions provided.'
    );
    new PapayaObjectCallbacks(array());
  }

  /**
  * @covers PapayaObjectCallbacks::__construct
  * @covers PapayaObjectCallbacks::defineCallbacks
  */
  public function testConstructorWithInvalidDefinitionsExpectingException() {
    $this->setExpectedException(
      'LogicException',
      'Method "blocker" does already exists and can not be defined as a callback.'
    );
    new PapayaObjectCallbacks_TestProxy(array('blocker' => NULL));
  }

  /**
  * @covers PapayaObjectCallbacks::__isset
  */
  public function testMagicMethodIssetExpectingTrue() {
    $list = new PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $list->sample = 'substr';
    $this->assertTrue(isset($list->sample));
  }

  /**
  * @covers PapayaObjectCallbacks::__isset
  */
  public function testMagicMethodIssetExpectingFalse() {
    $list = new PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $this->assertFalse(isset($list->sample));
  }

  /**
  * @covers PapayaObjectCallbacks::__get
  * @covers PapayaObjectCallbacks::__set
  * @covers PapayaObjectCallbacks::validateName
  */
  public function testGetAfterSetWithNull() {
    $list = new PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $list->sample = 'substr';
    $list->sample = NULL;
    $this->assertNull($list->sample->callback);
    $this->assertEquals(23, $list->sample->defaultReturn);
  }

  /**
  * @covers PapayaObjectCallbacks::__get
  * @covers PapayaObjectCallbacks::__set
  * @covers PapayaObjectCallbacks::validateName
  */
  public function testGetAfterSetWithCallback() {
    $list = new PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $list->sample = 'substr';
    $this->assertEquals('substr', $list->sample->callback);
    $this->assertEquals(23, $list->sample->defaultReturn);
  }

  /**
  * @covers PapayaObjectCallbacks::__get
  * @covers PapayaObjectCallbacks::__set
  * @covers PapayaObjectCallbacks::validateName
  */
  public function testGetAfterSetWithPapayaObjectCallbackObject() {
    $callback = $this->createMock(PapayaObjectCallback::class);
    $list = new PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $list->sample = $callback;
    $this->assertSame($callback, $list->sample);
  }

  /**
  * @covers PapayaObjectCallbacks::__set
  * @covers PapayaObjectCallbacks::validateName
  */
  public function testGetWithInvalidValueExpectingException() {
    $list = new PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $this->setExpectedException(
      'LogicException',
      'Argument $callback must be an valid Callback or an instance of PapayaObjectCallback.'
    );
    $list->sample = new stdClass;
  }

  /**
  * @covers PapayaObjectCallbacks::__get
  * @covers PapayaObjectCallbacks::validateName
  */
  public function testGetWithInvalidNameExpectingException() {
    $list = new PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $this->setExpectedException(
      'LogicException',
      'Invalid callback name: UNKNOWN.'
    );
    $list->UNKNOWN = NULL;
  }

  /**
  * @covers PapayaObjectCallbacks::__unset
  */
  public function testUnsetCreatesNewPapayaObjectCallbackObject() {
    $list = new PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $list->sample = 'substr';
    unset($list->sample);
    $this->assertNull($list->sample->callback);
  }

  /**
  * @covers PapayaObjectCallbacks::__call
  */
  public function testCallExecutesCallback() {
    $list = new PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $list->sample = array($this, 'callbackSample');
    $this->assertEquals(42, $list->sample(42));
  }

  public function callbackSample($context, $argument) {
    return $argument;
  }

  /**
  * @covers PapayaObjectCallbacks::__call
  */
  public function testCallWithInvalidNameExpectingException() {
    $list = new PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $this->setExpectedException(
      'LogicException',
      'Invalid callback name: UNKNOWN.'
    );
    $list->UNKNOWN();
  }

  /**
  * @covers PapayaObjectCallbacks::getIterator
  */
  public function testGetIterator() {
    $list = new PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $this->assertEquals(
      array('sample' => new PapayaObjectCallback(23)),
      iterator_to_array($list)
    );
  }

  /**
  * @covers PapayaObjectCallbacks::getIterator
  */
  public function testGetIteratorAfterSet() {
    $callback = $this->createMock(PapayaObjectCallback::class);
    $list = new PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $list->sample = $callback;
    $this->assertSame(array('sample' => $callback), iterator_to_array($list));
  }
}

/**
 * @property PapayaObjectCallback $sample
 * @method mixed sample
 */
class PapayaObjectCallbacks_TestProxy extends PapayaObjectCallbacks {
  public function blocker() {
  }
}
