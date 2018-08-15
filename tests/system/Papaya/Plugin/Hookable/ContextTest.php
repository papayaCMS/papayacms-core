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
   * @covers \Papaya\Plugin\Hookable\Context::__construct
   */
  public function testConstructor() {
    $context = new \Papaya\Plugin\Hookable\Context();
    $this->assertFalse($context->hasParent());
  }

  /**
   * @covers \Papaya\Plugin\Hookable\Context::__construct
   */
  public function testConstructorWithAllArguments() {
    $context = new \Papaya\Plugin\Hookable\Context($parent = new \stdClass(), array('foo' => 'bar'));
    $this->assertSame($parent, $context->getParent());
    $this->assertEquals(array('foo' => 'bar'), iterator_to_array($context->data()));
  }

  /**
   * @covers \Papaya\Plugin\Hookable\Context::hasParent
   */
  public function testHasParentExpectingTrue() {
    $context = new \Papaya\Plugin\Hookable\Context(new \stdClass());
    $this->assertTrue($context->hasParent());
  }

  /**
   * @covers \Papaya\Plugin\Hookable\Context::hasParent
   */
  public function testHasParentExpectingFalse() {
    $context = new \Papaya\Plugin\Hookable\Context();
    $this->assertFalse($context->hasParent());
  }

  /**
   * @covers \Papaya\Plugin\Hookable\Context::getParent
   */
  public function testGetParent() {
    $context = new \Papaya\Plugin\Hookable\Context($parent = new \stdClass());
    $this->assertSame($parent, $context->getParent());
  }

  /**
   * @covers \Papaya\Plugin\Hookable\Context::getParent
   */
  public function testGetParentWithoutParentExpectingException() {
    $context = new \Papaya\Plugin\Hookable\Context();
    $this->expectException(\LogicException::class);
    $context->getParent();
  }

  /**
   * @covers \Papaya\Plugin\Hookable\Context::data
   */
  public function testGetDataImplicitCreate() {
    $context = new \Papaya\Plugin\Hookable\Context();
    $this->assertInstanceOf(\Papaya\Plugin\Editable\Content::class, $context->data());
  }

  /**
   * @covers \Papaya\Plugin\Hookable\Context::data
   */
  public function testGetDataContainsDataFromContructor() {
    $context = new \Papaya\Plugin\Hookable\Context(NULL, array('foo' => 'bar'));
    $this->assertEquals(array('foo' => 'bar'), iterator_to_array($context->data()));
  }

  /**
   * @covers \Papaya\Plugin\Hookable\Context::data
   */
  public function testDataAssigningArrayOverridesContructorData() {
    $context = new \Papaya\Plugin\Hookable\Context(NULL, array('foo' => 'bar'));
    $context->data(array('success' => 42));
    $this->assertEquals(array('success' => 42), iterator_to_array($context->data()));
  }

  /**
   * @covers \Papaya\Plugin\Hookable\Context::data
   */
  public function testDataReturnsContentObjectFromConstructor() {
    $data = $this->createMock(\Papaya\Plugin\Editable\Content::class);
    $context = new \Papaya\Plugin\Hookable\Context(NULL, $data);
    $this->assertSame($data, $context->data());
  }

  /**
   * @covers \Papaya\Plugin\Hookable\Context::data
   */
  public function testDataAssingingNewContentObject() {
    $data = $this->createMock(\Papaya\Plugin\Editable\Content::class);
    $context = new \Papaya\Plugin\Hookable\Context();
    $context->data($data);
    $this->assertSame($data, $context->data());
  }

  /**
   * @covers \Papaya\Plugin\Hookable\Context::data
   */
  public function testDataAddingValues() {
    $context = new \Papaya\Plugin\Hookable\Context(NULL, array('foo' => 'bar'));
    $context->data()->merge(array('bar' => 'foo'));
    $this->assertEquals(
      array('foo' => 'bar', 'bar' => 'foo'),
      iterator_to_array($context->data())
    );
  }
}
