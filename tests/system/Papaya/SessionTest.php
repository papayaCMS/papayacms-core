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

namespace Papaya {
  require_once __DIR__.'/../../bootstrap.php';

  /**
   * @covers \Papaya\Session
   */
  class SessionTest extends TestCase {

    private $_idSources = [];

    public function testSetName() {
      $session = new Session();
      $session->setName('sessionname');
      $this->assertEquals(
        'sessionname', $session->name
      );
    }

    public function testIsActiveExpectingFalse() {
      $session = new Session();
      $this->assertFalse($session->isActive());
    }

    public function testIsActiveAfterActivationExpectingTrue() {
      $_SERVER['HTTP_USER_AGENT'] =
        'Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2';
      $_SERVER['HTTPS'] = NULL;
      $session = new Session();
      $session->papaya($this->mockPapaya()->application());
      $session->wrapper($this->getSessionWrapperFixture());
      $session->id($this->getSessionIdFixture([Session\Id::SOURCE_QUERY]));
      $session->activate(FALSE);
      $this->assertTrue($session->isActive());
    }

    public function testPropertyActiveExpectingFalse() {
      $session = new Session();
      $this->assertFalse($session->active);
    }

    public function testValuesSet() {
      $session = new Session();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Session\Values $values */
      $values = $this
        ->getMockBuilder(Session\Values::class)
        ->setConstructorArgs([$session])
        ->getMock();
      $session->values($values);
      $this->assertSame(
        $values, $session->values
      );
    }

    public function testValuesGetAfterSet() {
      $session = new Session();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Session\Values $values */
      $values = $this
        ->getMockBuilder(Session\Values::class)
        ->setConstructorArgs([$session])
        ->getMock();
      $session->values($values);
      $this->assertSame(
        $values, $session->values()
      );
    }

    public function testValuesGetUsingImplicitCreate() {
      $session = new Session();
      $this->assertInstanceOf(
        Session\Values::class, $session->values()
      );
    }

    public function testSetValue() {
      $session = new Session();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Session\Values $values */
      $values = $this
        ->getMockBuilder(Session\Values::class)
        ->setConstructorArgs([$session])
        ->getMock();
      $values
        ->expects($this->once())
        ->method('set')
        ->with($this->equalTo('foo'), $this->equalTo('bar'));
      $session->values($values);
      $session->setValue('foo', 'bar');
    }

    public function testGetValue() {
      $session = new Session();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Session\Values $values */
      $values = $this
        ->getMockBuilder(Session\Values::class)
        ->setConstructorArgs([$session])
        ->getMock();
      $values
        ->expects($this->once())
        ->method('get')
        ->with($this->equalTo('foo'))
        ->willReturn('bar');
      $session->values($values);
      $this->assertEquals('bar', $session->getValue('foo'));
    }

    public function testOptionsSet() {
      $session = new Session();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Session\Options $options */
      $options = $this->createMock(Session\Options::class);
      $session->options($options);
      $this->assertSame(
        $options, $session->options
      );
    }

    public function testOptionsGetAfterSet() {
      $session = new Session();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Session\Options $options */
      $options = $this->createMock(Session\Options::class);
      $session->options($options);
      $this->assertSame(
        $options, $session->options()
      );
    }

    public function testOptionsGetUsingImplicitCreate() {
      $session = new Session();
      $this->assertInstanceOf(
        Session\Options::class, $session->options()
      );
    }

    public function testIdSet() {
      $session = new Session();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Session\Id $id */
      $id = $this->createMock(Session\Id::class);
      $session->id($id);
      $this->assertSame(
        $id, $session->id()
      );
    }

    public function testIdGetAfterSet() {
      $session = new Session();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Session\Id $id */
      $id = $this->createMock(Session\Id::class);
      $session->id($id);
      $this->assertSame(
        $id, $session->id()
      );
    }

    public function testIdGetUsingImplicitCreate() {
      $session = new Session();
      $this->assertInstanceOf(
        Session\Id::class, $session->id()
      );
    }

    public function testPropertyIdReturnsString() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Session\Id $id */
      $id = $this->createMock(Session\Id::class);
      $id
        ->expects($this->once())
        ->method('__toString')
        ->willReturn('success');
      $session = new Session();
      $session->id($id);
      $this->assertEquals(
        'success', $session->id
      );
    }

    public function testWrapperGetAfterSet() {
      $session = new Session();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Session\Wrapper $wrapper */
      $wrapper = $this->createMock(Session\Wrapper::class);
      $session->wrapper($wrapper);
      $this->assertSame(
        $wrapper, $session->wrapper()
      );
    }

    public function testWrapperGetUsingImplicitCreate() {
      $session = new Session();
      $this->assertInstanceOf(
        Session\Wrapper::class, $session->wrapper()
      );
    }

    public function testValuesPropertyGet() {
      $session = new Session();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Session\Values $values */
      $values = $this
        ->getMockBuilder(Session\Values::class)
        ->setConstructorArgs([$session])
        ->getMock();
      $session->values($values);
      $this->assertSame(
        $values, $session->values
      );
    }

    public function testOptionsPropertyGet() {
      $session = new Session();
      /** @var \PHPUnit_Framework_MockObject_MockObject|Session\Options $options */
      $options = $this->createMock(Session\Options::class);
      $session->options($options);
      $this->assertSame(
        $options, $session->options
      );
    }

    public function testPropertyGetExpectingException() {
      $session = new Session();
      $this->expectException(\UnexpectedValueException::class);
      /** @noinspection PhpUndefinedFieldInspection */
      $session->INVALID_PROPERTY_NAME;
    }

    public function testIsAllowedExpectingTrue() {
      $_SERVER['HTTP_USER_AGENT'] =
        'Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2';
      $_SERVER['HTTPS'] = NULL;
      $session = new Session();
      $session->papaya($this->mockPapaya()->application());
      $this->assertTrue($session->isAllowed());
    }

    public function testIsAllowedExpectingFalse() {
      $_SERVER['HTTP_USER_AGENT'] = 'Googlebot/2.1 (+http://www.google.com/bot.html)';
      $_SERVER['HTTPS'] = NULL;
      $session = new Session();
      $session->papaya($this->mockPapaya()->application());
      $this->assertFalse($session->isAllowed());
    }

    public function testIsProtocolAllowedExpectingTrue() {
      $session = new Session();
      $session->papaya($this->mockPapaya()->application());
      $_SERVER['HTTPS'] = NULL;
      $this->assertTrue($session->isProtocolAllowed());
    }

    public function testIsProtocolAllowedWithSecureSessionExpectingTrue() {
      $session = new Session();
      $session->papaya(
        $this->mockPapaya()->application(
          [
            'options' => $this->mockPapaya()->options(
              [
                'PAPAYA_SESSION_SECURE' => TRUE
              ]
            )
          ]
        )
      );
      $_SERVER['HTTPS'] = 'on';
      $this->assertTrue($session->isProtocolAllowed());
    }

    /**
     * @backupGlobals
     */
    public function testIsProtocolAllowedWithSecureSessionExpectingFalse() {
      $session = new Session();
      $session->papaya(
        $this->mockPapaya()->application(
          [
            'options' => $this->mockPapaya()->options(
              [
                'PAPAYA_SESSION_SECURE' => TRUE
              ]
            )
          ]
        )
      );
      $_SERVER['HTTPS'] = NULL;
      $this->assertFalse($session->isProtocolAllowed());
    }

    public function testIsSecureOnlyExpectingTrue() {
      $session = new Session();
      $session->papaya(
        $this->mockPapaya()->application(
          [
            'options' => $this->mockPapaya()->options(
              [
                'PAPAYA_SESSION_SECURE' => TRUE
              ]
            )
          ]
        )
      );
      $this->assertTrue($session->isSecureOnly());
    }

    public function testIsSecureOnlyAdministrationExpectingTrue() {
      $session = new Session();
      $session->papaya(
        $this->mockPapaya()->application(
          [
            'options' => $this->mockPapaya()->options(
              [
                'PAPAYA_SESSION_SECURE' => FALSE,
                'PAPAYA_UI_SECURE' => TRUE
              ]
            )
          ]
        )
      );
      $session->isAdministration(TRUE);
      $this->assertTrue($session->isSecureOnly());
    }

    /**
     * @dataProvider provideInvalidOptionsForSecureSession
     * @param array $options
     */
    public function testIsSecureOnlyExpectingFalse(array $options) {
      $session = new Session();
      $session->papaya(
        $this->mockPapaya()->application(
          [
            'options' => $this->mockPapaya()->options($options)
          ]
        )
      );
      $session->isAdministration(FALSE);
      $this->assertFalse($session->isSecureOnly());
    }

    /**
     * @dataProvider provideSessionSourcesNoRedirect
     * @param array $sources
     * @param int $fallback
     */
    public function testActivateExpectingSuccess(array $sources, $fallback) {
      $_SERVER['HTTP_USER_AGENT'] =
        'Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2';
      $_SERVER['HTTPS'] = NULL;
      $session = new Session();
      $session->papaya($this->mockPapaya()->application());
      $session->wrapper($this->getSessionWrapperFixture());
      $session->id($this->getSessionIdFixture($sources));
      $session->options()->fallback = $fallback;
      $this->assertNull(
        $session->activate(TRUE)
      );
    }

    public function testActivateWithRobotExpectingNull() {
      $_SERVER['HTTP_USER_AGENT'] = 'Googlebot';
      $_SERVER['HTTPS'] = NULL;
      $session = new Session();
      $session->papaya($this->mockPapaya()->application());
      $this->assertNull($session->activate(FALSE));
    }

    /**
     * @dataProvider provideSessionSourcesTriggeringRedirect
     * @param array $sources
     * @param int $fallback
     * @param int $transport
     */
    public function testActivateExpectingRedirect($sources, $fallback, $transport) {
      $_SERVER['HTTP_USER_AGENT'] =
        'Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2';
      $_SERVER['HTTPS'] = NULL;
      $session = new Session();
      $session->papaya($this->mockPapaya()->application());
      $session->wrapper($this->getSessionWrapperFixture());
      $session->id($this->getSessionIdFixture($sources));
      $session->options()->fallback = $fallback;
      $redirect = $session->activate(TRUE);
      $this->assertInstanceOf(
        Session\Redirect::class, $redirect
      );
      $this->assertSame(
        $transport, $redirect->transport
      );
    }

    public function testActivateWithoutIdExpectingRedirect() {
      $_SERVER['HTTP_USER_AGENT'] =
        'Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2';
      $_SERVER['HTTPS'] = NULL;
      /** @var \PHPUnit_Framework_MockObject_MockObject|Session\Wrapper $wrapper */
      $wrapper = $this->createMock(Session\Wrapper::class);
      $wrapper
        ->expects($this->once())
        ->method('setName')
        ->with($this->equalTo('sid'));
      $wrapper
        ->expects($this->once())
        ->method('start')
        ->willReturn(TRUE);
      $session = new Session();
      $session->papaya($this->mockPapaya()->application());
      $session->wrapper($wrapper);
      $session->id($this->getSessionIdFixture([]));
      $session->options()->fallback = Session\Options::FALLBACK_REWRITE;
      $redirect = $session->activate(TRUE);
      $this->assertInstanceOf(
        Session\Redirect::class, $redirect
      );
      $this->assertSame(
        Session\Id::SOURCE_PATH, $redirect->transport
      );
    }

    public function testClose() {
      $session = $this->getActiveSessionFixture(Session\Id::SOURCE_COOKIE);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Session\Wrapper $wrapper */
      $wrapper = $session->wrapper();
      $wrapper
        ->expects($this->once())
        ->method('writeClose');
      $session->close();
      $this->assertEquals(
        FALSE, $session->active
      );
    }

    public function testReset() {
      $_SESSION = ['foo' => 'bar'];
      $session = $this->getActiveSessionFixture(Session\Id::SOURCE_COOKIE);
      $session->reset();
      $this->assertEquals(
        [], $_SESSION
      );
    }

    public function testDestroy() {
      $session = $this->getActiveSessionFixture(Session\Id::SOURCE_COOKIE);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Session\Wrapper $wrapper */
      $wrapper = $session->wrapper();
      $wrapper
        ->expects($this->once())
        ->method('destroy');
      $session->destroy();
      $this->assertEquals(
        FALSE, $session->active
      );
    }

    public function testRegenerateId() {
      $session = $this->getActiveSessionFixture(Session\Id::SOURCE_COOKIE);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Session\Wrapper $wrapper */
      $wrapper = $session->wrapper();
      $wrapper
        ->expects($this->once())
        ->method('regenerateId');
      $this->assertFalse($session->regenerateId());
      $this->assertTrue($session->active);
    }

    public function testRegenerateIdExpectingPathRedirect() {
      $session = $this->getActiveSessionFixture(Session\Id::SOURCE_PATH);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Session\Wrapper $wrapper */
      $wrapper = $session->wrapper();
      $wrapper
        ->expects($this->once())
        ->method('regenerateId');
      $redirect = $session->regenerateId();
      $this->assertInstanceOf(
        Session\Redirect::class, $redirect
      );
    }

    public function testRegenerateIdExpectingPathRedirectToTargetUrl() {
      $session = $this->getActiveSessionFixture(Session\Id::SOURCE_PATH);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Session\Wrapper $wrapper */
      $wrapper = $session->wrapper();
      $wrapper
        ->expects($this->once())
        ->method('regenerateId');
      $redirect = $session->regenerateId('http://www.sample.tld/foo?bar=123');
      $this->assertInstanceOf(
        Session\Redirect::class, $redirect
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
     * @return \PHPUnit_Framework_MockObject_MockObject|Session\Wrapper
     */
    public function getSessionWrapperFixture($canStart = TRUE) {
      $wrapper = $this->createMock(Session\Wrapper::class);
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
        ->willReturn($canStart);
      return $wrapper;
    }

    /**
     * @param $source
     * @return \PHPUnit_Framework_MockObject_MockObject|Session\Id
     */
    public function getSessionIdFixture($source) {
      $this->_idSources = $source;
      $id = $this->createMock(Session\Id::class);
      $id
        ->method('__toString')
        ->willReturn('ab123456789012345678901234567890');
      $id
        ->method('existsIn')
        ->withAnyParameters()
        ->willReturnCallback([$this, 'callbackSessionIdExistsIn']);
      return $id;
    }

    public function callbackSessionIdExistsIn($source) {
      if ($source === Session\Id::SOURCE_ANY && count($this->_idSources) > 0) {
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
     * @return Session
     */
    public function getActiveSessionFixture($source) {
      $_SERVER['HTTP_USER_AGENT'] =
        'Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2';
      $_SERVER['HTTPS'] = NULL;
      $session = new Session();
      $session->papaya($this->mockPapaya()->application());
      $session->wrapper($this->getSessionWrapperFixture());
      $session->id($this->getSessionIdFixture([$source]));
      $session->options()->fallback = Session\Options::FALLBACK_REWRITE;
      $session->activate(FALSE);
      return $session;
    }

    /**************************
     * Data Provider
     **************************/

    public static function provideInvalidOptionsForSecureSession() {
      return [
        'default' => [[]],
        'admin only' => [
          [
            'PAPAYA_UI_SECURE' => TRUE
          ]
        ]
      ];
    }

    public static function provideSessionSourcesNoRedirect() {
      return [
        'cookie only' => [
          [Session\Id::SOURCE_COOKIE],
          Session\Options::FALLBACK_REWRITE
        ],
        'path only' => [
          [Session\Id::SOURCE_PATH],
          Session\Options::FALLBACK_REWRITE
        ],
        'query only' => [
          [Session\Id::SOURCE_QUERY],
          Session\Options::FALLBACK_PARAMETER
        ]
      ];
    }

    public static function provideSessionSourcesTriggeringRedirect() {
      return [
        'cookie and path' => [
          [Session\Id::SOURCE_COOKIE, Session\Id::SOURCE_PATH],
          Session\Options::FALLBACK_REWRITE,
          0,
        ],
        'path and query' => [
          [Session\Id::SOURCE_PATH, Session\Id::SOURCE_QUERY],
          Session\Options::FALLBACK_REWRITE,
          Session\Id::SOURCE_PATH
        ],
        'query and path' => [
          [Session\Id::SOURCE_PATH, Session\Id::SOURCE_QUERY],
          Session\Options::FALLBACK_PARAMETER,
          Session\Id::SOURCE_QUERY
        ]
      ];
    }
  }
}
