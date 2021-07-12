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

namespace Papaya\Session {

  require_once __DIR__.'/../../../bootstrap.php';

  class ShareTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers \Papaya\Session\Share::getSessionValues
     * @covers \Papaya\Session\Share::setSessionValues
     */
    public function testGetSessionValuesAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Values $values */
      $values = $this
        ->getMockBuilder(Values::class)
        ->setConstructorArgs(array($this->createMock(\Papaya\Session::class)))
        ->getMock();
      $share = new SessionShare_TestProxy();
      $share->setSessionValues($values);
      $this->assertSame($values, $share->getSessionValues());
    }

    /**
     * @covers \Papaya\Session\Share::getSessionValues
     */
    public function testGetSessionValuesFromApplicationRegistry() {
      $session = $this->createMock(\Papaya\Session::class);
      $values = $this
        ->getMockBuilder(Values::class)
        ->setConstructorArgs(array($session))
        ->getMock();
      $session
        ->expects($this->once())
        ->method('values')
        ->will($this->returnValue($values));
      $share = new SessionShare_TestProxy();
      $share->papaya(
        $this->mockPapaya()->application(array('session' => $session))
      );
      $this->assertSame($values, $share->getSessionValues());
    }

    /**
     * @covers \Papaya\Session\Share::__isset
     */
    public function testMagicMethodIssetExpectingTrue() {
      $share = new SessionShare_TestProxy();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Values $values */
      $values = $this
        ->getMockBuilder(Values::class)
        ->setConstructorArgs(array($this->createMock(\Papaya\Session::class)))
        ->getMock();
      $values
        ->expects($this->once())
        ->method('offsetExists')
        ->with(array(SessionShare_TestProxy::class, 'session_property'))
        ->will($this->returnValue(TRUE));
      $share->setSessionValues($values);
      $this->assertTrue(isset($share->sessionProperty));
    }

    /**
     * @covers \Papaya\Session\Share::__isset
     * @covers \Papaya\Session\Share::getGroupName
     */
    public function testMagicMethodIssetExpectingFalse() {
      $share = new SessionShare_TestProxy();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Values $values */
      $values = $this
        ->getMockBuilder(Values::class)
        ->setConstructorArgs(array($this->createMock(\Papaya\Session::class)))
        ->getMock();
      $values
        ->expects($this->once())
        ->method('offsetExists')
        ->with(array(SessionShare_TestProxy::class, 'session_property'))
        ->will($this->returnValue(FALSE));
      $share->setSessionValues($values);
      $this->assertFalse(isset($share->sessionProperty));
    }

    /**
     * @covers \Papaya\Session\Share::__get
     * @covers \Papaya\Session\Share::getGroupName
     */
    public function testMagicMethodGet() {
      $share = new SessionShare_TestProxy();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Values $values */
      $values = $this
        ->getMockBuilder(Values::class)
        ->setConstructorArgs(array($this->createMock(\Papaya\Session::class)))
        ->getMock();
      $values
        ->expects($this->once())
        ->method('offsetGet')
        ->with(array(SessionShare_TestProxy::class, 'session_property'))
        ->will($this->returnValue('success'));
      $share->setSessionValues($values);
      $this->assertEquals('success', $share->sessionProperty);
    }

    /**
     * @covers \Papaya\Session\Share::__set
     * @covers \Papaya\Session\Share::getGroupName
     */
    public function testMagicMethodSet() {
      $share = new SessionShare_TestProxy();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Values $values */
      $values = $this
        ->getMockBuilder(Values::class)
        ->setConstructorArgs(array($this->createMock(\Papaya\Session::class)))
        ->getMock();
      $values
        ->expects($this->once())
        ->method('offsetSet')
        ->with(array(SessionShare_TestProxy::class, 'session_property'), 'someValue');
      $share->setSessionValues($values);
      $share->sessionProperty = 'someValue';
    }

    /**
     * @covers \Papaya\Session\Share::__unset
     * @covers \Papaya\Session\Share::getGroupName
     */
    public function testMagicMethodUnset() {
      $share = new SessionShare_TestProxy();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Values $values */
      $values = $this
        ->getMockBuilder(Values::class)
        ->setConstructorArgs(array($this->createMock(\Papaya\Session::class)))
        ->getMock();
      $values
        ->expects($this->once())
        ->method('offsetUnset')
        ->with(array(SessionShare_TestProxy::class, 'session_property'));
      $share->setSessionValues($values);
      unset($share->sessionProperty);
    }

    /**
     * @covers \Papaya\Session\Share::__call
     */
    public function testMagicMethodCallTriggersSet() {
      $share = new SessionShare_TestProxy();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Values $values */
      $values = $this
        ->getMockBuilder(Values::class)
        ->setConstructorArgs(array($this->createMock(\Papaya\Session::class)))
        ->getMock();
      $values
        ->expects($this->once())
        ->method('offsetSet')
        ->with(array(SessionShare_TestProxy::class, 'session_property'), 'someValue');
      $share->setSessionValues($values);
      $share->setSessionProperty('someValue');
    }

    /**
     * @covers \Papaya\Session\Share::__call
     */
    public function testMagicMethodCallTriggersGet() {
      $share = new SessionShare_TestProxy();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Values $values */
      $values = $this
        ->getMockBuilder(Values::class)
        ->setConstructorArgs(array($this->createMock(\Papaya\Session::class)))
        ->getMock();
      $values
        ->expects($this->once())
        ->method('offsetGet')
        ->with(array(SessionShare_TestProxy::class, 'session_property'))
        ->will($this->returnValue('success'));
      $share->setSessionValues($values);
      $this->assertEquals('success', $share->getSessionProperty());
    }

    /**
     * @covers \Papaya\Session\Share::__call
     */
    public function testMagicMethodCallExpectingException() {
      $share = new SessionShare_TestProxy();
      $this->expectException(\LogicException::class);
      $this->expectExceptionMessage('LogicException: Unknown method "Papaya\Session\SessionShare_TestProxy::unknownMethodName".');
      /** @noinspection PhpUndefinedMethodInspection */
      $share->unknownMethodName();
    }

    /**
     * @covers \Papaya\Session\Share::preparePropertyName
     * @dataProvider providePropertyNames
     * @param string $propertyName
     */
    public function testPreparePropertyName($propertyName) {
      $share = new SessionShare_TestProxy();
      $this->assertEquals('session_property', $share->preparePropertyName($propertyName));
    }

    /**
     * @covers \Papaya\Session\Share::preparePropertyName
     */
    public function testPreparePropertyNameWithDisabledNormalization() {
      $share = new SessionShare_TestProxy();
      $share->_normalizeNames = FALSE;
      $this->expectException(\LogicException::class);
      $this->expectExceptionMessage('InvalidArgumentException: Invalid session share property name "SessionProperty".');
      $share->preparePropertyName('SessionProperty');
    }

    /**
     * @covers \Papaya\Session\Share::preparePropertyName
     */
    public function testPreparePropertyNameExpectingException() {
      $share = new SessionShare_TestProxy();
      $this->expectException(\LogicException::class);
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
  class SessionShare_TestProxy extends Share {

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
}
