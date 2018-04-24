<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaConfigurationPathTest extends PapayaTestCase {

  /**
   * @covers PapayaConfigurationPath
   * @backupGlobals enabled
   * @dataProvider providePathSamples
   */
  public function testPathAsString($expected, $identifer, $subPath) {
    $path = new PapayaConfigurationPath($identifer, $subPath);
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
   * @covers PapayaConfigurationPath
   */
  public function testPathThemeCallsThemeHandler() {
    $themeHandler = $this->getMock('PapayaThemeHandler');
    $themeHandler
      ->expects($this->once())
      ->method('getLocalPath')
      ->will($this->returnValue('/success/'));
    $path = new PapayaConfigurationPath(PapayaConfigurationPath::PATH_THEMES, 'sample');
    $path->themeHandler($themeHandler);
    $this->assertEquals(
      '/success/sample/', (string)$path
    );
  }

  /**
   * @covers PapayaConfigurationPath
   */
  public function testPathCurrentThemeCallsThemeHandler() {
    $themeHandler = $this->getMock('PapayaThemeHandler');
    $themeHandler
      ->expects($this->once())
      ->method('getLocalThemePath')
      ->will($this->returnValue('/success/theme/'));
    $path = new PapayaConfigurationPath(PapayaConfigurationPath::PATH_THEME_CURRENT, 'sample');
    $path->themeHandler($themeHandler);
    $this->assertEquals(
      '/success/theme/sample/', (string)$path
    );
  }

  /**
   * @covers PapayaConfigurationPath::themeHandler
   */
  public function testThemeHandlerGetAfterSet() {
    $path = new PapayaConfigurationPath('', '');
    $path->themeHandler($handler = $this->getMock('PapayaThemeHandler'));
    $this->assertSame($handler, $path->themeHandler());
  }

  /**
   * @covers PapayaConfigurationPath::themeHandler
   */
  public function testThemeHandlerGetImplicitCreate() {
    $path = new PapayaConfigurationPath('', '');
    $this->assertInstanceOf('PapayaThemeHandler', $path->themeHandler());
  }

  /**
   * @covers PapayaConfigurationPath::isIdentifier
   */
  public function testIsIdentiferExpectingTrue() {
    $this->assertTrue(
      PapayaConfigurationPath::isIdentifier(PapayaConfigurationPath::PATH_INSTALLATION)
    );
  }

  /**
   * @covers PapayaConfigurationPath::isIdentifier
   */
  public function testIsIdentiferExpectingFalse() {
    $this->assertFalse(
      PapayaConfigurationPath::isIdentifier('###')
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
        PapayaConfigurationPath::PATH_INSTALLATION,
        'subpath'
      ),
      array(
        '/document/root/web/admin/subpath/',
        PapayaConfigurationPath::PATH_ADMINISTRATION,
        'subpath'
      ),
      array(
        '/data/subpath/',
        PapayaConfigurationPath::PATH_UPLOAD,
        'subpath'
      )
    );
  }

}
