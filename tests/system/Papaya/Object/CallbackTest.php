<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaObjectCallbackTest extends PapayaTestCase {

  /**
  * @covers PapayaObjectCallback::__construct
  */
  public function testConstructor() {
    $callback = new PapayaObjectCallback('foo');
    $this->assertEquals('foo', $callback->defaultReturn);
    $this->assertEquals(new StdClass, $callback->context);
  }

  /**
  * @covers PapayaObjectCallback::execute
  */
  public function testExecuteReturnsDefault() {
    $callback = new PapayaObjectCallback('foo');
    $this->assertEquals('foo', $callback->execute());
  }

  /**
  * @covers PapayaObjectCallback::execute
  */
  public function testExecuteWithDefinedCallback() {
    $callback = new PapayaObjectCallback(NULL);
    $callback->callback = array($this, 'callbackSample');
    $callback->context->prefix = 'foo';
    $this->assertEquals('foobar', $callback->execute('bar'));
  }

  public function callbackSample($context, $argument) {
    return $context->prefix.$argument;
  }

  /**
  * @covers PapayaObjectCallback::__isset
  * @covers PapayaObjectCallback::getPropertyName
  */
  public function testIssetPropertyDefaultReturnExpectingFalse() {
    $callback = new PapayaObjectCallback(NULL);
    $this->assertFalse(isset($callback->defaultReturn));
  }

  /**
  * @covers PapayaObjectCallback::__isset
  * @covers PapayaObjectCallback::getPropertyName
  */
  public function testIssetPropertyDefaultReturnExpectingTrue() {
    $callback = new PapayaObjectCallback('foo');
    $this->assertTrue(isset($callback->defaultReturn));
  }

  /**
  * @covers PapayaObjectCallback::__get
  * @covers PapayaObjectCallback::__set
  * @covers PapayaObjectCallback::getPropertyName
  */
  public function testPropertyDefaultReturnGetAfterSet() {
    $callback = new PapayaObjectCallback(NULL);
    $callback->defaultReturn = 'foo';
    $this->assertEquals('foo', $callback->defaultReturn);
  }

  /**
  * @covers PapayaObjectCallback::__unset
  * @covers PapayaObjectCallback::getPropertyName
  */
  public function testUnsetPropertyDefaultReturn() {
    $callback = new PapayaObjectCallback('foo');
    unset($callback->defaultReturn);
    $this->assertFalse(isset($callback->defaultReturn));
  }

  /**
  * @covers PapayaObjectCallback::__isset
  */
  public function testIssetPropertyCallbackExpectingFalse() {
    $callback = new PapayaObjectCallback(NULL);
    $this->assertFalse(isset($callback->callback));
  }

  /**
  * @covers PapayaObjectCallback::__isset
  */
  public function testIssetPropertyCallbackExpectingTrue() {
    $callback = new PapayaObjectCallback(NULL);
    $callback->callback = 'substr';
    $this->assertTrue(isset($callback->callback));
  }

  /**
  * @covers PapayaObjectCallback::__get
  * @covers PapayaObjectCallback::__set
  */
  public function testPropertyCallbackGetAfterSet() {
    $callback = new PapayaObjectCallback(NULL);
    $callback->callback = 'substr';
    $this->assertEquals('substr', $callback->callback);
  }

  /**
  * @covers PapayaObjectCallback::__unset
  */
  public function testUnsetPropertyCallback() {
    $callback = new PapayaObjectCallback(NULL);
    $this->callback = 'substr';
    unset($callback->callback);
    $this->assertFalse(isset($callback->callback));
  }

  /**
  * @covers PapayaObjectCallback::__isset
  */
  public function testIssetPropertyContextExpectingTrue() {
    $callback = new PapayaObjectCallback(NULL);
    $this->assertTrue(isset($callback->context));
  }

  /**
  * @covers PapayaObjectCallback::__get
  * @covers PapayaObjectCallback::__set
  */
  public function testPropertyContextGetAfterSet() {
    $callback = new PapayaObjectCallback(NULL);
    $callback->context = $context = new stdClass;
    $this->assertSame($context, $callback->context);
  }

  /**
  * @covers PapayaObjectCallback::__unset
  */
  public function testUnsetPropertyContext() {
    $callback = new PapayaObjectCallback(NULL);
    $callback->context->foo = 'bar';
    unset($callback->context);
    $this->assertEquals(new stdClass, $callback->context);
  }

  /**
  * @covers PapayaObjectCallback::getPropertyName
  */
  public function testMagicSetWithUnknownProperty() {
    $callback = new PapayaObjectCallback(NULL);
    $this->setExpectedException(
      'UnexpectedValueException',
      'Unknown property PapayaObjectCallback::$UNKNOWN'
    );
    $callback->UNKNOWN = NULL;
  }
}