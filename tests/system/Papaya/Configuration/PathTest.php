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

namespace Papaya\CMS\Configuration;

use Papaya\CMS\Theme\Handler as ThemeHandler;

require_once __DIR__.'/../../../bootstrap.php';

/**
 * @covers \Papaya\CMS\Configuration\Path
 */
class PathTest extends \Papaya\TestCase {

  /**
   * @backupGlobals enabled
   * @dataProvider providePathSamples
   * @param string $expected
   * @param string $identifier
   * @param string $subPath
   */
  public function testPathAsString($expected, $identifier, $subPath) {
    $path = new Path($identifier, $subPath);
    $_SERVER['DOCUMENT_ROOT'] = '/document/root';
    $path->papaya(
      $this->mockPapaya()->application(
        array(
          'options' => $this->mockPapaya()->options(
            array(
              'PAPAYA_PATH_WEB' => '/web/',
              'PAPAYA_PATH_ADMIN' => '/admin/',
              'PAPAYA_PATH_DATA' => '/data/'
            )
          )
        )
      )
    );
    $this->assertEquals(
      $expected, (string)$path
    );
  }

  public function testPathThemeCallsThemeHandler() {
    $themeHandler = $this->createMock(ThemeHandler::class);
    $themeHandler
      ->expects($this->once())
      ->method('getLocalPath')
      ->will($this->returnValue('/success/'));
    $path = new Path(Path::PATH_THEMES, 'sample');
    $path->themeHandler($themeHandler);
    $this->assertEquals(
      '/success/sample/', (string)$path
    );
  }

  public function testPathCurrentThemeCallsThemeHandler() {
    $themeHandler = $this->createMock(ThemeHandler::class);
    $themeHandler
      ->expects($this->once())
      ->method('getLocalThemePath')
      ->will($this->returnValue('/success/theme/'));
    $path = new Path(Path::PATH_THEME_CURRENT, 'sample');
    $path->themeHandler($themeHandler);
    $this->assertEquals(
      '/success/theme/sample/', (string)$path
    );
  }

  public function testThemeHandlerGetAfterSet() {
    $path = new Path('', '');
    $path->themeHandler($handler = $this->createMock(ThemeHandler::class));
    $this->assertSame($handler, $path->themeHandler());
  }

  public function testThemeHandlerGetImplicitCreate() {
    $path = new Path('', '');
    $this->assertInstanceOf(ThemeHandler::class, $path->themeHandler());
  }

  public function testIsIdentiferExpectingTrue() {
    $this->assertTrue(
      Path::isIdentifier(Path::PATH_INSTALLATION)
    );
  }

  public function testIsIdentiferExpectingFalse() {
    $this->assertFalse(
      Path::isIdentifier('###')
    );
  }

  public static function providePathSamples() {
    return array(
      array(
        '/subpath/',
        '',
        'subpath'
      ),
      array(
        '/path/subpath/',
        'path',
        'subpath'
      ),
      array(
        '/document/root/web/subpath/',
        Path::PATH_INSTALLATION,
        'subpath'
      ),
      array(
        '/document/root/web/admin/subpath/',
        Path::PATH_ADMINISTRATION,
        'subpath'
      ),
      array(
        '/data/subpath/',
        Path::PATH_UPLOAD,
        'subpath'
      )
    );
  }

}
