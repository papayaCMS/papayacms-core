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

namespace Papaya\Theme\Wrapper;

require_once __DIR__.'/../../../../bootstrap.php';

class URLTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Theme\Wrapper\URL::__construct
   */
  public function testConstructorWithUrl() {
    $requestUrl = $this->createMock(\Papaya\URL::class);
    $wrapperUrl = new URL($requestUrl);
    $this->assertAttributeSame(
      $requestUrl, '_requestURL', $wrapperUrl
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper\URL::__construct
   */
  public function testConstructorWithoutUrl() {
    $wrapperUrl = new URL();
    $this->assertAttributeInstanceOf(
      \Papaya\URL\Current::class, '_requestURL', $wrapperUrl
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper\URL::getMimetype
   * @dataProvider provideValidWrapperUrls
   * @param string $mimetype
   * @param string $url
   */
  public function testGetMimetypeExpectingMimeType($mimetype, $url) {
    $wrapperUrl = new URL(new \Papaya\URL($url));
    $this->assertEquals($mimetype, $wrapperUrl->getMimetype());
  }

  /**
   * @covers \Papaya\Theme\Wrapper\URL::getMimetype
   * @dataProvider provideInvalidWrapperUrls
   * @param string $url
   */
  public function testGetMimetypeExpectingFalse($url) {
    $wrapperUrl = new URL(new \Papaya\URL($url));
    $this->assertFalse($wrapperUrl->getMimetype());
  }

  /**
   * @covers \Papaya\Theme\Wrapper\URL::getThemeSet
   */
  public function testGetThemeSet() {
    $wrapperUrl = new URL(
      new \Papaya\URL('http://www.sample.tld/papaya-themes/theme/js?set=42')
    );
    $this->assertEquals(42, $wrapperUrl->getThemeSet());
  }

  /**
   * @covers \Papaya\Theme\Wrapper\URL::getMimetype
   */
  public function testGetThemeSetExpectingZero() {
    $wrapperUrl = new URL(
      new \Papaya\URL('http://www.sample.tld/papaya-themes/theme/js')
    );
    $this->assertEquals(0, $wrapperUrl->getThemeSet());
  }

  /**
   * @covers \Papaya\Theme\Wrapper\URL::parameters
   */
  public function testParametersSetParameters() {
    $parameters = $this->createMock(\Papaya\Request\Parameters::class);
    $wrapper = new URL(new \Papaya\URL('http://www.sample.tld'));
    $wrapper->parameters($parameters);
    $this->assertAttributeSame(
      $parameters, '_parameters', $wrapper
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper\URL::parameters
   */
  public function testParametersGetParametersAfterSet() {
    $parameters = $this->createMock(\Papaya\Request\Parameters::class);
    $wrapper = new URL(new \Papaya\URL('http://www.sample.tld'));
    $this->assertSame(
      $parameters, $wrapper->parameters($parameters)
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper\URL::parameters
   */
  public function testParametersGetParametersImplicitCreate() {
    $wrapper = new URL(new \Papaya\URL('http://www.sample.tld?foo=bar'));
    $parameters = $wrapper->parameters();
    $this->assertInstanceOf(\Papaya\Request\Parameters::class, $parameters);
    $this->assertEquals(array('foo' => 'bar'), $parameters->toArray());
  }

  /**
   * @covers \Papaya\Theme\Wrapper\URL::getFiles
   */
  public function testGetFiles() {
    $wrapperUrl = new URL(
      new \Papaya\URL('http://www.sample.tld/papaya-themes/theme/js?files=foo,bar&rev=42')
    );
    $this->assertEquals(
      array('foo', 'bar'), $wrapperUrl->getFiles()
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper\URL::getGroup
   */
  public function testGetGroup() {
    $wrapperUrl = new URL(
      new \Papaya\URL('http://www.sample.tld/papaya-themes/theme/js?group=foo&rev=42')
    );
    $this->assertEquals(
      'foo', $wrapperUrl->getGroup()
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper\URL::getTheme
   * @dataProvider provideThemesFromUrl
   * @param string $theme
   * @param string $url
   */
  public function testGetTheme($theme, $url) {
    $wrapperUrl = new URL(
      new \Papaya\URL($url)
    );
    $this->assertEquals(
      $theme, $wrapperUrl->getTheme()
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper\URL::allowDirectories
   */
  public function testAllowDirectoriesExpectingTrue() {
    $wrapperUrl = new URL(
      new \Papaya\URL('http://www.sample.tld/papaya-themes/theme/css?rec=yes')
    );
    $this->assertTrue($wrapperUrl->allowDirectories());
  }

  /**
   * @covers \Papaya\Theme\Wrapper\URL::allowDirectories
   */
  public function testAllowDirectoriesExpectingFalse() {
    $wrapperUrl = new URL(
      new \Papaya\URL('http://www.sample.tld/papaya-themes/theme/css')
    );
    $this->assertFalse($wrapperUrl->allowDirectories());
  }

  /***************************
   * DataProvider
   ***************************/

  public static function provideValidWrapperUrls() {
    return array(
      array(
        'text/css',
        'http://www.sample.tld/papaya-themes/theme/css.php'
      ),
      array(
        'text/css',
        'http://www.sample.tld/papaya-themes/theme/css'
      ),
      array(
        'text/css',
        'http://www.sample.tld/papaya-themes/theme/css.php?files=sample.css'
      ),
      array(
        'text/css',
        'http://www.sample.tld/papaya-themes/theme/css?files=sample.css'
      ),
      array(
        'text/css',
        'http://www.sample.tld/papaya-themes/theme/css?files=sample'
      ),
      array(
        'text/css',
        'http://www.sample.tld/papaya-themes/theme/css?files=foo,bar'
      ),
      array(
        'text/css',
        'http://www.sample.tld/papaya-themes/theme/css?files=foo,bar&rev=42'
      ),
      array(
        'text/javascript',
        'http://www.sample.tld/papaya-themes/theme/js.php'
      ),
      array(
        'text/javascript',
        'http://www.sample.tld/papaya-themes/theme/js'
      ),
      array(
        'text/javascript',
        'http://www.sample.tld/papaya-themes/theme/js.php?files=sample.js'
      ),
      array(
        'text/javascript',
        'http://www.sample.tld/papaya-themes/theme/js?files=sample.js'
      ),
      array(
        'text/javascript',
        'http://www.sample.tld/papaya-themes/theme/js?files=sample'
      ),
      array(
        'text/javascript',
        'http://www.sample.tld/papaya-themes/theme/js?files=foo,bar'
      ),
      array(
        'text/javascript',
        'http://www.sample.tld/papaya-themes/theme/js?files=foo,bar&rev=42'
      ),
      array(
        'text/javascript',
        'http://theme.sample.tld/theme/js?files=foo,bar&rev=42'
      ),
      array(
        'text/css',
        'http://theme.sample.tld/theme/css.php?files=foo,bar&rev=42'
      )
    );
  }

  public static function provideInvalidWrapperUrls() {
    return array(
      array('http://www.sample.tld/'),
      array('http://www.sample.tld/css'),
      array('http://www.sample.tld/css.php'),
      array('http://www.sample.tld/js'),
      array('http://www.sample.tld/js.php'),
      array('http://www.sample.tld/index.html'),
      array('http://www.sample.tld/index.de.html')
    );
  }

  public static function provideThemesFromUrl() {
    return array(
      array('theme', 'http://www.sample.tld/papaya-themes/theme/css')
    );
  }
}
