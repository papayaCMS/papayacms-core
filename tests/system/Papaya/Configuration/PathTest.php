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

namespace Papaya\Configuration;

require_once __DIR__.'/../../../bootstrap.php';

class PathTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Configuration\Path
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

  /**
   * @covers \Papaya\Configuration\Path
   */
  public function testPathThemeCallsThemeHandler() {
    $themeHandler = $this->createMock(\Papaya\Theme\Handler::class);
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

  /**
   * @covers \Papaya\Configuration\Path
   */
  public function testPathCurrentThemeCallsThemeHandler() {
    $themeHandler = $this->createMock(\Papaya\Theme\Handler::class);
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

  /**
   * @covers \Papaya\Configuration\Path::themeHandler
   */
  public function testThemeHandlerGetAfterSet() {
    $path = new Path('', '');
    $path->themeHandler($handler = $this->createMock(\Papaya\Theme\Handler::class));
    $this->assertSame($handler, $path->themeHandler());
  }

  /**
   * @covers \Papaya\Configuration\Path::themeHandler
   */
  public function testThemeHandlerGetImplicitCreate() {
    $path = new Path('', '');
    $this->assertInstanceOf(\Papaya\Theme\Handler::class, $path->themeHandler());
  }

  /**
   * @covers \Papaya\Configuration\Path::isIdentifier
   */
  public function testIsIdentiferExpectingTrue() {
    $this->assertTrue(
      Path::isIdentifier(Path::PATH_INSTALLATION)
    );
  }

  /**
   * @covers \Papaya\Configuration\Path::isIdentifier
   */
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
