<?php
require_once __DIR__.'/../../bootstrap.php';

class PapayaSessionTest extends PapayaTestCase {

  /**
  * @covers PapayaSession::setName
  * @covers PapayaSession::__Get
  */
  public function testSetName() {
    $session = new PapayaSession();
    $session->setName('sessionname');
    $this->assertEquals(
      'sessionname', $session->name
    );
  }

  /**
  * @covers PapayaSession::isActive
  */
  public function testIsActiveExpectingFalse() {
    $session = new PapayaSession();
    $this->assertFalse($session->isActive());
  }

  /**
  * @covers PapayaSession::isActive
  * @backupGlobals enabled
  * @runInSeparateProcess
  */
  public function testIsActiveAfterActivationExpectingTrue() {
    $session = new PapayaSession();
    $_SERVER['HTTP_USER_AGENT'] =
      'Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2';
    $_SERVER['HTTPS'] = NULL;
    $session = new PapayaSession();
    $session->papaya($this->mockPapaya()->application());
    $session->wrapper($this->getSessionWrapperFixture());
    $session->id($this->getSessionIdFixture(array(PapayaSessionId::SOURCE_QUERY)));
    $session->activate(FALSE);
    $this->assertTrue($session->isActive());
  }

  /**
  * @covers PapayaSession::__get
  */
  public function testPropertyActiveExpectingFalse() {
    $session = new PapayaSession();
    $this->assertFalse($session->active);
  }

  /**
  * @covers PapayaSession::values
  */
  public function testValuesSet() {
    $session = new PapayaSession();
    $values = $this->getMock(PapayaSessionValues::class, array(), array($session));
    $session->values($values);
    $this->assertAttributeSame(
      $values, '_values', $session
    );
  }

  /**
  * @covers PapayaSession::values
  */
  public function testValuesGetAfterSet() {
    $session = new PapayaSession();
    $values = $this->getMock(PapayaSessionValues::class, array(), array($session));
    $session->values($values);
    $this->assertSame(
      $values, $session->values()
    );
  }

  /**
  * @covers PapayaSession::values
  */
  public function testValuesGetUsingImplicitCreate() {
    $session = new PapayaSession();
    $this->assertInstanceOf(
      PapayaSessionValues::class, $session->values()
    );
  }

  /**
  * @covers PapayaSession::setValue
  */
  public function testSetValue() {
    $session = new PapayaSession();
    $values = $this->getMock(PapayaSessionValues::class, array('set'), array($session));
    $values
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('foo'), $this->equalTo('bar'));
    $session->values($values);
    $session->setValue('foo', 'bar');
  }

  /**
  * @covers PapayaSession::getValue
  */
  public function testGetValue() {
    $session = new PapayaSession();
    $values = $this->getMock(PapayaSessionValues::class, array('get'), array($session));
    $values
      ->expects($this->once())
      ->method('get')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue('bar'));
    $session->values($values);
    $this->assertEquals('bar', $session->getValue('foo'));
  }

  /**
  * @covers PapayaSession::options
  */
  public function testOptionsSet() {
    $session = new PapayaSession();
    $options = $this->getMock(PapayaSessionOptions::class, array());
    $session->options($options);
    $this->assertAttributeSame(
      $options, '_options', $session
    );
  }

  /**
  * @covers PapayaSession::options
  */
  public function testOptionsGetAfterSet() {
    $session = new PapayaSession();
    $options = $this->getMock(PapayaSessionOptions::class, array());
    $session->options($options);
    $this->assertSame(
      $options, $session->options()
    );
  }

  /**
  * @covers PapayaSession::options
  */
  public function testOptionsGetUsingImplicitCreate() {
    $session = new PapayaSession();
    $this->assertInstanceOf(
      PapayaSessionOptions::class, $session->options()
    );
  }

  /**
  * @covers PapayaSession::id
  */
  public function testIdSet() {
    $session = new PapayaSession();
    $id = $this->getMock(PapayaSessionId::class, array());
    $session->id($id);
    $this->assertAttributeSame(
      $id, '_id', $session
    );
  }

  /**
  * @covers PapayaSession::id
  */
  public function testIdGetAfterSet() {
    $session = new PapayaSession();
    $id = $this->getMock(PapayaSessionId::class, array());
    $session->id($id);
    $this->assertSame(
      $id, $session->id()
    );
  }

  /**
  * @covers PapayaSession::id
  */
  public function testIdGetUsingImplicitCreate() {
    $session = new PapayaSession();
    $this->assertInstanceOf(
      PapayaSessionId::class, $session->id()
    );
  }

  /**
  * @covers PapayaSession::__get
  */
  public function testPropertIdReturnsString() {
    $id = $this->getMock(PapayaSessionId::class, array('__toString'));
    $id
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('success'));
    $session = new PapayaSession();
    $session->id($id);
    $this->assertEquals(
      'success', $session->id
    );
  }

  /**
  * @covers PapayaSession::wrapper
  */
  public function testWrapperSet() {
    $session = new PapayaSession();
    $wrapper = $this->getMock(PapayaSessionWrapper::class, array());
    $session->wrapper($wrapper);
    $this->assertAttributeSame(
      $wrapper, '_wrapper', $session
    );
  }

  /**
  * @covers PapayaSession::wrapper
  */
  public function testWrapperGetAfterSet() {
    $session = new PapayaSession();
    $wrapper = $this->getMock(PapayaSessionWrapper::class, array());
    $session->wrapper($wrapper);
    $this->assertSame(
      $wrapper, $session->wrapper()
    );
  }

  /**
  * @covers PapayaSession::wrapper
  */
  public function testWrapperGetUsingImplicitCreate() {
    $session = new PapayaSession();
    $this->assertInstanceOf(
      PapayaSessionWrapper::class, $session->wrapper()
    );
  }

  /**
  * @covers PapayaSession::__get
  */
  public function testValuesPropertyGet() {
    $session = new PapayaSession();
    $values = $this->getMock(PapayaSessionValues::class, array(), array($session));
    $session->values($values);
    $this->assertSame(
      $values, $session->values
    );
  }

  /**
  * @covers PapayaSession::__get
  */
  public function testOptionsPropertyGet() {
    $session = new PapayaSession();
    $options = $this->getMock(PapayaSessionOptions::class, array());
    $session->options($options);
    $this->assertSame(
      $options, $session->options
    );
  }

  /**
  * @covers PapayaSession::__get
  */
  public function testPropertyGetExpectingException() {
    $session = new PapayaSession();
    $this->setExpectedException(UnexpectedValueException::class);
    $dummy = $session->INVALID_PROPERTY_NAME;
  }

  /**
  * @covers PapayaSession::__set
  */
  public function testPropertySetExpectingException() {
    $session = new PapayaSession();
    $this->setExpectedException(LogicException::class);
    $session->values = 'foo';
  }

  /**
  * @backupGlobals
  * @covers PapayaSession::isAllowed
  */
  public function testIsAllowedExpectingTrue() {
    $_SERVER['HTTP_USER_AGENT'] =
      'Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2';
    $_SERVER['HTTPS'] = NULL;
    $session = new PapayaSession();
    $session->papaya($this->mockPapaya()->application());
    $this->assertTrue($session->isAllowed());
  }

  /**
  * @covers PapayaSession::isAllowed
  */
  public function testIsAllowedExpectingFalse() {
    $_SERVER['HTTP_USER_AGENT'] = 'Googlebot/2.1 (+http://www.google.com/bot.html)';
    $_SERVER['HTTPS'] = NULL;
    $session = new PapayaSession();
    $session->papaya($this->mockPapaya()->application());
    $this->assertFalse($session->isAllowed());
  }

  /**
  * @covers PapayaSession::isProtocolAllowed
  * @backupGlobals
  */
  public function testIsProtocolAllowedExpectingTrue() {
    $session = new PapayaSession();
    $session->papaya($this->mockPapaya()->application());
    $_SERVER['HTTPS'] = NULL;
    $this->assertTrue($session->isProtocolAllowed());
  }

  /**
  * @covers PapayaSession::isProtocolAllowed
  * @backupGlobals
  */
  public function testIsProtocolAllowedWithSecureSessionExpectingTrue() {
    $session = new PapayaSession();
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
  * @covers PapayaSession::isProtocolAllowed
  * @backupGlobals
  */
  public function testIsProtocolAllowedWithSecureSessionExpectingFalse() {
    $session = new PapayaSession();
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
  * @covers PapayaSession::isSecureOnly
  * @dataProvider provideValidOptionsForSecureSession
  */
  public function testIsSecureOnlyExpectingTrue($options) {
    $session = new PapayaSession();
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
  * @covers PapayaSession::isSecureOnly
  * @dataProvider provideInvalidOptionsForSecureSession
  */
  public function testIsSecureOnlyExpectingFalse($options) {
    $session = new PapayaSession();
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
  * @covers PapayaSession::activate
  * @covers PapayaSession::configure
  * @covers PapayaSession::redirectIfNeeded
  * @backupGlobals enabled
   * @runInSeparateProcess
   * @dataProvider provideSessionSourcesNoRedirect
  */
  public function testActivateExpectingSuccess($sources, $fallback) {
    $_SERVER['HTTP_USER_AGENT'] =
      'Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2';
    $_SERVER['HTTPS'] = NULL;
    $session = new PapayaSession();
    $session->papaya($this->mockPapaya()->application());
    $session->wrapper($this->getSessionWrapperFixture());
    $session->id($this->getSessionIdFixture($sources));
    $session->options()->fallback = $fallback;
    $this->assertNull(
      $session->activate(TRUE)
    );
  }

  /**
  * @covers PapayaSession::activate
  * @covers PapayaSession::configure
  * @covers PapayaSession::redirectIfNeeded
  * @backupGlobals enabled
  */
  public function testActivateWithRobotExpectingNull() {
    $_SERVER['HTTP_USER_AGENT'] = 'Googlebot';
    $_SERVER['HTTPS'] = NULL;
    $session = new PapayaSession();
    $session->papaya($this->mockPapaya()->application());
    $this->assertNull($session->activate(FALSE));
  }

  /**
  * @covers PapayaSession::activate
  * @covers PapayaSession::configure
  * @covers PapayaSession::_createRedirect
  * @covers PapayaSession::redirectIfNeeded
  * @backupGlobals enabled
   * @runInSeparateProcess
   * @dataProvider provideSessionSourcesTriggeringRedirect
  */
  public function testActivateExpectingRedirect($sources, $fallback, $transport) {
    $_SERVER['HTTP_USER_AGENT'] =
      'Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2';
    $_SERVER['HTTPS'] = NULL;
    $session = new PapayaSession();
    $session->papaya($this->mockPapaya()->application());
    $session->wrapper($this->getSessionWrapperFixture());
    $session->id($this->getSessionIdFixture($sources));
    $session->options()->fallback = $fallback;
    $redirect = $session->activate(TRUE);
    $this->assertInstanceOf(
      PapayaSessionRedirect::class, $redirect
    );
    $this->assertAttributeSame(
      $transport, '_transport', $redirect
    );
  }

  /**
  * @covers PapayaSession::activate
  * @covers PapayaSession::configure
  * @covers PapayaSession::redirectIfNeeded
  * @covers PapayaSession::_createRedirect
  * @backupGlobals enabled
   * @runInSeparateProcess
   */
  public function testActivateWithoutIdExpectingRedirect() {
    $_SERVER['HTTP_USER_AGENT'] =
      'Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2';
    $_SERVER['HTTPS'] = NULL;
    $wrapper = $this->getMock(PapayaSessionWrapper::class, array('setName', 'setId', 'start'));
    $wrapper
      ->expects($this->once())
      ->method('setName')
      ->with($this->equalTo('sid'));
    $wrapper
      ->expects($this->once())
      ->method('start')
      ->will($this->returnValue(TRUE));
    $session = new PapayaSession();
    $session->papaya($this->mockPapaya()->application());
    $session->wrapper($wrapper);
    $session->id($this->getSessionIdFixture(array()));
    $session->options()->fallback = PapayaSessionOptions::FALLBACK_REWRITE;
    $redirect = $session->activate(TRUE);
    $this->assertInstanceOf(
      PapayaSessionRedirect::class, $redirect
    );
    $this->assertAttributeSame(
      PapayaSessionId::SOURCE_PATH, '_transport', $redirect
    );
  }

  /**
  * @covers PapayaSession::close
  * @backupGlobals enabled
   * @runInSeparateProcess
   */
  public function testClose() {
    $session = $this->getActiveSessionMockFixture(PapayaSessionId::SOURCE_COOKIE);
    $session
      ->wrapper()
      ->expects($this->once())
      ->method('writeClose');
    $session->close();
    $this->assertAttributeEquals(
      FALSE, '_active', $session
    );
  }

  /**
  * @covers PapayaSession::reset
  * @backupGlobals enabled
   * @runInSeparateProcess
   */
  public function testReset() {
    $_SESSION = array('foo' => 'bar');
    $session = $this->getActiveSessionMockFixture(PapayaSessionId::SOURCE_COOKIE);
    $session->reset();
    $this->assertEquals(
      array(), $_SESSION
    );
  }

  /**
  * @covers PapayaSession::destroy
  * @backupGlobals enabled
   * @runInSeparateProcess
   */
  public function testDestroy() {
    $session = $this->getActiveSessionMockFixture(PapayaSessionId::SOURCE_COOKIE);
    $session
      ->wrapper()
      ->expects($this->once())
      ->method('destroy');
    $session->destroy();
    $this->assertAttributeEquals(
      FALSE, '_active', $session
    );
  }

  /**
  * @covers PapayaSession::regenerateId
  * @backupGlobals enabled
   * @runInSeparateProcess
   */
  public function testRegenerateId() {
    $session = $this->getActiveSessionMockFixture(PapayaSessionId::SOURCE_COOKIE);
    $session
      ->wrapper()
      ->expects($this->once())
      ->method('regenerateId');
    $this->assertFalse($session->regenerateId());
    $this->assertAttributeEquals(
      TRUE, '_active', $session
    );
  }

  /**
  * @covers PapayaSession::regenerateId
  * @backupGlobals enabled
   * @runInSeparateProcess
   */
  public function testRegenerateIdExpectingPathRedirect() {
    $session = $this->getActiveSessionMockFixture(PapayaSessionId::SOURCE_PATH);
    $session
      ->wrapper()
      ->expects($this->once())
      ->method('regenerateId');
    $redirect = $session->regenerateId();
    $this->assertInstanceOf(
      PapayaSessionRedirect::class, $redirect
    );
  }

  /**
  * @covers PapayaSession::regenerateId
  * @backupGlobals enabled
   * @runInSeparateProcess
   */
  public function testRegenerateIdExpectingPathRedirectToTargetUrl() {
    $session = $this->getActiveSessionMockFixture(PapayaSessionId::SOURCE_PATH);
    $session
      ->wrapper()
      ->expects($this->once())
      ->method('regenerateId');
    $redirect = $session->regenerateId('http://www.sample.tld/foo?bar=123');
    $this->assertInstanceOf(
      PapayaSessionRedirect::class, $redirect
    );
    $this->assertEquals(
      'http://www.sample.tld/foo?bar=123', $redirect->url()->getUrl()
    );
  }

  /**************************
  * Fixtures
  **************************/

  public function getSessionWrapperFixture($canStart = TRUE) {
    $wrapper = $this->getMock(
      PapayaSessionWrapper::class,
      array('setName', 'setId', 'start', 'writeClose', 'destroy', 'regenerateId')
    );
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
    $id = $this->getMock(PapayaSessionId::class, array('__toString', 'existsIn'));
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
    if ($source == PapayaSessionId::SOURCE_ANY && count($this->_idSources) > 0) {
      return TRUE;
    }
    foreach ($this->_idSources as $idSource) {
      if ($source & $idSource) {
        return TRUE;
      }
    }
    return FALSE;
  }

  public function getActiveSessionMockFixture($source) {
    $_SERVER['HTTP_USER_AGENT'] =
      'Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2';
    $_SERVER['HTTPS'] = NULL;
    $session = new PapayaSession();
    $session->papaya($this->mockPapaya()->application());
    $session->wrapper($this->getSessionWrapperFixture());
    $session->id($this->getSessionIdFixture(array($source)));
    $session->options()->fallback = PapayaSessionOptions::FALLBACK_REWRITE;
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
        array(PapayaSessionId::SOURCE_COOKIE),
        PapayaSessionOptions::FALLBACK_REWRITE
      ),
      'path only' => array(
        array(PapayaSessionId::SOURCE_PATH),
        PapayaSessionOptions::FALLBACK_REWRITE
      ),
      'query only' => array(
        array(PapayaSessionId::SOURCE_QUERY),
        PapayaSessionOptions::FALLBACK_PARAMETER
      )
    );
  }

  public static function provideSessionSourcesTriggeringRedirect() {
    return array(
      'cookie and path' => array(
        array(PapayaSessionId::SOURCE_COOKIE, PapayaSessionId::SOURCE_PATH),
        PapayaSessionOptions::FALLBACK_REWRITE,
        0,
      ),
      'path and query' => array(
        array(PapayaSessionId::SOURCE_PATH, PapayaSessionId::SOURCE_QUERY),
        PapayaSessionOptions::FALLBACK_REWRITE,
        PapayaSessionId::SOURCE_PATH
      ),
      'query and path' => array(
        array(PapayaSessionId::SOURCE_PATH, PapayaSessionId::SOURCE_QUERY),
        PapayaSessionOptions::FALLBACK_PARAMETER,
        PapayaSessionId::SOURCE_QUERY
      )
    );
  }
}
