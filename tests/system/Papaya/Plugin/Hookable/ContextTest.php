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

class PapayaPluginHookableContextTest extends \PapayaTestCase {

  /**
   * @covers \PapayaPluginHookableContext::__construct
   */
  public function testConstructor() {
    $context = new \PapayaPluginHookableContext();
    $this->assertFalse($context->hasParent());
  }

  /**
   * @covers \PapayaPluginHookableContext::__construct
   */
  public function testConstructorWithAllArguments() {
    $context = new \PapayaPluginHookableContext($parent = new stdClass(), array('foo' => 'bar'));
    $this->assertSame($parent, $context->getParent());
    $this->assertEquals(array('foo' => 'bar'), iterator_to_array($context->data()));
  }

  /**
   * @covers \PapayaPluginHookableContext::hasParent
   */
  public function testHasParentExpectingTrue() {
    $context = new \PapayaPluginHookableContext(new stdClass());
    $this->assertTrue($context->hasParent());
  }

  /**
   * @covers \PapayaPluginHookableContext::hasParent
   */
  public function testHasParentExpectingFalse() {
    $context = new \PapayaPluginHookableContext();
    $this->assertFalse($context->hasParent());
  }

  /**
   * @covers \PapayaPluginHookableContext::getParent
   */
  public function testGetParent() {
    $context = new \PapayaPluginHookableContext($parent = new stdClass());
    $this->assertSame($parent, $context->getParent());
  }

  /**
   * @covers \PapayaPluginHookableContext::getParent
   */
  public function testGetParentWithoutParentExpectingException() {
    $context = new \PapayaPluginHookableContext();
    $this->expectException(LogicException::class);
    $context->getParent();
  }

  /**
   * @covers \PapayaPluginHookableContext::data
   */
  public function testGetDataImplicitCreate() {
    $context = new \PapayaPluginHookableContext();
    $this->assertInstanceOf(\PapayaPluginEditableContent::class, $context->data());
  }

  /**
   * @covers \PapayaPluginHookableContext::data
   */
  public function testGetDataContainsDataFromContructor() {
    $context = new \PapayaPluginHookableContext(NULL, array('foo' => 'bar'));
    $this->assertEquals(array('foo' => 'bar'), iterator_to_array($context->data()));
  }

  /**
   * @covers \PapayaPluginHookableContext::data
   */
  public function testDataAssigningArrayOverridesContructorData() {
    $context = new \PapayaPluginHookableContext(NULL, array('foo' => 'bar'));
    $context->data(array('success' => 42));
    $this->assertEquals(array('success' => 42), iterator_to_array($context->data()));
  }

  /**
   * @covers \PapayaPluginHookableContext::data
   */
  public function testDataReturnsContentObjectFromConstructor() {
    $data = $this->createMock(\PapayaPluginEditableContent::class);
    $context = new \PapayaPluginHookableContext(NULL, $data);
    $this->assertSame($data, $context->data());
  }

  /**
   * @covers \PapayaPluginHookableContext::data
   */
  public function testDataAssingingNewContentObject() {
    $data = $this->createMock(\PapayaPluginEditableContent::class);
    $context = new \PapayaPluginHookableContext();
    $context->data($data);
    $this->assertSame($data, $context->data());
  }

  /**
   * @covers \PapayaPluginHookableContext::data
   */
  public function testDataAddingValues() {
    $context = new \PapayaPluginHookableContext(NULL, array('foo' => 'bar'));
    $context->data()->merge(array('bar' => 'foo'));
    $this->assertEquals(
      array('foo' => 'bar', 'bar' => 'foo'),
      iterator_to_array($context->data())
    );
  }
}
