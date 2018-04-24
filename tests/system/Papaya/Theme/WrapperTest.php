<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaThemeWrapperTest extends PapayaTestCase {

  /**
  * @covers PapayaThemeWrapper::__construct
  */
  public function testConstructorWithUrl() {
    $wrapperUrl = $this->createMock(PapayaThemeWrapperUrl::class);
    $wrapper = new PapayaThemeWrapper($wrapperUrl);
    $this->assertAttributeSame(
      $wrapperUrl, '_wrapperUrl', $wrapper
    );
  }

  /**
  * @covers PapayaThemeWrapper::__construct
  */
  public function testConstructorWithoutUrl() {
    $wrapper = new PapayaThemeWrapper();
    $this->assertAttributeInstanceOf(
      PapayaThemeWrapperUrl::class, '_wrapperUrl', $wrapper
    );
  }

  /**
  * @covers PapayaThemeWrapper::group
  */
  public function testGroupGetAfterSet() {
    $group = $this
      ->getMockBuilder(PapayaThemeWrapperGroup::class)
      ->disableOriginalConstructor()
      ->getMock();
    $wrapper = new PapayaThemeWrapper();
    $this->assertSame($group, $wrapper->group($group));
  }

  /**
  * @covers PapayaThemeWrapper::group
  */
  public function testGroupImplicitCreate() {
    $handler = $this->createMock(PapayaThemeHandler::class);
    $handler
      ->expects($this->once())
      ->method('getLocalThemePath')
      ->will($this->returnValue(dirname(__FILE__).'/TestData/'));
    $wrapper = new PapayaThemeWrapper();
    $wrapper->handler($handler);
    $this->assertInstanceOf(
      PapayaThemeWrapperGroup::class, $wrapper->group()
    );
  }

  /**
  * @covers PapayaThemeWrapper::handler
  */
  public function testHandlerSetHandler() {
    $handler = $this->createMock(PapayaThemeHandler::class);
    $wrapper = new PapayaThemeWrapper();
    $wrapper->handler($handler);
    $this->assertAttributeSame(
      $handler, '_handler', $wrapper
    );
  }

  /**
  * @covers PapayaThemeWrapper::handler
  */
  public function testHandlerGetHandlerAfterSet() {
    $handler = $this->createMock(PapayaThemeHandler::class);
    $wrapper = new PapayaThemeWrapper();
    $this->assertSame(
      $handler, $wrapper->handler($handler)
    );
  }

  /**
  * @covers PapayaThemeWrapper::handler
  */
  public function testHandlerGetHandlerImplicitCreate() {
    $application = $this->mockPapaya()->application();
    $wrapper = new PapayaThemeWrapper();
    $wrapper->papaya($application);
    $handler = $wrapper->handler();
    $this->assertInstanceOf(PapayaThemeHandler::class, $handler);
    $this->assertSame($application, $handler->papaya());
  }

  /**
  * @covers PapayaThemeWrapper::themeSet
  */
  public function testThemeSetGetAfterSet() {
    $themeSet = $this->createMock(PapayaContentThemeSet::class);
    $wrapper = new PapayaThemeWrapper();
    $this->assertSame(
      $themeSet, $wrapper->themeSet($themeSet)
    );
  }

  /**
  * @covers PapayaThemeWrapper::themeSet
  */
  public function testThemeSetGetHandlerImplicitCreate() {
    $application = $this->mockPapaya()->application();
    $wrapper = new PapayaThemeWrapper();
    $wrapper->papaya($application);
    $themeSet = $wrapper->themeSet();
    $this->assertInstanceOf(PapayaContentThemeSet::class, $themeSet);
    $this->assertSame($application, $themeSet->papaya());
  }

  /**
  * @covers PapayaThemeWrapper::templateEngine
  */
  public function testTemplateEngineGetAfterSet() {
    $engine = $this->createMock(PapayaTemplateEngine::class);
    $wrapper = new PapayaThemeWrapper();
    $this->assertSame(
      $engine, $wrapper->templateEngine($engine)
    );
  }

  /**
  * @covers PapayaThemeWrapper::templateEngine
  */
  public function testTemplateEngineGetWithoutSetExpectingNull() {
    $wrapper = new PapayaThemeWrapper();
    $this->assertNull($wrapper->templateEngine());
  }

  /**
  * @covers PapayaThemeWrapper::cache
  */
  public function testCacheSetCache() {
    $service = $this->createMock(PapayaCacheService::class);
    $wrapper = new PapayaThemeWrapper();
    $wrapper->cache($service);
    $this->assertAttributeSame(
      $service, '_cacheService', $wrapper
    );
  }

  /**
  * @covers PapayaThemeWrapper::cache
  */
  public function testCacheGetCacheAfterSet() {
    $service = $this->createMock(PapayaCacheService::class);
    $wrapper = new PapayaThemeWrapper();
    $this->assertSame(
      $service, $wrapper->cache($service)
    );
  }

  /**
  * @covers PapayaThemeWrapper::cache
  */
  public function testCacheGetCacheImplicitCreate() {
    $wrapper = new PapayaThemeWrapper();
    $wrapper->papaya($this->mockPapaya()->application());
    $service = $wrapper->cache();
    $this->assertInstanceOf(PapayaCacheService::class, $service);
  }

  /**
  * @covers PapayaThemeWrapper::getCompiledContent
  * @dataProvider provideFilesToCompileContent
  */
  public function testGetCompiledContent($content, $files) {
    $handler = $this->getMock(PapayaThemeHandler::class, array('getLocalThemePath'));
    $handler
      ->expects($this->once())
      ->method('getLocalThemePath')
      ->with('theme')
      ->will($this->returnValue(dirname(__FILE__).'/TestData/'));
    $wrapper = new PapayaThemeWrapper();
    $wrapper->handler($handler);
    $this->assertEquals(
      $content, $wrapper->getCompiledContent('theme', 0, $files, FALSE)
    );
  }

  /**
  * @covers PapayaThemeWrapper::getCompiledContent
  */
  public function testGetCompiledContentCompressed() {
    if (!function_exists('gzencode')) {
      $this->markTestSkipped('Compression not available.');
    }
    $handler = $this->getMock(PapayaThemeHandler::class, array('getLocalThemePath'));
    $handler
      ->expects($this->once())
      ->method('getLocalThemePath')
      ->with('theme')
      ->will($this->returnValue(dirname(__FILE__).'/TestData/'));
    $wrapper = new PapayaThemeWrapper();
    $wrapper->handler($handler);
    $this->assertEquals(
      gzencode(''),
      $wrapper->getCompiledContent('theme', 0, array(), TRUE)
    );
  }

  /**
  * @covers PapayaThemeWrapper::getCompiledContent
  */
  public function testGetCompiledContentUsingTemplates() {
    $engine = $this->createMock(PapayaTemplateEngine::class);
    $engine
      ->expects($this->once())
      ->method('setTemplateString')
      ->with(".sample {}");
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
    $themeSet = $this->createMock(PapayaContentThemeSet::class);
    $themeSet
      ->expects($this->once())
      ->method('load')
      ->will($this->returnValue(TRUE));

    $handler = $this->getMock(PapayaThemeHandler::class, array('getLocalThemePath'));
    $handler
      ->expects($this->any())
      ->method('getLocalThemePath')
      ->with('theme')
      ->will($this->returnValue(dirname(__FILE__).'/TestData/'));
    $wrapper = new PapayaThemeWrapper();
    $wrapper->handler($handler);
    $wrapper->templateEngine($engine);
    $wrapper->themeSet($themeSet);
    $this->assertEquals(
      'SUCCESS', $wrapper->getCompiledContent('theme', 42, array('wrapperTest.css'), FALSE)
    );
  }

  /**
  * @covers PapayaThemeWrapper::getFiles
  * @covers PapayaThemeWrapper::prepareFileName
  * @dataProvider provideFileListsForValidation
  */
  public function testGetFiles($validated, $files, $mimetype, $allowDirectories) {
    $wrapperUrl = $this->getMock(
      PapayaThemeWrapperUrl::class, array('getMimetype', 'allowDirectories', 'getGroup', 'getFiles')
    );
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
    $wrapper = new PapayaThemeWrapper($wrapperUrl);
    $this->assertEquals(
      $validated, $wrapper->getFiles()
    );
  }

  /**
  * @covers PapayaThemeWrapper::getFiles
  */
  public function testGetFilesUsingGroup() {
    $wrapperUrl = $this->getMock(
      PapayaThemeWrapperUrl::class, array('getMimetype', 'allowDirectories', 'getGroup')
    );
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
    $group = $this->getMock(
      PapayaThemeWrapperGroup::class, array('getFiles', 'allowDirectories'), array('theme.xml')
    );
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
    $wrapper = new PapayaThemeWrapper($wrapperUrl);
    $wrapper->group($group);
    $this->assertEquals(
      array('sample.css'), $wrapper->getFiles()
    );
  }

  /**
  * @covers PapayaThemeWrapper::getFiles
  */
  public function testGetFilesUsingGroupRecursionByUrl() {
    $wrapperUrl = $this->getMock(
      PapayaThemeWrapperUrl::class, array('getMimetype', 'allowDirectories', 'getGroup')
    );
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
    $group = $this->getMock(
      PapayaThemeWrapperGroup::class, array('getFiles'), array('theme.xml')
    );
    $group
      ->expects($this->once())
      ->method('getFiles')
      ->with($this->equalTo('main'), $this->equalTo('css'))
      ->will($this->returnValue(array('sample')));
    $wrapper = new PapayaThemeWrapper($wrapperUrl);
    $wrapper->group($group);
    $this->assertEquals(
      array('sample.css'), $wrapper->getFiles()
    );
  }

  /**
  * @covers PapayaThemeWrapper::getFiles
  */
  public function testGetFilesWithEmptyMimeType() {
    $wrapperUrl = $this->getMock(
      PapayaThemeWrapperUrl::class, array('getMimetype', 'allowDirectories', 'getFiles')
    );
    $wrapperUrl
      ->expects($this->once())
      ->method('getMimetype')
      ->will($this->returnValue(''));
    $wrapper = new PapayaThemeWrapper($wrapperUrl);
    $this->assertEquals(
      array(), $wrapper->getFiles()
    );
  }

  /**
  * @covers PapayaThemeWrapper::getCacheIdentifier
  * @dataProvider provideDataForCacheIdentifiers
  */
  public function testGetCacheIdentifier($expected, $themeSetId, $files, $mimetype, $compress) {
    $wrapper = new PapayaThemeWrapper();
    $wrapper->papaya($this->mockPapaya()->application());
    $this->assertEquals(
      $expected, $wrapper->getCacheIdentifier($themeSetId, $files, $mimetype, $compress)
    );
  }

  /**
  * @covers PapayaThemeWrapper::getResponse
  */
  public function testGetResponse() {
    $wrapper = new PapayaThemeWrapper(
      new PapayaThemeWrapperUrl(
        new PapayaUrl('http://www.sample.tld/theme/css?files=wrapperTest')
      )
    );
    $wrapper->papaya($this->getResponseApplicationFixture(array(), FALSE));
    $wrapper->handler($this->getThemeHandlerFixture());
    $response = $wrapper->getResponse();
    $this->assertAttributeEquals(
      array(
        'Cache-Control' =>
          'no-store, no-cache, must-revalidate, post-check=0, pre-check=0, no-transform',
        'Pragma' => 'no-cache',
        'Expires' => 'Thu, 19 Nov 1981 08:52:00 GMT',
        'Content-Type' => 'text/css; charset=UTF-8'
      ),
      '_headers',
      $response->headers()
    );
    $this->assertEquals(
      ".sample {}",
      (string)$response->content()
    );
    $this->assertAttributeEquals(
      200, '_status', $response
    );
  }

  /**
  * @covers PapayaThemeWrapper::getResponse
  */
  public function testGetResponseCompressed() {
    $wrapper = new PapayaThemeWrapper(
      new PapayaThemeWrapperUrl(
        new PapayaUrl('http://www.sample.tld/theme/css?files=wrapperTest')
      )
    );
    $wrapper->papaya($this->getResponseApplicationFixture(array(), TRUE));
    $wrapper->handler($this->getThemeHandlerFixture());
    $response = $wrapper->getResponse();
    $this->assertAttributeEquals(
      array(
        'Cache-Control' =>
          'no-store, no-cache, must-revalidate, post-check=0, pre-check=0, no-transform',
        'Pragma' => 'no-cache',
        'Expires' => 'Thu, 19 Nov 1981 08:52:00 GMT',
        'X-Papaya-Compress' => 'yes',
        'Content-Encoding' => 'gzip',
        'Content-Type' => 'text/css; charset=UTF-8'
      ),
      '_headers',
      $response->headers()
    );
    $this->assertEquals(
      gzencode(".sample {}"),
      (string)$response->content()
    );
  }

  /**
  * @covers PapayaThemeWrapper::getResponse
  */
  public function testGetResponseWriteCache() {
    $cache = $this->getMock(
      PapayaCacheService::class,
      array('exists', 'created', 'read', 'write', 'verify', 'delete', 'setConfiguration')
    );
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
        ".sample {}",
        $this->greaterThan(0)
      );
    $wrapper = new PapayaThemeWrapper(
      new PapayaThemeWrapperUrl(
        new PapayaUrl('http://www.sample.tld/test/css?files=wrapperTest')
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
      ".sample {}",
      (string)$response->content()
    );
  }

  /**
  * @covers PapayaThemeWrapper::getResponse
  */
  public function testGetResponseReadCache() {
    $cache = $this->getMock(
      PapayaCacheService::class,
      array('exists', 'created', 'read', 'write', 'verify', 'delete', 'setConfiguration')
    );
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
      ->will($this->returnValue("CACHED CSS"));
    $wrapper = new PapayaThemeWrapper(
      new PapayaThemeWrapperUrl(
        new PapayaUrl('http://www.sample.tld/test/css?files=wrapperTest')
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
      "CACHED CSS",
      (string)$response->content()
    );
  }

  /**
  * @covers PapayaThemeWrapper::getResponse
  */
  public function testGetResponseUseBrowserCache() {
    $cache = $this->getMock(
      PapayaCacheService::class,
      array('exists', 'created', 'read', 'write', 'verify', 'delete', 'setConfiguration')
    );
    $cache
      ->expects($this->once())
      ->method('created')
      ->with(
        'theme', 'test', '42_css_b6f46cc11375a7aa9899b0fdd5a926c6', $this->greaterThan(0)
      )
      ->will($this->returnValue(time() - 900));
    $wrapper = new PapayaThemeWrapper(
      new PapayaThemeWrapperUrl(
        new PapayaUrl('http://www.sample.tld/test/css?files=wrapperTest')
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
    $this->assertAttributeEquals(
      304, '_status', $response
    );
  }

  /**************************
  * Fixtures
  ***************************/

  public function getResponseApplicationFixture(array $options = array(),
                                                $allowCompression = FALSE,
                                                $browserCache = FALSE) {
    $request = $this->getMock(PapayaRequest::class, array('allowCompression', 'validateBrowserCache'));
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

  public function getThemeHandlerFixture() {
    $handler = $this->getMock(PapayaThemeHandler::class, array('getLocalThemePath'));
    $handler
      ->expects($this->any())
      ->method('getLocalThemePath')
      ->will($this->returnValue(dirname(__FILE__).'/TestData/'));
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
        ".sample {}",
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
