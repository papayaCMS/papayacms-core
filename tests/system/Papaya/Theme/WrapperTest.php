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

namespace Papaya\Theme;
require_once __DIR__.'/../../../bootstrap.php';

class WrapperTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Theme\Wrapper::__construct
   */
  public function testConstructorWithUrl() {
    $wrapperUrl = $this->createMock(Wrapper\URL::class);
    $wrapper = new Wrapper($wrapperUrl);
    $this->assertSame(
      $wrapperUrl, $wrapper->getUrl()
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper::__construct
   */
  public function testConstructorWithoutUrl() {
    $wrapper = new Wrapper();
    $this->assertNotNull(
      $wrapper->getUrl()
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper::group
   */
  public function testGroupGetAfterSet() {
    $group = $this
      ->getMockBuilder(Wrapper\Group::class)
      ->disableOriginalConstructor()
      ->getMock();
    $wrapper = new Wrapper();
    $this->assertSame($group, $wrapper->group($group));
  }

  /**
   * @covers \Papaya\Theme\Wrapper::group
   */
  public function testGroupImplicitCreate() {
    $handler = $this->createMock(Handler::class);
    $handler
      ->expects($this->once())
      ->method('getLocalThemePath')
      ->will($this->returnValue(__DIR__.'/TestData/'));
    $wrapper = new Wrapper();
    $wrapper->handler($handler);
    $this->assertInstanceOf(
      Wrapper\Group::class, $wrapper->group()
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper::handler
   */
  public function testHandlerGetHandlerAfterSet() {
    $handler = $this->createMock(Handler::class);
    $wrapper = new Wrapper();
    $this->assertSame(
      $handler, $wrapper->handler($handler)
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper::handler
   */
  public function testHandlerGetHandlerImplicitCreate() {
    $application = $this->mockPapaya()->application();
    $wrapper = new Wrapper();
    $wrapper->papaya($application);
    $handler = $wrapper->handler();
    $this->assertInstanceOf(Handler::class, $handler);
    $this->assertSame($application, $handler->papaya());
  }

  /**
   * @covers \Papaya\Theme\Wrapper::themeSet
   */
  public function testThemeSetGetAfterSet() {
    $themeSet = $this->createMock(\Papaya\Content\Theme\Skin::class);
    $wrapper = new Wrapper();
    $this->assertSame(
      $themeSet, $wrapper->themeSet($themeSet)
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper::themeSet
   */
  public function testThemeSetGetHandlerImplicitCreate() {
    $application = $this->mockPapaya()->application();
    $wrapper = new Wrapper();
    $wrapper->papaya($application);
    $themeSet = $wrapper->themeSet();
    $this->assertInstanceOf(\Papaya\Content\Theme\Skin::class, $themeSet);
    $this->assertSame($application, $themeSet->papaya());
  }

  /**
   * @covers \Papaya\Theme\Wrapper::templateEngine
   */
  public function testTemplateEngineGetAfterSet() {
    $engine = $this->createMock(\Papaya\Template\Engine::class);
    $wrapper = new Wrapper();
    $this->assertSame(
      $engine, $wrapper->templateEngine($engine)
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper::templateEngine
   */
  public function testTemplateEngineGetWithoutSetExpectingNull() {
    $wrapper = new Wrapper();
    $this->assertNull($wrapper->templateEngine());
  }

  /**
   * @covers \Papaya\Theme\Wrapper::cache
   */
  public function testCacheGetCacheAfterSet() {
    $service = $this->createMock(\Papaya\Cache\Service::class);
    $wrapper = new Wrapper();
    $this->assertSame(
      $service, $wrapper->cache($service)
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper::cache
   */
  public function testCacheGetCacheImplicitCreate() {
    $wrapper = new Wrapper();
    $wrapper->papaya($this->mockPapaya()->application());
    $service = $wrapper->cache();
    $this->assertInstanceOf(\Papaya\Cache\Service::class, $service);
  }

  /**
   * @covers \Papaya\Theme\Wrapper::getCompiledContent
   * @dataProvider provideFilesToCompileContent
   * @param string $content
   * @param array $files
   */
  public function testGetCompiledContent($content, array $files) {
    $handler = $this->createMock(Handler::class);
    $handler
      ->expects($this->once())
      ->method('getLocalThemePath')
      ->with('theme')
      ->will($this->returnValue(__DIR__.'/TestData/'));
    $wrapper = new Wrapper();
    $wrapper->handler($handler);
    $this->assertEquals(
      $content, $wrapper->getCompiledContent('theme', 0, $files, FALSE)
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper::getCompiledContent
   */
  public function testGetCompiledContentCompressed() {
    if (!function_exists('gzencode')) {
      $this->markTestSkipped('Compression not available.');
    }
    $handler = $this->createMock(Handler::class);
    $handler
      ->expects($this->once())
      ->method('getLocalThemePath')
      ->with('theme')
      ->will($this->returnValue(__DIR__.'/TestData/'));
    $wrapper = new Wrapper();
    $wrapper->handler($handler);
    /** @noinspection PhpComposerExtensionStubsInspection */
    $this->assertEquals(
      gzencode(''),
      $wrapper->getCompiledContent('theme', 0, array(), TRUE)
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper::getCompiledContent
   */
  public function testGetCompiledContentUsingTemplates() {
    $engine = $this->createMock(\Papaya\Template\Engine::class);
    $engine
      ->expects($this->once())
      ->method('setTemplateString')
      ->with('.sample {}');
    $engine
      ->expects($this->once())
      ->method('prepare');
    $engine
      ->expects($this->once())
      ->method('run');
    $engine
      ->expects($this->once())
      ->method('getResult')
      ->will($this->returnValue('SUCCESS'));
    $themeSet = $this->createMock(\Papaya\Content\Theme\Skin::class);
    $themeSet
      ->expects($this->once())
      ->method('load')
      ->will($this->returnValue(TRUE));

    $handler = $this->createMock(Handler::class);
    $handler
      ->expects($this->any())
      ->method('getLocalThemePath')
      ->with('theme')
      ->will($this->returnValue(__DIR__.'/TestData/'));
    $handler
      ->expects($this->any())
      ->method('getDefinition')
      ->willReturn($this->createMock(\Papaya\Theme\Definition::class));

    $wrapper = new Wrapper();
    $wrapper->handler($handler);
    $wrapper->templateEngine($engine);
    $wrapper->themeSet($themeSet);
    $this->assertEquals(
      'SUCCESS', $wrapper->getCompiledContent('theme', 42, array('wrapperTest.css'), FALSE)
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper::getFiles
   * @covers \Papaya\Theme\Wrapper::prepareFileName
   * @dataProvider provideFileListsForValidation
   * @param $validated
   * @param $files
   * @param $mimetype
   * @param $allowDirectories
   */
  public function testGetFiles($validated, $files, $mimetype, $allowDirectories) {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Wrapper\URL $wrapperUrl */
    $wrapperUrl = $this->createMock(Wrapper\URL::class);
    $wrapperUrl
      ->expects($this->once())
      ->method('getMimetype')
      ->will($this->returnValue($mimetype));
    $wrapperUrl
      ->expects($this->any())
      ->method('allowDirectories')
      ->will($this->returnValue($allowDirectories));
    $wrapperUrl
      ->expects($this->once())
      ->method('getGroup')
      ->will($this->returnValue(NULL));
    $wrapperUrl
      ->expects($this->once())
      ->method('getFiles')
      ->will($this->returnValue($files));
    $wrapper = new Wrapper($wrapperUrl);
    $this->assertEquals(
      $validated, $wrapper->getFiles()
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper::getFiles
   */
  public function testGetFilesUsingGroup() {
    $wrapperUrl = $this->createMock(Wrapper\URL::class);
    $wrapperUrl
      ->expects($this->once())
      ->method('getMimetype')
      ->will($this->returnValue('text/css'));
    $wrapperUrl
      ->expects($this->once())
      ->method('allowDirectories')
      ->will($this->returnValue(FALSE));
    $wrapperUrl
      ->expects($this->once())
      ->method('getGroup')
      ->will($this->returnValue('main'));
    $group = $this
      ->getMockBuilder(Wrapper\Group::class)
      ->setConstructorArgs(array('theme.xml'))
      ->getMock();
    $group
      ->expects($this->once())
      ->method('allowDirectories')
      ->with($this->equalTo('main'), $this->equalTo('css'))
      ->will($this->returnValue(TRUE));
    $group
      ->expects($this->once())
      ->method('getFiles')
      ->with($this->equalTo('main'), $this->equalTo('css'))
      ->will($this->returnValue(array('sample')));
    $wrapper = new Wrapper($wrapperUrl);
    $wrapper->group($group);
    $this->assertEquals(
      array('sample.css'), $wrapper->getFiles()
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper::getFiles
   */
  public function testGetFilesUsingGroupRecursionByUrl() {
    $wrapperUrl = $this->createMock(Wrapper\URL::class);
    $wrapperUrl
      ->expects($this->once())
      ->method('getMimetype')
      ->will($this->returnValue('text/css'));
    $wrapperUrl
      ->expects($this->once())
      ->method('allowDirectories')
      ->will($this->returnValue(TRUE));
    $wrapperUrl
      ->expects($this->once())
      ->method('getGroup')
      ->will($this->returnValue('main'));
    $group = $this
      ->getMockBuilder(Wrapper\Group::class)
      ->setConstructorArgs(array('theme.xml'))
      ->getMock();
    $group
      ->expects($this->once())
      ->method('getFiles')
      ->with($this->equalTo('main'), $this->equalTo('css'))
      ->will($this->returnValue(array('sample')));
    $wrapper = new Wrapper($wrapperUrl);
    $wrapper->group($group);
    $this->assertEquals(
      array('sample.css'), $wrapper->getFiles()
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper::getFiles
   */
  public function testGetFilesWithEmptyMimeType() {
    $wrapperUrl = $this->createMock(Wrapper\URL::class);
    $wrapperUrl
      ->expects($this->once())
      ->method('getMimetype')
      ->will($this->returnValue(''));
    $wrapper = new Wrapper($wrapperUrl);
    $this->assertEquals(
      array(), $wrapper->getFiles()
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper::getCacheIdentifier
   * @dataProvider provideDataForCacheIdentifiers
   * @param string $expected
   * @param int $themeSetId
   * @param array $files
   * @param string $mimetype
   * @param bool $compress
   */
  public function testGetCacheIdentifier($expected, $themeSetId, array $files, $mimetype, $compress) {
    $wrapper = new Wrapper();
    $wrapper->papaya($this->mockPapaya()->application());
    $this->assertEquals(
      $expected, $wrapper->getCacheIdentifier($themeSetId, $files, $mimetype, $compress)
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper::getResponse
   */
  public function testGetResponse() {
    $wrapper = new Wrapper(
      new Wrapper\URL(
        new \Papaya\URL('http://www.sample.tld/theme/css?files=wrapperTest')
      )
    );
    $wrapper->papaya($this->getResponseApplicationFixture(array(), FALSE));
    $wrapper->handler($this->getThemeHandlerFixture());
    $response = $wrapper->getResponse();
    $this->assertEquals(
      array(
        'Cache-Control' =>
          'no-store, no-cache, must-revalidate, post-check=0, pre-check=0, no-transform',
        'Pragma' => 'no-cache',
        'Expires' => 'Thu, 19 Nov 1981 08:52:00 GMT',
        'Content-Type' => 'text/css; charset=UTF-8'
      ),
      iterator_to_array($response->headers())
    );
    $this->assertEquals(
      '.sample {}',
      (string)$response->content()
    );
    $this->assertEquals(
      200, $response->getStatus()
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper::getResponse
   */
  public function testGetResponseCompressed() {
    $wrapper = new Wrapper(
      new Wrapper\URL(
        new \Papaya\URL('http://www.sample.tld/theme/css?files=wrapperTest')
      )
    );
    $wrapper->papaya($this->getResponseApplicationFixture(array(), TRUE));
    $wrapper->handler($this->getThemeHandlerFixture());
    $response = $wrapper->getResponse();
    $this->assertEquals(
      array(
        'Cache-Control' =>
          'no-store, no-cache, must-revalidate, post-check=0, pre-check=0, no-transform',
        'Pragma' => 'no-cache',
        'Expires' => 'Thu, 19 Nov 1981 08:52:00 GMT',
        'X-Papaya-Compress' => 'yes',
        'Content-Encoding' => 'gzip',
        'Content-Type' => 'text/css; charset=UTF-8'
      ),
      iterator_to_array($response->headers())
    );
    /** @noinspection PhpComposerExtensionStubsInspection */
    $this->assertEquals(
      gzencode('.sample {}'),
      (string)$response->content()
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper::getResponse
   */
  public function testGetResponseWriteCache() {
    $cache = $this->createMock(\Papaya\Cache\Service::class);
    $cache
      ->expects($this->once())
      ->method('created')
      ->with(
        'theme', 'test', '42_css_b6f46cc11375a7aa9899b0fdd5a926c6', $this->greaterThan(0)
      )
      ->will($this->returnValue(time() - 900));
    $cache
      ->expects($this->once())
      ->method('read')
      ->with(
        'theme', 'test', '42_css_b6f46cc11375a7aa9899b0fdd5a926c6', $this->greaterThan(0)
      )
      ->will($this->returnValue(FALSE));
    $cache
      ->expects($this->once())
      ->method('write')
      ->with(
        'theme',
        'test',
        '42_css_b6f46cc11375a7aa9899b0fdd5a926c6',
        '.sample {}',
        $this->greaterThan(0)
      );
    $wrapper = new Wrapper(
      new Wrapper\URL(
        new \Papaya\URL('http://www.sample.tld/test/css?files=wrapperTest')
      )
    );
    $wrapper->papaya(
      $this->getResponseApplicationFixture(
        array(
          'PAPAYA_CACHE_THEMES' => TRUE,
          'PAPAYA_CACHE_TIME_THEMES' => 1800,
          'PAPAYA_WEBSITE_REVISION' => 42
        ),
        FALSE
      )
    );
    $wrapper->handler($this->getThemeHandlerFixture());
    $wrapper->cache($cache);
    $response = $wrapper->getResponse();
    $headers = $response->headers();
    $this->assertEquals(
      'public, max-age=1800, pre-check=1800, no-transform', $headers['Cache-Control']
    );
    $this->assertEquals(
      '42_css_b6f46cc11375a7aa9899b0fdd5a926c6', $headers['Etag']
    );
    $this->assertEquals(
      '.sample {}',
      (string)$response->content()
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper::getResponse
   */
  public function testGetResponseReadCache() {
    $cache = $this->createMock(\Papaya\Cache\Service::class);
    $cache
      ->expects($this->once())
      ->method('created')
      ->with(
        'theme', 'test', '42_css_b6f46cc11375a7aa9899b0fdd5a926c6', $this->greaterThan(0)
      )
      ->will($this->returnValue(time() - 900));
    $cache
      ->expects($this->once())
      ->method('read')
      ->with(
        'theme', 'test', '42_css_b6f46cc11375a7aa9899b0fdd5a926c6', $this->greaterThan(0)
      )
      ->will($this->returnValue('CACHED CSS'));
    $wrapper = new Wrapper(
      new Wrapper\URL(
        new \Papaya\URL('http://www.sample.tld/test/css?files=wrapperTest')
      )
    );
    $wrapper->papaya(
      $this->getResponseApplicationFixture(
        array(
          'PAPAYA_CACHE_THEMES' => TRUE,
          'PAPAYA_CACHE_TIME_THEMES' => 1800,
          'PAPAYA_WEBSITE_REVISION' => 42
        ),
        FALSE
      )
    );
    $wrapper->handler($this->getThemeHandlerFixture());
    $wrapper->cache($cache);
    $response = $wrapper->getResponse();
    $headers = $response->headers();
    $this->assertEquals(
      '42_css_b6f46cc11375a7aa9899b0fdd5a926c6', $headers['Etag']
    );
    $this->assertEquals(
      'CACHED CSS',
      (string)$response->content()
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper::getResponse
   */
  public function testGetResponseUseBrowserCache() {
    $cache = $this->createMock(\Papaya\Cache\Service::class);
    $cache
      ->expects($this->once())
      ->method('created')
      ->with(
        'theme', 'test', '42_css_b6f46cc11375a7aa9899b0fdd5a926c6', $this->greaterThan(0)
      )
      ->will($this->returnValue(time() - 900));
    $wrapper = new Wrapper(
      new Wrapper\URL(
        new \Papaya\URL('http://www.sample.tld/test/css?files=wrapperTest')
      )
    );
    $wrapper->papaya(
      $this->getResponseApplicationFixture(
        array(
          'PAPAYA_CACHE_THEMES' => TRUE,
          'PAPAYA_CACHE_TIME_THEMES' => 1800,
          'PAPAYA_WEBSITE_REVISION' => 42
        ),
        FALSE,
        TRUE
      )
    );
    $wrapper->handler($this->getThemeHandlerFixture());
    $wrapper->cache($cache);
    $response = $wrapper->getResponse();
    $this->assertEquals(
      304, $response->getStatus()
    );
  }

  /**************************
   * Fixtures
   ***************************/

  /**
   * @param array $options
   * @param bool $allowCompression
   * @param bool $browserCache
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\Application
   */
  public function getResponseApplicationFixture(
    array $options = array(), $allowCompression = FALSE, $browserCache = FALSE
  ) {
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('allowCompression')
      ->will($this->returnValue($allowCompression));
    $request
      ->expects($this->any())
      ->method('validateBrowserCache')
      ->withAnyParameters()
      ->will($this->returnValue($browserCache));
    return $this->mockPapaya()->application(
      array(
        'Request' => $request,
        'Options' => $this->mockPapaya()->options($options)
      )
    );
  }

  /**
   * @return \PHPUnit_Framework_MockObject_MockObject|Handler
   */
  public function getThemeHandlerFixture() {
    $handler = $this->createMock(Handler::class);
    $handler
      ->expects($this->any())
      ->method('getLocalThemePath')
      ->will($this->returnValue(__DIR__.'/TestData/'));
    return $handler;
  }


  /**************************
   * Data Provider
   ***************************/

  public static function provideFileListsForValidation() {
    return array(
      array(
        array('sample.css'),
        array('sample'),
        'text/css',
        FALSE
      ),
      array(
        array('sample.css'),
        array('sample.css'),
        'text/css',
        FALSE
      ),
      array(
        array('sample.js.css'),
        array('sample.js'),
        'text/css',
        FALSE
      ),
      array(
        array('sample.js'),
        array('sample.js'),
        'text/javascript',
        FALSE
      ),
      array(
        array('sample.css.js'),
        array('sample.css'),
        'text/javascript',
        FALSE
      ),
      array(
        array('path/sample.css'),
        array('path/sample'),
        'text/css',
        TRUE
      ),
      array(
        array('path/sample.css'),
        array('path/sample.css'),
        'text/css',
        TRUE
      ),
      array(
        array('path/sample.js'),
        array('path/sample'),
        'text/javascript',
        FALSE
      ),
      array(
        array(),
        array('path/sample'),
        'text/css',
        FALSE
      ),
      array(
        array(),
        array('./sample'),
        'text/css',
        TRUE
      ),
      array(
        array('papaya/jquery-1.7.2.min.js', 'papaya/jquery.papayaPopUp.js'),
        array('papaya/jquery-1.7.2.min', 'papaya/jquery.papayaPopUp'),
        'text/javascript',
        FALSE
      )
    );
  }

  public static function provideFilesToCompileContent() {
    return array(
      array(
        "\n/* Missing file: NONEXISTING.css */\n",
        array('NONEXISTING.css')
      ),
      array(
        '.sample {}',
        array('wrapperTest.css')
      ),
      array(
        "\n/* Adding file: wrapperTest.css */\n.sample {}".
        "\n/* Adding file: wrapperTest.css */\n.sample {}",
        array('wrapperTest.css', 'wrapperTest.css')
      ),
      array(
        "\n/* Missing file: NONEXISTING.css */\n".
        "\n/* Adding file: wrapperTest.css */\n.sample {}",
        array('NONEXISTING.css', 'wrapperTest.css')
      )
    );
  }

  public static function provideDataForCacheIdentifiers() {
    return array(
      array(
        'dev_css_76a2427c00d5443d3fc4b363fbf7dc10',
        0,
        array('sample.css'),
        'text/css',
        FALSE
      ),
      array(
        'dev_css_76a2427c00d5443d3fc4b363fbf7dc10.gz',
        0,
        array('sample.css'),
        'text/css',
        TRUE
      ),
      array(
        'dev_js_388640f712e890c6bf1d7676144c67e9',
        0,
        array('sample.js'),
        'text/javascript',
        FALSE
      ),
      array(
        'dev_js_388640f712e890c6bf1d7676144c67e9.gz',
        0,
        array('sample.js'),
        'text/javascript',
        TRUE
      ),
      array(
        '42_dev_js_388640f712e890c6bf1d7676144c67e9.gz',
        42,
        array('sample.js'),
        'text/javascript',
        TRUE
      ),
    );
  }
}
