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

namespace Papaya\CMS\Theme {


  use Papaya\CMS\Content\Structure as ContentStructure;
  use Papaya\Request;
  use Papaya\Session;
  use Papaya\Session\Values as SessionValues;
  use Papaya\TestCase;
  use Papaya\URL;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\CMS\Theme\Handler
   */
  class HandlerTest extends TestCase {

    public function testGetUrl() {
      $url = $this->getMockBuilder(URL::class)->setMethods(['getHostUrl'])->getMock();
      $url
        ->expects($this->once())
        ->method('getHostUrl')
        ->willReturn('http://test.tld');
      $request = $this->createMock(Request::class);
      $request
        ->expects($this->once())
        ->method('getParameter')
        ->willReturn(FALSE);
      $request
        ->expects($this->once())
        ->method('getUrl')
        ->willReturn($url);
      $handler = new Handler();
      $handler->papaya(
        $this->mockPapaya()->application(
          [
            'Request' => $request,
            'Options' => $this->mockPapaya()->options(
              [
                'PAPAYA_CDN_THEMES' => '',
                'PAPAYA_PATH_THEMES' => '/themes/',
                'PAPAYA_LAYOUT_THEME' => 'theme'
              ]
            )
          ]
        )
      );
      $this->assertEquals(
        'http://test.tld/themes/theme/',
        $handler->getURL()
      );
    }

    public function testGetUrlWithThemeNameParameter() {
      $url = $this->getMockBuilder(URL::class)->setMethods(['getHostUrl'])->getMock();
      $url
        ->expects($this->once())
        ->method('getHostUrl')
        ->willReturn('http://test.tld');
      $request = $this->createMock(Request::class);
      $request
        ->expects($this->once())
        ->method('getUrl')
        ->willReturn($url);
      $handler = new Handler();
      $handler->papaya(
        $this->mockPapaya()->application(
          [
            'Request' => $request,
            'Options' => $this->mockPapaya()->options(
              [
                'PAPAYA_CDN_THEMES' => '',
                'PAPAYA_PATH_THEMES' => '/themes/',
                'PAPAYA_LAYOUT_THEME' => 'theme'
              ]
            )
          ]
        )
      );
      $this->assertEquals(
        'http://test.tld/themes/sample/',
        $handler->getURL('sample')
      );
    }

    public function testGetUrlForFileWithThemeNameParameter() {
      $url = $this->getMockBuilder(URL::class)->setMethods(['getHostUrl'])->getMock();
      $url
        ->expects($this->once())
        ->method('getHostUrl')
        ->willReturn('http://test.tld');
      $request = $this->createMock(Request::class);
      $request
        ->expects($this->once())
        ->method('getUrl')
        ->willReturn($url);
      $handler = new Handler();
      $handler->papaya(
        $this->mockPapaya()->application(
          [
            'Request' => $request,
            'Options' => $this->mockPapaya()->options(
              [
                'PAPAYA_CDN_THEMES' => '',
                'PAPAYA_PATH_THEMES' => '/themes/',
                'PAPAYA_LAYOUT_THEME' => 'theme'
              ]
            )
          ]
        )
      );
      $this->assertEquals(
        'http://test.tld/themes/sample/script.js',
        $handler->getURL('sample', 'script.js', FALSE)
      );
    }

    public function testGetUrlWithCdn() {
      $request = $this->createMock(Request::class);
      $request
        ->expects($this->once())
        ->method('getParameter')
        ->willReturn(FALSE);
      $handler = new Handler();
      $handler->papaya(
        $this->mockPapaya()->application(
          [
            'Request' => $request,
            'Options' => $this->mockPapaya()->options(
              [
                'PAPAYA_CDN_THEMES' => 'http://test.tld/',
                'PAPAYA_LAYOUT_THEME' => 'theme'
              ]
            )
          ]
        )
      );
      $this->assertEquals(
        'http://test.tld/theme/',
        $handler->getURL()
      );
    }

    /**
     * @backupGlobals enabled
     */
    public function testGetUrlWithSecureCdn() {
      $_SERVER['HTTPS'] = 'on';
      $request = $this->createMock(Request::class);
      $request
        ->expects($this->once())
        ->method('getParameter')
        ->willReturn(FALSE);
      $handler = new Handler();
      $handler->papaya(
        $this->mockPapaya()->application(
          [
            'Request' => $request,
            'Options' => $this->mockPapaya()->options(
              [
                'PAPAYA_CDN_THEMES_SECURE' => 'https://secure.test.tld/',
                'PAPAYA_LAYOUT_THEME' => 'theme'
              ]
            )
          ]
        )
      );
      $this->assertEquals(
        'https://secure.test.tld/theme/',
        $handler->getURL()
      );
    }

    /**
     * @backupGlobals enabled
     */
    public function testGetLocalPath() {
      $_SERVER['DOCUMENT_ROOT'] = '/document/root';
      $handler = new Handler();
      $handler->papaya(
        $this->mockPapaya()->application(
          [
            'Options' => $this->mockPapaya()->options(
              [
                'PAPAYA_PATH_THEMES' => '/themes/'
              ]
            )
          ]
        )
      );
      $this->assertEquals(
        '/document/root/themes/',
        $handler->getLocalPath()
      );
    }

    /**
     * @backupGlobals enabled
     */
    public function testGetLocalThemePath() {
      $_SERVER['DOCUMENT_ROOT'] = '/document/root';
      $request = $this->createMock(Request::class);
      $request
        ->expects($this->once())
        ->method('getParameter')
        ->willReturn(FALSE);
      $handler = new Handler();
      $handler->papaya(
        $this->mockPapaya()->application(
          [
            'Request' => $request,
            'Options' => $this->mockPapaya()->options(
              [
                'PAPAYA_PATH_THEMES' => '/themes/',
                'PAPAYA_LAYOUT_THEME' => 'theme'
              ]
            )
          ]
        )
      );
      $this->assertEquals(
        '/document/root/themes/theme/',
        $handler->getLocalThemePath()
      );
    }

    /**
     * @backupGlobals enabled
     */
    public function testGetLocalThemePathWithThemeName() {
      $_SERVER['DOCUMENT_ROOT'] = '/document/root';
      $handler = new Handler();
      $handler->papaya(
        $this->mockPapaya()->application(
          [
            'Options' => $this->mockPapaya()->options(
              [
                'PAPAYA_PATH_THEMES' => '/themes/',
                'PAPAYA_LAYOUT_THEME' => 'theme'
              ]
            )
          ]
        )
      );
      $this->assertEquals(
        '/document/root/themes/sample/',
        $handler->getLocalThemePath('sample')
      );
    }

    /**
     * @backupGlobals enabled
     */
    public function testGetDefinition() {
      $_SERVER['DOCUMENT_ROOT'] = __DIR__;
      $handler = new Handler();
      $handler->papaya(
        $this->mockPapaya()->application(
          [
            'Options' => $this->mockPapaya()->options(
              [
                'PAPAYA_PATH_THEMES' => '/'
              ]
            )
          ]
        )
      );
      $this->assertInstanceOf(
        ContentStructure::class,
        $handler->getDefinition('TestData')
      );
    }

    public function testGetThemeInPublicMode() {
      $request = $this->createMock(Request::class);
      $request
        ->expects($this->once())
        ->method('getParameter')
        ->willReturn(FALSE);
      $handler = new Handler();
      $handler->papaya(
        $this->mockPapaya()->application(
          [
            'Request' => $request,
            'Options' => $this->mockPapaya()->options(
              [
                'PAPAYA_LAYOUT_THEME' => 'theme'
              ]
            )
          ]
        )
      );
      $this->assertEquals(
        'theme',
        $handler->getTheme()
      );
    }

    public function testGetThemeInPreviewMode() {
      $request = $this->createMock(Request::class);
      $request
        ->expects($this->once())
        ->method('getParameter')
        ->willReturn(TRUE);
      $session = $this->createMock(Session::class);
      $values = $this
        ->getMockBuilder(SessionValues::class)
        ->setConstructorArgs([$session])
        ->getMock();
      $values
        ->expects($this->once())
        ->method('get')
        ->with($this->equalTo('PapayaPreviewTheme'))
        ->willReturn('ThemeFromSession');
      $session
        ->expects($this->once())
        ->method('__get')
        ->with($this->equalTo('values'))
        ->willReturn($values);
      $handler = new Handler();
      $handler->papaya(
        $this->mockPapaya()->application(
          [
            'Request' => $request,
            'Session' => $session,
            'Options' => $this->mockPapaya()->options(
              [
                'PAPAYA_LAYOUT_THEME' => 'theme'
              ]
            )
          ]
        )
      );
      $this->assertEquals(
        'ThemeFromSession',
        $handler->getTheme()
      );
    }

    public function testGetThemeSkinInPublicMode() {
      $request = $this->createMock(Request::class);
      $request
        ->expects($this->once())
        ->method('__get')
        ->with('isPreview')
        ->willReturn(FALSE);
      $handler = new Handler();
      $handler->papaya(
        $this->mockPapaya()->application(
          [
            'Request' => $request,
            'Options' => $this->mockPapaya()->options(
              [
                'PAPAYA_LAYOUT_THEME_SET' => '23 (faked)'
              ]
            )
          ]
        )
      );
      $this->assertEquals(
        '23',
        $handler->getThemeSkin()
      );
    }

    public function testGetThemeSetInPreviewMode() {
      $request = $this->createMock(Request::class);
      $request
        ->expects($this->once())
        ->method('__get')
        ->with('isPreview')
        ->willReturn(TRUE);
      $session = $this->createMock(Session::class);
      $values = $this
        ->getMockBuilder(SessionValues::class)
        ->setConstructorArgs([$session])
        ->getMock();
      $values
        ->expects($this->once())
        ->method('get')
        ->with($this->equalTo('PapayaPreviewThemeSkin'))
        ->willReturn('42 (yeah)');
      $session
        ->expects($this->once())
        ->method('__get')
        ->with($this->equalTo('values'))
        ->willReturn($values);
      $handler = new Handler();
      $handler->papaya(
        $this->mockPapaya()->application(
          [
            'Request' => $request,
            'Session' => $session,
            'Options' => $this->mockPapaya()->options(
              [
                'PAPAYA_LAYOUT_THEME_SET' => '23 (faked)'
              ]
            )
          ]
        )
      );
      $this->assertEquals(
        '42',
        $handler->getThemeSkin()
      );
    }

    public function testGetThemeSetInPublicMode() {
      $request = $this->createMock(Request::class);
      $request
        ->expects($this->once())
        ->method('__get')
        ->with('isPreview')
        ->willReturn(FALSE);
      $handler = new Handler();
      $handler->papaya(
        $this->mockPapaya()->application(
          [
            'Request' => $request,
            'Options' => $this->mockPapaya()->options(
              [
                'PAPAYA_LAYOUT_THEME_SET' => '23 (faked)'
              ]
            )
          ]
        )
      );
      $this->assertEquals(
        '23',
        $handler->getThemeSet()
      );
    }

    public function testSetThemePreview() {
      $session = $this->createMock(Session::class);
      $values = $this
        ->getMockBuilder(SessionValues::class)
        ->setConstructorArgs([$session])
        ->getMock();
      $values
        ->expects($this->once())
        ->method('set')
        ->with($this->equalTo('PapayaPreviewTheme'), $this->equalTo('Sample'));
      $session
        ->expects($this->once())
        ->method('__get')
        ->with($this->equalTo('values'))
        ->willReturn($values);
      $handler = new Handler();
      $handler->papaya(
        $this->mockPapaya()->application(['Session' => $session])
      );
      $handler->setThemePreview('Sample');
    }

    public function testRemoveThemePreview() {
      $session = $this->createMock(Session::class);
      $values = $this
        ->getMockBuilder(SessionValues::class)
        ->setConstructorArgs([$session])
        ->getMock();
      $values
        ->expects($this->once())
        ->method('set')
        ->with($this->equalTo('PapayaPreviewTheme'), $this->equalTo(NULL));
      $session
        ->expects($this->once())
        ->method('__get')
        ->with($this->equalTo('values'))
        ->willReturn($values);
      $handler = new Handler();
      $handler->papaya(
        $this->mockPapaya()->application(['Session' => $session])
      );
      $handler->removeThemePreview();
    }

    /**
     * @param string $expected
     * @param string $fileName
     * @param string $themeName
     * @param bool $validate
     * @testWith
     *   [null, ""]
     *   ["/document/root/themes/a-theme/foo.js", "foo.js"]
     *   ["/document/root/themes/example/foo.js", "foo.js", "example"]
     *   [null, "foo.js", "example", true]
     */
    public function testGetLocalThemeFile($expected, $fileName, $themeName = NULL, $validate = FALSE) {
      $_SERVER['DOCUMENT_ROOT'] = '/document/root';
      $handler = new Handler();
      $handler->papaya(
        $this->mockPapaya()->application(
          [
            'Options' => $this->mockPapaya()->options(
              [
                'PAPAYA_PATH_THEMES' => '/themes/',
                'PAPAYA_LAYOUT_THEME' => 'a-theme'
              ]
            )
          ]
        )
      );
      $this->assertSame($expected, $handler->getLocalThemeFile($fileName, $themeName, $validate));
    }
  }
}
