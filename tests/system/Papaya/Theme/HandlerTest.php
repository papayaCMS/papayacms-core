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

use Papaya\Content\Structure;
use Papaya\Url;

require_once __DIR__.'/../../../bootstrap.php';

class PapayaThemeHandlerTest extends PapayaTestCase {

  /**
  * @covers \PapayaThemeHandler::getUrl
  */
  public function testGetUrl() {
    $url = $this->getMockBuilder(Url::class)->setMethods(array('getHostUrl'))->getMock();
    $url
      ->expects($this->once())
      ->method('getHostUrl')
      ->will($this->returnValue('http://test.tld'));
    $request = $this->createMock(PapayaRequest::class);
    $request
      ->expects($this->once())
      ->method('getParameter')
      ->will($this->returnValue(FALSE));
    $request
      ->expects($this->once())
      ->method('getUrl')
      ->will($this->returnValue($url));
    $handler = new \PapayaThemeHandler();
    $handler->papaya(
      $this->mockPapaya()->application(
        array(
          'Request' => $request,
          'Options' => $this->mockPapaya()->options(
            array(
             'PAPAYA_CDN_THEMES' => '',
             'PAPAYA_PATH_THEMES' => '/themes/',
             'PAPAYA_LAYOUT_THEME' => 'theme'
            )
          )
        )
      )
    );
    $this->assertEquals(
      'http://test.tld/themes/theme/',
      $handler->getUrl()
    );
  }

  /**
  * @covers \PapayaThemeHandler::getUrl
  */
  public function testGetUrlWithThemeNameParameter() {
    $url = $this->getMockBuilder(Url::class)->setMethods(array('getHostUrl'))->getMock();
    $url
      ->expects($this->once())
      ->method('getHostUrl')
      ->will($this->returnValue('http://test.tld'));
    $request = $this->createMock(PapayaRequest::class);
    $request
      ->expects($this->once())
      ->method('getUrl')
      ->will($this->returnValue($url));
    $handler = new \PapayaThemeHandler();
    $handler->papaya(
      $this->mockPapaya()->application(
        array(
          'Request' => $request,
          'Options' => $this->mockPapaya()->options(
            array(
             'PAPAYA_CDN_THEMES' => '',
             'PAPAYA_PATH_THEMES' => '/themes/',
             'PAPAYA_LAYOUT_THEME' => 'theme'
            )
          )
        )
      )
    );
    $this->assertEquals(
      'http://test.tld/themes/sample/',
      $handler->getUrl('sample')
    );
  }

  /**
  * @covers \PapayaThemeHandler::getUrl
  */
  public function testGetUrlWithCdn() {
    $request = $this->createMock(PapayaRequest::class);
    $request
      ->expects($this->once())
      ->method('getParameter')
      ->will($this->returnValue(FALSE));
    $handler = new \PapayaThemeHandler();
    $handler->papaya(
      $this->mockPapaya()->application(
        array(
          'Request' => $request,
          'Options' => $this->mockPapaya()->options(
            array(
             'PAPAYA_CDN_THEMES' => 'http://test.tld/',
             'PAPAYA_LAYOUT_THEME' => 'theme'
            )
          )
        )
      )
    );
    $this->assertEquals(
      'http://test.tld/theme/',
      $handler->getUrl()
    );
  }

  /**
  * @covers \PapayaThemeHandler::getUrl
  * @backupGlobals enabled
  */
  public function testGetUrlWithSecureCdn() {
    $_SERVER['HTTPS'] = 'on';
    $request = $this->createMock(PapayaRequest::class);
    $request
      ->expects($this->once())
      ->method('getParameter')
      ->will($this->returnValue(FALSE));
    $handler = new \PapayaThemeHandler();
    $handler->papaya(
      $this->mockPapaya()->application(
        array(
          'Request' => $request,
          'Options' => $this->mockPapaya()->options(
            array(
             'PAPAYA_CDN_THEMES_SECURE' => 'https://secure.test.tld/',
             'PAPAYA_LAYOUT_THEME' => 'theme'
            )
          )
        )
      )
    );
    $this->assertEquals(
      'https://secure.test.tld/theme/',
      $handler->getUrl()
    );
  }

  /**
  * @covers \PapayaThemeHandler::getLocalPath
  * @backupGlobals enabled
  */
  public function testGetLocalPath() {
    $_SERVER['DOCUMENT_ROOT'] = '/document/root';
    $handler = new \PapayaThemeHandler();
    $handler->papaya(
      $this->mockPapaya()->application(
        array(
          'Options' => $this->mockPapaya()->options(
            array(
             'PAPAYA_PATH_THEMES' => '/themes/'
            )
          )
        )
      )
    );
    $this->assertEquals(
      '/document/root/themes/',
      $handler->getLocalPath()
    );
  }

  /**
  * @covers \PapayaThemeHandler::getLocalThemePath
  * @backupGlobals enabled
  */
  public function testGetLocalThemePath() {
    $_SERVER['DOCUMENT_ROOT'] = '/document/root';
    $request = $this->createMock(PapayaRequest::class);
    $request
      ->expects($this->once())
      ->method('getParameter')
      ->will($this->returnValue(FALSE));
    $handler = new \PapayaThemeHandler();
    $handler->papaya(
      $this->mockPapaya()->application(
        array(
          'Request' => $request,
          'Options' => $this->mockPapaya()->options(
            array(
             'PAPAYA_PATH_THEMES' => '/themes/',
             'PAPAYA_LAYOUT_THEME' => 'theme'
            )
          )
        )
      )
    );
    $this->assertEquals(
      '/document/root/themes/theme/',
      $handler->getLocalThemePath()
    );
  }

  /**
  * @covers \PapayaThemeHandler::getLocalThemePath
  * @backupGlobals enabled
  */
  public function testGetLocalThemePathWithThemeName() {
    $_SERVER['DOCUMENT_ROOT'] = '/document/root';
    $handler = new \PapayaThemeHandler();
    $handler->papaya(
      $this->mockPapaya()->application(
        array(
          'Options' => $this->mockPapaya()->options(
            array(
             'PAPAYA_PATH_THEMES' => '/themes/',
             'PAPAYA_LAYOUT_THEME' => 'theme'
            )
          )
        )
      )
    );
    $this->assertEquals(
      '/document/root/themes/sample/',
      $handler->getLocalThemePath('sample')
    );
  }

  /**
  * @covers \PapayaThemeHandler::getDefinition
  * @backupGlobals enabled
  */
  public function testGetDefinition() {
    $_SERVER['DOCUMENT_ROOT'] = __DIR__;
    $handler = new \PapayaThemeHandler();
    $handler->papaya(
      $this->mockPapaya()->application(
        array(
          'Options' => $this->mockPapaya()->options(
            array(
             'PAPAYA_PATH_THEMES' => '/'
            )
          )
        )
      )
    );
    $this->assertInstanceOf(
      Structure::class,
      $handler->getDefinition('TestData')
    );
  }

  /**
  * @covers \PapayaThemeHandler::getTheme
  */
  public function testGetThemeInPublicMode() {
    $request = $this->createMock(PapayaRequest::class);
    $request
      ->expects($this->once())
      ->method('getParameter')
      ->will($this->returnValue(FALSE));
    $handler = new \PapayaThemeHandler();
    $handler->papaya(
      $this->mockPapaya()->application(
        array(
          'Request' => $request,
          'Options' => $this->mockPapaya()->options(
            array(
             'PAPAYA_LAYOUT_THEME' => 'theme'
            )
          )
        )
      )
    );
    $this->assertEquals(
      'theme',
      $handler->getTheme()
    );
  }

  /**
  * @covers \PapayaThemeHandler::getTheme
  */
  public function testGetThemeInPreviewMode() {
    $request = $this->createMock(PapayaRequest::class);
    $request
      ->expects($this->once())
      ->method('getParameter')
      ->will($this->returnValue(TRUE));
    $session = $this->createMock(PapayaSession::class);
    $values = $this
      ->getMockBuilder(PapayaSessionValues::class)
      ->setConstructorArgs(array($session))
      ->getMock();
    $values
      ->expects($this->once())
      ->method('get')
      ->with($this->equalTo('PapayaPreviewTheme'))
      ->will($this->returnValue('ThemeFromSession'));
    $session
      ->expects($this->once())
      ->method('__get')
      ->with($this->equalTo('values'))
      ->will($this->returnValue($values));
    $handler = new \PapayaThemeHandler();
    $handler->papaya(
      $this->mockPapaya()->application(
        array(
          'Request' => $request,
          'Session' => $session,
          'Options' => $this->mockPapaya()->options(
            array(
             'PAPAYA_LAYOUT_THEME' => 'theme'
            )
          )
        )
      )
    );
    $this->assertEquals(
      'ThemeFromSession',
      $handler->getTheme()
    );
  }

  /**
  * @covers \PapayaThemeHandler::getThemeSet
  */
  public function testGetThemeSetInPublicMode() {
    $request = $this->createMock(PapayaRequest::class);
    $request
      ->expects($this->once())
      ->method('getParameter')
      ->will($this->returnValue(FALSE));
    $handler = new \PapayaThemeHandler();
    $handler->papaya(
      $this->mockPapaya()->application(
        array(
          'Request' => $request,
          'Options' => $this->mockPapaya()->options(
            array(
             'PAPAYA_LAYOUT_THEME_SET' => '23 (faked)'
            )
          )
        )
      )
    );
    $this->assertEquals(
      '23',
      $handler->getThemeSet()
    );
  }

  /**
  * @covers \PapayaThemeHandler::getThemeSet
  */
  public function testGetThemeSetInPreviewMode() {
    $request = $this->createMock(PapayaRequest::class);
    $request
      ->expects($this->once())
      ->method('getParameter')
      ->will($this->returnValue(TRUE));
    $session = $this->createMock(PapayaSession::class);
    $values = $this
      ->getMockBuilder(PapayaSessionValues::class)
      ->setConstructorArgs(array($session))
      ->getMock();
    $values
      ->expects($this->once())
      ->method('get')
      ->with($this->equalTo('PapayaPreviewThemeSet'))
      ->will($this->returnValue('42 (yeah)'));
    $session
      ->expects($this->once())
      ->method('__get')
      ->with($this->equalTo('values'))
      ->will($this->returnValue($values));
    $handler = new \PapayaThemeHandler();
    $handler->papaya(
      $this->mockPapaya()->application(
        array(
          'Request' => $request,
          'Session' => $session,
          'Options' => $this->mockPapaya()->options(
            array(
             'PAPAYA_LAYOUT_THEME_SET' => '23 (faked)'
            )
          )
        )
      )
    );
    $this->assertEquals(
      '42',
      $handler->getThemeSet()
    );
  }

  /**
  * @covers \PapayaThemeHandler::setThemePreview
  */
  public function testSetThemePreview() {
    $session = $this->createMock(PapayaSession::class);
    $values = $this
      ->getMockBuilder(PapayaSessionValues::class)
      ->setConstructorArgs(array($session))
      ->getMock();
    $values
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('PapayaPreviewTheme'), $this->equalTo('Sample'));
    $session
      ->expects($this->once())
      ->method('__get')
      ->with($this->equalTo('values'))
      ->will($this->returnValue($values));
    $handler = new \PapayaThemeHandler();
    $handler->papaya(
      $this->mockPapaya()->application(array('Session' => $session))
    );
    $handler->setThemePreview('Sample');
  }

  /**
  * @covers \PapayaThemeHandler::removeThemePreview
  */
  public function testRemoveThemePreview() {
    $session = $this->createMock(PapayaSession::class);
    $values = $this
      ->getMockBuilder(PapayaSessionValues::class)
      ->setConstructorArgs(array($session))
      ->getMock();
    $values
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('PapayaPreviewTheme'), $this->equalTo(NULL));
    $session
      ->expects($this->once())
      ->method('__get')
      ->with($this->equalTo('values'))
      ->will($this->returnValue($values));
    $handler = new \PapayaThemeHandler();
    $handler->papaya(
      $this->mockPapaya()->application(array('Session' => $session))
    );
    $handler->removeThemePreview();
  }
}
