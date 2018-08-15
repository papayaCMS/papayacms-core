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

class PapayaObjectCallbackTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\BaseObject\Callback::__construct
  */
  public function testConstructor() {
    $callback = new \Papaya\BaseObject\Callback('foo');
    $this->assertEquals('foo', $callback->defaultReturn);
    $this->assertEquals(new StdClass, $callback->context);
  }

  /**
  * @covers \Papaya\BaseObject\Callback::execute
  */
  public function testExecuteReturnsDefault() {
    $callback = new \Papaya\BaseObject\Callback('foo');
    $this->assertEquals('foo', $callback->execute());
  }

  /**
  * @covers \Papaya\BaseObject\Callback::execute
  */
  public function testExecuteWithDefinedCallback() {
    $callback = new \Papaya\BaseObject\Callback(NULL);
    $callback->callback = array($this, 'callbackSample');
    $callback->context->prefix = 'foo';
    $this->assertEquals('foobar', $callback->execute('bar'));
  }

  public function callbackSample($context, $argument) {
    return $context->prefix.$argument;
  }

  /**
   * @covers \Papaya\BaseObject\Callback::execute
   */
  public function testExecuteWithDefinedCallbackWithoutContext() {
    $callback = new \Papaya\BaseObject\Callback(NULL, FALSE);
    $callback->callback = array($this, 'callbackSampleNoContext');
    $callback->context->prefix = 'foo';
    $this->assertEquals('bar', $callback->execute('bar'));
  }

  public function callbackSampleNoContext($argument) {
    return $argument;
  }

  /**
  * @covers \Papaya\BaseObject\Callback::__isset
  * @covers \Papaya\BaseObject\Callback::getPropertyName
  */
  public function testIssetPropertyDefaultReturnExpectingFalse() {
    $callback = new \Papaya\BaseObject\Callback(NULL);
    $this->assertFalse(isset($callback->defaultReturn));
  }

  /**
  * @covers \Papaya\BaseObject\Callback::__isset
  * @covers \Papaya\BaseObject\Callback::getPropertyName
  */
  public function testIssetPropertyDefaultReturnExpectingTrue() {
    $callback = new \Papaya\BaseObject\Callback('foo');
    $this->assertTrue(isset($callback->defaultReturn));
  }

  /**
  * @covers \Papaya\BaseObject\Callback::__get
  * @covers \Papaya\BaseObject\Callback::__set
  * @covers \Papaya\BaseObject\Callback::getPropertyName
  */
  public function testPropertyDefaultReturnGetAfterSet() {
    $callback = new \Papaya\BaseObject\Callback(NULL);
    $callback->defaultReturn = 'foo';
    $this->assertEquals('foo', $callback->defaultReturn);
  }

  /**
  * @covers \Papaya\BaseObject\Callback::__unset
  * @covers \Papaya\BaseObject\Callback::getPropertyName
  */
  public function testUnsetPropertyDefaultReturn() {
    $callback = new \Papaya\BaseObject\Callback('foo');
    unset($callback->defaultReturn);
    $this->assertFalse(isset($callback->defaultReturn));
  }

  /**
  * @covers \Papaya\BaseObject\Callback::__isset
  */
  public function testIssetPropertyCallbackExpectingFalse() {
    $callback = new \Papaya\BaseObject\Callback(NULL);
    $this->assertFalse(isset($callback->callback));
  }

  /**
  * @covers \Papaya\BaseObject\Callback::__isset
  */
  public function testIssetPropertyCallbackExpectingTrue() {
    $callback = new \Papaya\BaseObject\Callback(NULL);
    $callback->callback = 'substr';
    $this->assertTrue(isset($callback->callback));
  }

  /**
  * @covers \Papaya\BaseObject\Callback::__get
  * @covers \Papaya\BaseObject\Callback::__set
  */
  public function testPropertyCallbackGetAfterSet() {
    $callback = new \Papaya\BaseObject\Callback(NULL);
    $callback->callback = 'substr';
    $this->assertEquals('substr', $callback->callback);
  }

  /**
  * @covers \Papaya\BaseObject\Callback::__unset
  */
  public function testUnsetPropertyCallback() {
    $callback = new \Papaya\BaseObject\Callback(NULL);
    /** @noinspection PhpUndefinedFieldInspection */
    $this->callback = 'substr';
    unset($callback->callback);
    $this->assertFalse(isset($callback->callback));
  }

  /**
  * @covers \Papaya\BaseObject\Callback::__isset
  */
  public function testIssetPropertyContextExpectingTrue() {
    $callback = new \Papaya\BaseObject\Callback(NULL);
    $this->assertTrue(isset($callback->context));
  }

  /**
  * @covers \Papaya\BaseObject\Callback::__get
  * @covers \Papaya\BaseObject\Callback::__set
  */
  public function testPropertyContextGetAfterSet() {
    $callback = new \Papaya\BaseObject\Callback(NULL);
    $callback->context = $context = new \stdClass;
    $this->assertSame($context, $callback->context);
  }

  /**
  * @covers \Papaya\BaseObject\Callback::__unset
  */
  public function testUnsetPropertyContext() {
    $callback = new \Papaya\BaseObject\Callback(NULL);
    $callback->context->foo = 'bar';
    unset($callback->context);
    $this->assertEquals(new \stdClass, $callback->context);
  }

  /**
  * @covers \Papaya\BaseObject\Callback::getPropertyName
  */
  public function testMagicSetWithUnknownProperty() {
    $callback = new \Papaya\BaseObject\Callback(NULL);
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('Unknown property Papaya\BaseObject\Callback::$UNKNOWN');
    /** @noinspection PhpUndefinedFieldInspection */
    $callback->UNKNOWN = NULL;
  }
}
