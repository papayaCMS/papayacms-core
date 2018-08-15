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

class PapayaObjectCallbacksTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\BaseObject\Callbacks::__construct
  * @covers \Papaya\BaseObject\Callbacks::defineCallbacks
  */
  public function testContructor() {
    $list = new \PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $this->assertEquals(23, $list->sample->defaultReturn);
  }

  /**
  * @covers \Papaya\BaseObject\Callbacks::__construct
  * @covers \Papaya\BaseObject\Callbacks::defineCallbacks
  */
  public function testConstructorWithoutDefinitionsExpectingException() {
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('No callback definitions provided.');
    new \Papaya\BaseObject\Callbacks(array());
  }

  /**
  * @covers \Papaya\BaseObject\Callbacks::__construct
  * @covers \Papaya\BaseObject\Callbacks::defineCallbacks
  */
  public function testConstructorWithInvalidDefinitionsExpectingException() {
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Method "blocker" does already exists and can not be defined as a callback.');
    new \PapayaObjectCallbacks_TestProxy(array('blocker' => NULL));
  }

  /**
  * @covers \Papaya\BaseObject\Callbacks::__isset
  */
  public function testMagicMethodIssetExpectingTrue() {
    $list = new \PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $list->sample = 'substr';
    $this->assertTrue(isset($list->sample));
  }

  /**
  * @covers \Papaya\BaseObject\Callbacks::__isset
  */
  public function testMagicMethodIssetExpectingFalse() {
    $list = new \PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $this->assertFalse(isset($list->sample));
  }

  /**
  * @covers \Papaya\BaseObject\Callbacks::__get
  * @covers \Papaya\BaseObject\Callbacks::__set
  * @covers \Papaya\BaseObject\Callbacks::validateName
  */
  public function testGetAfterSetWithNull() {
    $list = new \PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $list->sample = 'substr';
    $this->assertSame('substr', $list->sample->callback);
    $list->sample = NULL;
    $this->assertNull($list->sample->callback);
    $this->assertEquals(23, $list->sample->defaultReturn);
  }

  /**
  * @covers \Papaya\BaseObject\Callbacks::__get
  * @covers \Papaya\BaseObject\Callbacks::__set
  * @covers \Papaya\BaseObject\Callbacks::validateName
  */
  public function testGetAfterSetWithCallback() {
    $list = new \PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $list->sample = 'substr';
    $this->assertEquals('substr', $list->sample->callback);
    $this->assertEquals(23, $list->sample->defaultReturn);
  }

  /**
  * @covers \Papaya\BaseObject\Callbacks::__get
  * @covers \Papaya\BaseObject\Callbacks::__set
  * @covers \Papaya\BaseObject\Callbacks::validateName
  */
  public function testGetAfterSetWithPapayaObjectCallbackObject() {
    $callback = $this->createMock(\Papaya\BaseObject\Callback::class);
    $list = new \PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $list->sample = $callback;
    $this->assertSame($callback, $list->sample);
  }

  /**
  * @covers \Papaya\BaseObject\Callbacks::__set
  * @covers \Papaya\BaseObject\Callbacks::validateName
  */
  public function testGetWithInvalidValueExpectingException() {
    $list = new \PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Argument $callback must be a callable or an instance of Papaya\BaseObject\Callback.');
    $list->sample = new \stdClass;
  }

  /**
  * @covers \Papaya\BaseObject\Callbacks::__get
  * @covers \Papaya\BaseObject\Callbacks::validateName
  */
  public function testGetWithInvalidNameExpectingException() {
    $list = new \PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Invalid callback name: UNKNOWN.');
    /** @noinspection PhpUndefinedFieldInspection */
    $list->UNKNOWN = NULL;
  }

  /**
  * @covers \Papaya\BaseObject\Callbacks::__unset
  */
  public function testUnsetCreatesNewPapayaObjectCallbackObject() {
    $list = new \PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $list->sample = 'substr';
    unset($list->sample);
    $this->assertNull($list->sample->callback);
  }

  /**
  * @covers \Papaya\BaseObject\Callbacks::__call
  */
  public function testCallExecutesCallback() {
    $list = new \PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $list->sample = function(
      /** @noinspection PhpUnusedParameterInspection */
      $context, $argument
    ) {
      return $argument;
    };
    $this->assertEquals(42, $list->sample(42));
  }

  /**
  * @covers \Papaya\BaseObject\Callbacks::__call
  */
  public function testCallWithInvalidNameExpectingException() {
    $list = new \PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Invalid callback name: UNKNOWN.');
    /** @noinspection PhpUndefinedMethodInspection */
    $list->UNKNOWN();
  }

  /**
  * @covers \Papaya\BaseObject\Callbacks::getIterator
  */
  public function testGetIterator() {
    $list = new \PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $this->assertEquals(
      array('sample' => new \Papaya\BaseObject\Callback(23)),
      iterator_to_array($list)
    );
  }

  /**
  * @covers \Papaya\BaseObject\Callbacks::getIterator
  */
  public function testGetIteratorAfterSet() {
    $callback = $this->createMock(\Papaya\BaseObject\Callback::class);
    $list = new \PapayaObjectCallbacks_TestProxy(array('sample' => 23));
    $list->sample = $callback;
    $this->assertSame(array('sample' => $callback), iterator_to_array($list));
  }
}

/**
 * @property \Papaya\BaseObject\Callback $sample
 * @method mixed sample($argument)
 */
class PapayaObjectCallbacks_TestProxy extends \Papaya\BaseObject\Callbacks {
  public function blocker() {
  }
}
