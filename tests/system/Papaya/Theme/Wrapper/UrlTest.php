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

use Papaya\Url;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaThemeWrapperUrlTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Theme\Wrapper\Url::__construct
  */
  public function testConstructorWithUrl() {
    $requestUrl = $this->createMock(Url::class);
    $wrapperUrl = new \Papaya\Theme\Wrapper\Url($requestUrl);
    $this->assertAttributeSame(
      $requestUrl, '_requestUrl', $wrapperUrl
    );
  }

  /**
  * @covers \Papaya\Theme\Wrapper\Url::__construct
  */
  public function testConstructorWithoutUrl() {
    $wrapperUrl = new \Papaya\Theme\Wrapper\Url();
    $this->assertAttributeInstanceOf(
      \PapayaUrlCurrent::class, '_requestUrl', $wrapperUrl
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper\Url::getMimetype
   * @dataProvider provideValidWrapperUrls
   * @param string $mimetype
   * @param string $url
   */
  public function testGetMimetypeExpectingMimeType($mimetype, $url) {
    $wrapperUrl = new \Papaya\Theme\Wrapper\Url(new Url($url));
    $this->assertEquals($mimetype, $wrapperUrl->getMimetype());
  }

  /**
   * @covers \Papaya\Theme\Wrapper\Url::getMimetype
   * @dataProvider provideInvalidWrapperUrls
   * @param string $url
   */
  public function testGetMimetypeExpectingFalse($url) {
    $wrapperUrl = new \Papaya\Theme\Wrapper\Url(new Url($url));
    $this->assertFalse($wrapperUrl->getMimetype());
  }

  /**
  * @covers \Papaya\Theme\Wrapper\Url::getThemeSet
  */
  public function testGetThemeSet() {
    $wrapperUrl = new \Papaya\Theme\Wrapper\Url(
      new Url('http://www.sample.tld/papaya-themes/theme/js?set=42')
    );
    $this->assertEquals(42, $wrapperUrl->getThemeSet());
  }

  /**
  * @covers \Papaya\Theme\Wrapper\Url::getMimetype
  */
  public function testGetThemeSetExpectingZero() {
    $wrapperUrl = new \Papaya\Theme\Wrapper\Url(
      new Url('http://www.sample.tld/papaya-themes/theme/js')
    );
    $this->assertEquals(0, $wrapperUrl->getThemeSet());
  }

  /**
  * @covers \Papaya\Theme\Wrapper\Url::parameters
  */
  public function testParametersSetParameters() {
    $parameters = $this->createMock(\Papaya\Request\Parameters::class);
    $wrapper = new \Papaya\Theme\Wrapper\Url(new Url('http://www.sample.tld'));
    $wrapper->parameters($parameters);
    $this->assertAttributeSame(
      $parameters, '_parameters', $wrapper
    );
  }

  /**
  * @covers \Papaya\Theme\Wrapper\Url::parameters
  */
  public function testParametersGetParametersAfterSet() {
    $parameters = $this->createMock(\Papaya\Request\Parameters::class);
    $wrapper = new \Papaya\Theme\Wrapper\Url(new Url('http://www.sample.tld'));
    $this->assertSame(
      $parameters, $wrapper->parameters($parameters)
    );
  }

  /**
  * @covers \Papaya\Theme\Wrapper\Url::parameters
  */
  public function testParametersGetParametersImplicitCreate() {
    $wrapper = new \Papaya\Theme\Wrapper\Url(new Url('http://www.sample.tld?foo=bar'));
    $parameters = $wrapper->parameters();
    $this->assertInstanceOf(\Papaya\Request\Parameters::class, $parameters);
    $this->assertEquals(array('foo' => 'bar'), $parameters->toArray());
  }

  /**
  * @covers \Papaya\Theme\Wrapper\Url::getFiles
  */
  public function testGetFiles() {
    $wrapperUrl = new \Papaya\Theme\Wrapper\Url(
      new Url('http://www.sample.tld/papaya-themes/theme/js?files=foo,bar&rev=42')
    );
    $this->assertEquals(
      array('foo', 'bar'), $wrapperUrl->getFiles()
    );
  }

  /**
  * @covers \Papaya\Theme\Wrapper\Url::getGroup
  */
  public function testGetGroup() {
    $wrapperUrl = new \Papaya\Theme\Wrapper\Url(
      new Url('http://www.sample.tld/papaya-themes/theme/js?group=foo&rev=42')
    );
    $this->assertEquals(
      'foo', $wrapperUrl->getGroup()
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper\Url::getTheme
   * @dataProvider provideThemesFromUrl
   * @param string $theme
   * @param string $url
   */
  public function testGetTheme($theme, $url) {
    $wrapperUrl = new \Papaya\Theme\Wrapper\Url(
      new Url($url)
    );
    $this->assertEquals(
      $theme, $wrapperUrl->getTheme()
    );
  }

  /**
  * @covers \Papaya\Theme\Wrapper\Url::allowDirectories
  */
  public function testAllowDirectoriesExpectingTrue() {
    $wrapperUrl = new \Papaya\Theme\Wrapper\Url(
      new Url('http://www.sample.tld/papaya-themes/theme/css?rec=yes')
    );
    $this->assertTrue($wrapperUrl->allowDirectories());
  }

  /**
  * @covers \Papaya\Theme\Wrapper\Url::allowDirectories
  */
  public function testAllowDirectoriesExpectingFalse() {
    $wrapperUrl = new \Papaya\Theme\Wrapper\Url(
      new Url('http://www.sample.tld/papaya-themes/theme/css')
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
