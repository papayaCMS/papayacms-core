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

class PapayaSessionShareTest extends \PapayaTestCase {

  /**
  * @covers \PapayaSessionShare::getSessionValues
  * @covers \PapayaSessionShare::setSessionValues
  */
  public function testGetSessionValuesAfterSet() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaSessionValues $values */
    $values = $this
      ->getMockBuilder(\PapayaSessionValues::class)
      ->setConstructorArgs(array($this->createMock(\Papaya\Session::class)))
      ->getMock();
    $share = new \PapayaSessionShare_TestProxy();
    $share->setSessionValues($values);
    $this->assertSame($values, $share->getSessionValues());
  }

  /**
  * @covers \PapayaSessionShare::getSessionValues
  */
  public function testGetSessionValuesFromApplicationRegistry() {
    $session = $this->createMock(\Papaya\Session::class);
    $values = $this
      ->getMockBuilder(\PapayaSessionValues::class)
      ->setConstructorArgs(array($session))
      ->getMock();
    $session
      ->expects($this->once())
      ->method('values')
      ->will($this->returnValue($values));
    $share = new \PapayaSessionShare_TestProxy();
    $share->papaya(
      $this->mockPapaya()->application(array('session' => $session))
    );
    $this->assertSame($values, $share->getSessionValues());
  }

  /**
  * @covers \PapayaSessionShare::__isset
  */
  public function testMagicMethodIssetExpectingTrue() {
    $share = new \PapayaSessionShare_TestProxy();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaSessionValues $values */
    $values = $this
      ->getMockBuilder(\PapayaSessionValues::class)
      ->setConstructorArgs(array($this->createMock(\Papaya\Session::class)))
      ->getMock();
    $values
      ->expects($this->once())
      ->method('offsetExists')
      ->with(array(\PapayaSessionShare_TestProxy::class, 'session_property'))
      ->will($this->returnValue(TRUE));
    $share->setSessionValues($values);
    $this->assertTrue(isset($share->sessionProperty));
  }

  /**
   * @covers \PapayaSessionShare::__isset
   * @covers \PapayaSessionShare::getGroupName
   */
  public function testMagicMethodIssetExpectingFalse() {
    $share = new \PapayaSessionShare_TestProxy();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaSessionValues $values */
    $values = $this
      ->getMockBuilder(\PapayaSessionValues::class)
      ->setConstructorArgs(array($this->createMock(\Papaya\Session::class)))
      ->getMock();
    $values
      ->expects($this->once())
      ->method('offsetExists')
      ->with(array(\PapayaSessionShare_TestProxy::class, 'session_property'))
      ->will($this->returnValue(FALSE));
    $share->setSessionValues($values);
    $this->assertFalse(isset($share->sessionProperty));
  }

  /**
   * @covers \PapayaSessionShare::__get
   * @covers \PapayaSessionShare::getGroupName
   */
  public function testMagicMethodGet() {
    $share = new \PapayaSessionShare_TestProxy();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaSessionValues $values */
    $values = $this
      ->getMockBuilder(\PapayaSessionValues::class)
      ->setConstructorArgs(array($this->createMock(\Papaya\Session::class)))
      ->getMock();
    $values
      ->expects($this->once())
      ->method('offsetGet')
      ->with(array(\PapayaSessionShare_TestProxy::class, 'session_property'))
      ->will($this->returnValue('success'));
    $share->setSessionValues($values);
    $this->assertEquals('success', $share->sessionProperty);
  }

  /**
   * @covers \PapayaSessionShare::__set
   * @covers \PapayaSessionShare::getGroupName
   */
  public function testMagicMethodSet() {
    $share = new \PapayaSessionShare_TestProxy();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaSessionValues $values */
    $values = $this
      ->getMockBuilder(\PapayaSessionValues::class)
      ->setConstructorArgs(array($this->createMock(\Papaya\Session::class)))
      ->getMock();
    $values
      ->expects($this->once())
      ->method('offsetSet')
      ->with(array(\PapayaSessionShare_TestProxy::class, 'session_property'), 'someValue');
    $share->setSessionValues($values);
    $share->sessionProperty = 'someValue';
  }

  /**
   * @covers \PapayaSessionShare::__unset
   * @covers \PapayaSessionShare::getGroupName
   */
  public function testMagicMethodUnset() {
    $share = new \PapayaSessionShare_TestProxy();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaSessionValues $values */
    $values = $this
      ->getMockBuilder(\PapayaSessionValues::class)
      ->setConstructorArgs(array($this->createMock(\Papaya\Session::class)))
      ->getMock();
    $values
      ->expects($this->once())
      ->method('offsetUnset')
      ->with(array(\PapayaSessionShare_TestProxy::class, 'session_property'));
    $share->setSessionValues($values);
    unset($share->sessionProperty);
  }

  /**
  * @covers \PapayaSessionShare::__call
  */
  public function testMagicMethodCallTriggersSet() {
    $share = new \PapayaSessionShare_TestProxy();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaSessionValues $values */
    $values = $this
      ->getMockBuilder(\PapayaSessionValues::class)
      ->setConstructorArgs(array($this->createMock(\Papaya\Session::class)))
      ->getMock();
    $values
      ->expects($this->once())
      ->method('offsetSet')
      ->with(array(\PapayaSessionShare_TestProxy::class, 'session_property'), 'someValue');
    $share->setSessionValues($values);
    $share->setSessionProperty('someValue');
  }

  /**
  * @covers \PapayaSessionShare::__call
  */
  public function testMagicMethodCallTriggersGet() {
    $share = new \PapayaSessionShare_TestProxy();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaSessionValues $values */
    $values = $this
      ->getMockBuilder(\PapayaSessionValues::class)
      ->setConstructorArgs(array($this->createMock(\Papaya\Session::class)))
      ->getMock();
    $values
      ->expects($this->once())
      ->method('offsetGet')
      ->with(array(\PapayaSessionShare_TestProxy::class, 'session_property'))
      ->will($this->returnValue('success'));
    $share->setSessionValues($values);
    $this->assertEquals('success', $share->getSessionProperty());
  }

  /**
  * @covers \PapayaSessionShare::__call
  */
  public function testMagicMethodCallExpectingException() {
    $share = new \PapayaSessionShare_TestProxy();
    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('LogicException: Unknown method "PapayaSessionShare_TestProxy::unknownMethodName".');
    /** @noinspection PhpUndefinedMethodInspection */
    $share->unknownMethodName();
  }

  /**
   * @covers \PapayaSessionShare::preparePropertyName
   * @dataProvider providePropertyNames
   * @param string $propertyName
   */
  public function testPreparePropertyName($propertyName) {
    $share = new \PapayaSessionShare_TestProxy();
    $this->assertEquals('session_property', $share->preparePropertyName($propertyName));
  }

  /**
  * @covers \PapayaSessionShare::preparePropertyName
  */
  public function testPreparePropertyNameWithDisabledNormalization() {
    $share = new \PapayaSessionShare_TestProxy();
    $share->_normalizeNames = FALSE;
    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('InvalidArgumentException: Invalid session share property name "SessionProperty".');
    $share->preparePropertyName('SessionProperty');
  }

  /**
  * @covers \PapayaSessionShare::preparePropertyName
  */
  public function testPreparePropertyNameExpectingException() {
    $share = new \PapayaSessionShare_TestProxy();
    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('InvalidArgumentException: Invalid session share property name "invalid_property".');
    $share->preparePropertyName('invalidProperty');
  }

  public static function providePropertyNames() {
    return array(
      'underscores' => array('session_property'),
      'camel case' => array('sessionProperty'),
      'camel case starting upper' => array('SessionProperty')
    );
  }
}


/**
 * @property mixed $sessionProperty
 * @method mixed getSessionProperty()
 * @method void setSessionProperty($value)
 */
class PapayaSessionShare_TestProxy extends \PapayaSessionShare {

  public
    /** @noinspection PropertyInitializationFlawsInspection */
    $_normalizeNames = TRUE;

  protected $_definitions = array(
    'session_property' => TRUE
  );

  public function preparePropertyName($name) {
    return parent::preparePropertyName($name);
  }
}
