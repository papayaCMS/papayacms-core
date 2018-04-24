<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaPluginHookableContextTest extends PapayaTestCase {

  /**
   * @covers PapayaPluginHookableContext::__construct
   */
  public function testConstructor() {
    $context = new PapayaPluginHookableContext();
    $this->assertFalse($context->hasParent());
  }

  /**
   * @covers PapayaPluginHookableContext::__construct
   */
  public function testConstructorWithAllArguments() {
    $context = new PapayaPluginHookableContext($parent = new stdClass(), array('foo' => 'bar'));
    $this->assertSame($parent, $context->getParent());
    $this->assertEquals(array('foo' => 'bar'), iterator_to_array($context->data()));
  }

  /**
   * @covers PapayaPluginHookableContext::hasParent
   */
  public function testHasParentExpectingTrue() {
    $context = new PapayaPluginHookableContext(new stdClass());
    $this->assertTrue($context->hasParent());
  }

  /**
   * @covers PapayaPluginHookableContext::hasParent
   */
  public function testHasParentExpectingFalse() {
    $context = new PapayaPluginHookableContext();
    $this->assertFalse($context->hasParent());
  }

  /**
   * @covers PapayaPluginHookableContext::getParent
   */
  public function testGetParent() {
    $context = new PapayaPluginHookableContext($parent = new stdClass());
    $this->assertSame($parent, $context->getParent());
  }

  /**
   * @covers PapayaPluginHookableContext::getParent
   */
  public function testGetParentWithoutParentExpectingException() {
    $context = new PapayaPluginHookableContext();
    $this->setExpectedException('LogicException');
    $context->getParent();
  }

  /**
   * @covers PapayaPluginHookableContext::data
   */
  public function testGetDataImplicitCreate() {
    $context = new PapayaPluginHookableContext();
    $this->assertInstanceOf('PapayaPluginEditableContent', $context->data());
  }

  /**
   * @covers PapayaPluginHookableContext::data
   */
  public function testGetDataContainsDataFromContructor() {
    $context = new PapayaPluginHookableContext(NULL, array('foo' => 'bar'));
    $this->assertEquals(array('foo' => 'bar'), iterator_to_array($context->data()));
  }

  /**
   * @covers PapayaPluginHookableContext::data
   */
  public function testDataAssigningArrayOverridesContructorData() {
    $context = new PapayaPluginHookableContext(NULL, array('foo' => 'bar'));
    $context->data(array('success' => 42));
    $this->assertEquals(array('success' => 42), iterator_to_array($context->data()));
  }

  /**
   * @covers PapayaPluginHookableContext::data
   */
  public function testDataReturnsContentObjectFromConstructor() {
    $data = $this->getMock('PapayaPluginEditableContent');
    $context = new PapayaPluginHookableContext(NULL, $data);
    $this->assertSame($data, $context->data());
  }

  /**
   * @covers PapayaPluginHookableContext::data
   */
  public function testDataAssingingNewContentObject() {
    $data = $this->getMock('PapayaPluginEditableContent');
    $context = new PapayaPluginHookableContext();
    $context->data($data);
    $this->assertSame($data, $context->data());
  }

  /**
   * @covers PapayaPluginHookableContext::data
   */
  public function testDataAddingValues() {
    $context = new PapayaPluginHookableContext(NULL, array('foo' => 'bar'));
    $context->data()->merge(array('bar' => 'foo'));
    $this->assertEquals(
      array('foo' => 'bar', 'bar' => 'foo'),
      iterator_to_array($context->data())
    );
  }
}
