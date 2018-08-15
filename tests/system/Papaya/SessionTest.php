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

require_once __DIR__.'/../../bootstrap.php';

class PapayaSessionTest extends \PapayaTestCase {

  private $_idSources = array();

  /**
  * @covers \Papaya\Session::setName
  * @covers \Papaya\Session::__Get
  */
  public function testSetName() {
    $session = new \Papaya\Session();
    $session->setName('sessionname');
    $this->assertEquals(
      'sessionname', $session->name
    );
  }

  /**
  * @covers \Papaya\Session::isActive
  */
  public function testIsActiveExpectingFalse() {
    $session = new \Papaya\Session();
    $this->assertFalse($session->isActive());
  }

  /**
  * @covers \Papaya\Session::isActive
  * @backupGlobals enabled
  * @runInSeparateProcess
  */
  public function testIsActiveAfterActivationExpectingTrue() {
    $_SERVER['HTTP_USER_AGENT'] =
      'Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2';
    $_SERVER['HTTPS'] = NULL;
    $session = new \Papaya\Session();
    $session->papaya($this->mockPapaya()->application());
    $session->wrapper($this->getSessionWrapperFixture());
    $session->id($this->getSessionIdFixture(array(\Papaya\Session\Id::SOURCE_QUERY)));
    $session->activate(FALSE);
    $this->assertTrue($session->isActive());
  }

  /**
  * @covers \Papaya\Session::__get
  */
  public function testPropertyActiveExpectingFalse() {
    $session = new \Papaya\Session();
    $this->assertFalse($session->active);
  }

  /**
  * @covers \Papaya\Session::values
  */
  public function testValuesSet() {
    $session = new \Papaya\Session();
    $values = $this
      ->getMockBuilder(\Papaya\Session\Values::class)
      ->setConstructorArgs(array($session))
      ->getMock();
    $session->values($values);
    $this->assertAttributeSame(
      $values, '_values', $session
    );
  }

  /**
  * @covers \Papaya\Session::values
  */
  public function testValuesGetAfterSet() {
    $session = new \Papaya\Session();
    $values = $this
      ->getMockBuilder(\Papaya\Session\Values::class)
      ->setConstructorArgs(array($session))
      ->getMock();
    $session->values($values);
    $this->assertSame(
      $values, $session->values()
    );
  }

  /**
  * @covers \Papaya\Session::values
  */
  public function testValuesGetUsingImplicitCreate() {
    $session = new \Papaya\Session();
    $this->assertInstanceOf(
      \Papaya\Session\Values::class, $session->values()
    );
  }

  /**
  * @covers \Papaya\Session::setValue
  */
  public function testSetValue() {
    $session = new \Papaya\Session();
    $values = $this
      ->getMockBuilder(\Papaya\Session\Values::class)
      ->setConstructorArgs(array($session))
      ->getMock();
    $values
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('foo'), $this->equalTo('bar'));
    $session->values($values);
    $session->setValue('foo', 'bar');
  }

  /**
  * @covers \Papaya\Session::getValue
  */
  public function testGetValue() {
    $session = new \Papaya\Session();
    $values = $this
      ->getMockBuilder(\Papaya\Session\Values::class)
      ->setConstructorArgs(array($session))
      ->getMock();
    $values
      ->expects($this->once())
      ->method('get')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue('bar'));
    $session->values($values);
    $this->assertEquals('bar', $session->getValue('foo'));
  }

  /**
  * @covers \Papaya\Session::options
  */
  public function testOptionsSet() {
    $session = new \Papaya\Session();
    $options = $this->createMock(\Papaya\Session\Options::class);
    $session->options($options);
    $this->assertAttributeSame(
      $options, '_options', $session
    );
  }

  /**
  * @covers \Papaya\Session::options
  */
  public function testOptionsGetAfterSet() {
    $session = new \Papaya\Session();
    $options = $this->createMock(\Papaya\Session\Options::class);
    $session->options($options);
    $this->assertSame(
      $options, $session->options()
    );
  }

  /**
  * @covers \Papaya\Session::options
  */
  public function testOptionsGetUsingImplicitCreate() {
    $session = new \Papaya\Session();
    $this->assertInstanceOf(
      \Papaya\Session\Options::class, $session->options()
    );
  }

  /**
  * @covers \Papaya\Session::id
  */
  public function testIdSet() {
    $session = new \Papaya\Session();
    $id = $this->createMock(\Papaya\Session\Id::class);
    $session->id($id);
    $this->assertAttributeSame(
      $id, '_id', $session
    );
  }

  /**
  * @covers \Papaya\Session::id
  */
  public function testIdGetAfterSet() {
    $session = new \Papaya\Session();
    $id = $this->createMock(\Papaya\Session\Id::class);
    $session->id($id);
    $this->assertSame(
      $id, $session->id()
    );
  }

  /**
  * @covers \Papaya\Session::id
  */
  public function testIdGetUsingImplicitCreate() {
    $session = new \Papaya\Session();
    $this->assertInstanceOf(
      \Papaya\Session\Id::class, $session->id()
    );
  }

  /**
  * @covers \Papaya\Session::__get
  */
  public function testPropertIdReturnsString() {
    $id = $this->createMock(\Papaya\Session\Id::class);
    $id
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('success'));
    $session = new \Papaya\Session();
    $session->id($id);
    $this->assertEquals(
      'success', $session->id
    );
  }

  /**
  * @covers \Papaya\Session::wrapper
  */
  public function testWrapperSet() {
    $session = new \Papaya\Session();
    $wrapper = $this->createMock(\Papaya\Session\Wrapper::class);
    $session->wrapper($wrapper);
    $this->assertAttributeSame(
      $wrapper, '_wrapper', $session
    );
  }

  /**
  * @covers \Papaya\Session::wrapper
  */
  public function testWrapperGetAfterSet() {
    $session = new \Papaya\Session();
    $wrapper = $this->createMock(\Papaya\Session\Wrapper::class);
    $session->wrapper($wrapper);
    $this->assertSame(
      $wrapper, $session->wrapper()
    );
  }

  /**
  * @covers \Papaya\Session::wrapper
  */
  public function testWrapperGetUsingImplicitCreate() {
    $session = new \Papaya\Session();
    $this->assertInstanceOf(
      \Papaya\Session\Wrapper::class, $session->wrapper()
    );
  }

  /**
  * @covers \Papaya\Session::__get
  */
  public function testValuesPropertyGet() {
    $session = new \Papaya\Session();
    $values = $this
      ->getMockBuilder(\Papaya\Session\Values::class)
      ->setConstructorArgs(array($session))
      ->getMock();
    $session->values($values);
    $this->assertSame(
      $values, $session->values
    );
  }

  /**
  * @covers \Papaya\Session::__get
  */
  public function testOptionsPropertyGet() {
    $session = new \Papaya\Session();
    $options = $this->createMock(\Papaya\Session\Options::class);
    $session->options($options);
    $this->assertSame(
      $options, $session->options
    );
  }

  /**
  * @covers \Papaya\Session::__get
  */
  public function testPropertyGetExpectingException() {
    $session = new \Papaya\Session();
    $this->expectException(UnexpectedValueException::class);
    /** @noinspection PhpUndefinedFieldInspection */
    $session->INVALID_PROPERTY_NAME;
  }

  /**
  * @covers \Papaya\Session::__set
  */
  public function testPropertySetExpectingException() {
    $session = new \Papaya\Session();
    $this->expectException(\LogicException::class);
    /** @noinspection Annotator */
    $session->values = 'foo';
  }

  /**
  * @backupGlobals
  * @covers \Papaya\Session::isAllowed
  */
  public function testIsAllowedExpectingTrue() {
    $_SERVER['HTTP_USER_AGENT'] =
      'Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2';
    $_SERVER['HTTPS'] = NULL;
    $session = new \Papaya\Session();
    $session->papaya($this->mockPapaya()->application());
    $this->assertTrue($session->isAllowed());
  }

  /**
  * @covers \Papaya\Session::isAllowed
  */
  public function testIsAllowedExpectingFalse() {
    $_SERVER['HTTP_USER_AGENT'] = 'Googlebot/2.1 (+http://www.google.com/bot.html)';
    $_SERVER['HTTPS'] = NULL;
    $session = new \Papaya\Session();
    $session->papaya($this->mockPapaya()->application());
    $this->assertFalse($session->isAllowed());
  }

  /**
  * @covers \Papaya\Session::isProtocolAllowed
  * @backupGlobals
  */
  public function testIsProtocolAllowedExpectingTrue() {
    $session = new \Papaya\Session();
    $session->papaya($this->mockPapaya()->application());
    $_SERVER['HTTPS'] = NULL;
    $this->assertTrue($session->isProtocolAllowed());
  }

  /**
  * @covers \Papaya\Session::isProtocolAllowed
  * @backupGlobals
  */
  public function testIsProtocolAllowedWithSecureSessionExpectingTrue() {
    $session = new \Papaya\Session();
    $session->papaya(
      $this->mockPapaya()->application(
        array(
          'options' => $this->mockPapaya()->options(
            array(
              'PAPAYA_SESSION_SECURE' => TRUE
            )
          )
        )
      )
    );
    $_SERVER['HTTPS'] = 'on';
    $this->assertTrue($session->isProtocolAllowed());
  }

  /**
  * @covers \Papaya\Session::isProtocolAllowed
  * @backupGlobals
  */
  public function testIsProtocolAllowedWithSecureSessionExpectingFalse() {
    $session = new \Papaya\Session();
    $session->papaya(
      $this->mockPapaya()->application(
        array(
          'options' => $this->mockPapaya()->options(
            array(
              'PAPAYA_SESSION_SECURE' => TRUE
            )
          )
        )
      )
    );
    $_SERVER['HTTPS'] = NULL;
    $this->assertFalse($session->isProtocolAllowed());
  }

  /**
   * @covers \Papaya\Session::isSecureOnly
   * @dataProvider provideValidOptionsForSecureSession
   * @param array $options
   */
  public function testIsSecureOnlyExpectingTrue(array $options) {
    $session = new \Papaya\Session();
    $session->papaya(
      $this->mockPapaya()->application(
        array(
          'options' => $this->mockPapaya()->options($options)
        )
      )
    );
    $this->assertTrue($session->isSecureOnly());
  }

  /**
   * @covers \Papaya\Session::isSecureOnly
   * @dataProvider provideInvalidOptionsForSecureSession
   * @param array $options
   */
  public function testIsSecureOnlyExpectingFalse(array $options) {
    $session = new \Papaya\Session();
    $session->papaya(
      $this->mockPapaya()->application(
        array(
          'options' => $this->mockPapaya()->options($options)
        )
      )
    );
    $this->assertFalse($session->isSecureOnly());
  }

  /**
   * @covers \Papaya\Session::activate
   * @covers \Papaya\Session::configure
   * @covers \Papaya\Session::redirectIfNeeded
   * @backupGlobals enabled
   * @runInSeparateProcess
   * @dataProvider provideSessionSourcesNoRedirect
   * @param array $sources
   * @param int $fallback
   */
  public function testActivateExpectingSuccess(array $sources, $fallback) {
    $_SERVER['HTTP_USER_AGENT'] =
      'Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2';
    $_SERVER['HTTPS'] = NULL;
    $session = new \Papaya\Session();
    $session->papaya($this->mockPapaya()->application());
    $session->wrapper($this->getSessionWrapperFixture());
    $session->id($this->getSessionIdFixture($sources));
    $session->options()->fallback = $fallback;
    $this->assertNull(
      $session->activate(TRUE)
    );
  }

  /**
  * @covers \Papaya\Session::activate
  * @covers \Papaya\Session::configure
  * @covers \Papaya\Session::redirectIfNeeded
  * @backupGlobals enabled
  */
  public function testActivateWithRobotExpectingNull() {
    $_SERVER['HTTP_USER_AGENT'] = 'Googlebot';
    $_SERVER['HTTPS'] = NULL;
    $session = new \Papaya\Session();
    $session->papaya($this->mockPapaya()->application());
    $this->assertNull($session->activate(FALSE));
  }

  /**
   * @covers \Papaya\Session::activate
   * @covers \Papaya\Session::configure
   * @covers \Papaya\Session::_createRedirect
   * @covers \Papaya\Session::redirectIfNeeded
   * @backupGlobals enabled
   * @runInSeparateProcess
   * @dataProvider provideSessionSourcesTriggeringRedirect
   * @param array $sources
   * @param int $fallback
   * @param int $transport
   */
  public function testActivateExpectingRedirect($sources, $fallback, $transport) {
    $_SERVER['HTTP_USER_AGENT'] =
      'Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2';
    $_SERVER['HTTPS'] = NULL;
    $session = new \Papaya\Session();
    $session->papaya($this->mockPapaya()->application());
    $session->wrapper($this->getSessionWrapperFixture());
    $session->id($this->getSessionIdFixture($sources));
    $session->options()->fallback = $fallback;
    $redirect = $session->activate(TRUE);
    $this->assertInstanceOf(
      \Papaya\Session\Redirect::class, $redirect
    );
    $this->assertAttributeSame(
      $transport, '_transport', $redirect
    );
  }

  /**
   * @covers \Papaya\Session::activate
   * @covers \Papaya\Session::configure
   * @covers \Papaya\Session::redirectIfNeeded
   * @covers \Papaya\Session::_createRedirect
   * @backupGlobals enabled
   * @runInSeparateProcess
   */
  public function testActivateWithoutIdExpectingRedirect() {
    $_SERVER['HTTP_USER_AGENT'] =
      'Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2';
    $_SERVER['HTTPS'] = NULL;
    $wrapper = $this->createMock(\Papaya\Session\Wrapper::class);
    $wrapper
      ->expects($this->once())
      ->method('setName')
      ->with($this->equalTo('sid'));
    $wrapper
      ->expects($this->once())
      ->method('start')
      ->will($this->returnValue(TRUE));
    $session = new \Papaya\Session();
    $session->papaya($this->mockPapaya()->application());
    $session->wrapper($wrapper);
    $session->id($this->getSessionIdFixture(array()));
    $session->options()->fallback = \Papaya\Session\Options::FALLBACK_REWRITE;
    $redirect = $session->activate(TRUE);
    $this->assertInstanceOf(
      \Papaya\Session\Redirect::class, $redirect
    );
    $this->assertAttributeSame(
      \Papaya\Session\Id::SOURCE_PATH, '_transport', $redirect
    );
  }

  /**
  * @covers \Papaya\Session::close
  * @backupGlobals enabled
   * @runInSeparateProcess
   */
  public function testClose() {
    $session = $this->getActiveSessionFixture(\Papaya\Session\Id::SOURCE_COOKIE);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Session\Wrapper $wrapper */
    $wrapper = $session->wrapper();
    $wrapper
      ->expects($this->once())
      ->method('writeClose');
    $session->close();
    $this->assertAttributeEquals(
      FALSE, '_active', $session
    );
  }

  /**
  * @covers \Papaya\Session::reset
  * @backupGlobals enabled
   * @runInSeparateProcess
   */
  public function testReset() {
    $_SESSION = array('foo' => 'bar');
    $session = $this->getActiveSessionFixture(\Papaya\Session\Id::SOURCE_COOKIE);
    $session->reset();
    $this->assertEquals(
      array(), $_SESSION
    );
  }

  /**
  * @covers \Papaya\Session::destroy
  * @backupGlobals enabled
   * @runInSeparateProcess
   */
  public function testDestroy() {
    $session = $this->getActiveSessionFixture(\Papaya\Session\Id::SOURCE_COOKIE);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Session\Wrapper $wrapper */
    $wrapper = $session->wrapper();
    $wrapper
      ->expects($this->once())
      ->method('destroy');
    $session->destroy();
    $this->assertAttributeEquals(
      FALSE, '_active', $session
    );
  }

  /**
  * @covers \Papaya\Session::regenerateId
  * @backupGlobals enabled
   * @runInSeparateProcess
   */
  public function testRegenerateId() {
    $session = $this->getActiveSessionFixture(\Papaya\Session\Id::SOURCE_COOKIE);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Session\Wrapper $wrapper */
    $wrapper = $session->wrapper();
    $wrapper
      ->expects($this->once())
      ->method('regenerateId');
    $this->assertFalse($session->regenerateId());
    $this->assertAttributeEquals(
      TRUE, '_active', $session
    );
  }

  /**
  * @covers \Papaya\Session::regenerateId
  * @backupGlobals enabled
   * @runInSeparateProcess
   */
  public function testRegenerateIdExpectingPathRedirect() {
    $session = $this->getActiveSessionFixture(\Papaya\Session\Id::SOURCE_PATH);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Session\Wrapper $wrapper */
    $wrapper = $session->wrapper();
    $wrapper
      ->expects($this->once())
      ->method('regenerateId');
    $redirect = $session->regenerateId();
    $this->assertInstanceOf(
      \Papaya\Session\Redirect::class, $redirect
    );
  }

  /**
  * @covers \Papaya\Session::regenerateId
  * @backupGlobals enabled
   * @runInSeparateProcess
   */
  public function testRegenerateIdExpectingPathRedirectToTargetUrl() {
    $session = $this->getActiveSessionFixture(\Papaya\Session\Id::SOURCE_PATH);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Session\Wrapper $wrapper */
    $wrapper = $session->wrapper();
    $wrapper
      ->expects($this->once())
      ->method('regenerateId');
    $redirect = $session->regenerateId('http://www.sample.tld/foo?bar=123');
    $this->assertInstanceOf(
      \Papaya\Session\Redirect::class, $redirect
    );
    $this->assertEquals(
      'http://www.sample.tld/foo?bar=123', $redirect->url()->getURL()
    );
  }

  /**************************
  * Fixtures
  **************************/

  /**
   * @param bool $canStart
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\Session\Wrapper
   */
  public function getSessionWrapperFixture($canStart = TRUE) {
    $wrapper = $this->createMock(\Papaya\Session\Wrapper::class);
    $wrapper
      ->expects($this->once())
      ->method('setName')
      ->with($this->equalTo('sid'));
    $wrapper
      ->expects($this->once())
      ->method('setId')
      ->with($this->equalTo('ab123456789012345678901234567890'));
    $wrapper
      ->expects($this->once())
      ->method('start')
      ->will($this->returnValue($canStart));
    return $wrapper;
  }

  public function getSessionIdFixture($source) {
    $this->_idSources = $source;
    $id = $this->createMock(\Papaya\Session\Id::class);
    $id
      ->expects($this->any())
      ->method('__toString')
      ->will($this->returnValue('ab123456789012345678901234567890'));
    $id
      ->expects($this->any())
      ->method('existsIn')
      ->withAnyParameters()
      ->will($this->returnCallback(array($this, 'callbackSessionIdExistsIn')));
    return $id;
  }

  public function callbackSessionIdExistsIn($source) {
    if ($source === \Papaya\Session\Id::SOURCE_ANY && count($this->_idSources) > 0) {
      return TRUE;
    }
    foreach ($this->_idSources as $idSource) {
      if ($source & $idSource) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * @param int $source
   * @return \Papaya\Session
   */
  public function getActiveSessionFixture($source) {
    $_SERVER['HTTP_USER_AGENT'] =
      'Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2';
    $_SERVER['HTTPS'] = NULL;
    $session = new \Papaya\Session();
    $session->papaya($this->mockPapaya()->application());
    $session->wrapper($this->getSessionWrapperFixture());
    $session->id($this->getSessionIdFixture(array($source)));
    $session->options()->fallback = \Papaya\Session\Options::FALLBACK_REWRITE;
    $session->activate(FALSE);
    return $session;
  }

  /**************************
  * Data Provider
  **************************/

  public static function provideValidOptionsForSecureSession() {
    return array(
      'secure session' => array(
        array(
          'PAPAYA_SESSION_SECURE' => TRUE
        )
      ),
      'secure administration' => array(
        array(
          'PAPAYA_ADMIN_PAGE' => TRUE,
          'PAPAYA_UI_SECURE' => TRUE
        )
      )
    );
  }

  public static function provideInvalidOptionsForSecureSession() {
    return array(
      'default' => array(array()),
      'admin only' => array(
        array(
          'PAPAYA_ADMIN_PAGE' => FALSE,
          'PAPAYA_UI_SECURE' => TRUE
        )
      )
    );
  }

  public static function provideSessionSourcesNoRedirect() {
    return array(
      'cookie only' => array(
        array(\Papaya\Session\Id::SOURCE_COOKIE),
        \Papaya\Session\Options::FALLBACK_REWRITE
      ),
      'path only' => array(
        array(\Papaya\Session\Id::SOURCE_PATH),
        \Papaya\Session\Options::FALLBACK_REWRITE
      ),
      'query only' => array(
        array(\Papaya\Session\Id::SOURCE_QUERY),
        \Papaya\Session\Options::FALLBACK_PARAMETER
      )
    );
  }

  public static function provideSessionSourcesTriggeringRedirect() {
    return array(
      'cookie and path' => array(
        array(\Papaya\Session\Id::SOURCE_COOKIE, \Papaya\Session\Id::SOURCE_PATH),
        \Papaya\Session\Options::FALLBACK_REWRITE,
        0,
      ),
      'path and query' => array(
        array(\Papaya\Session\Id::SOURCE_PATH, \Papaya\Session\Id::SOURCE_QUERY),
        \Papaya\Session\Options::FALLBACK_REWRITE,
        \Papaya\Session\Id::SOURCE_PATH
      ),
      'query and path' => array(
        array(\Papaya\Session\Id::SOURCE_PATH, \Papaya\Session\Id::SOURCE_QUERY),
        \Papaya\Session\Options::FALLBACK_PARAMETER,
        \Papaya\Session\Id::SOURCE_QUERY
      )
    );
  }
}
