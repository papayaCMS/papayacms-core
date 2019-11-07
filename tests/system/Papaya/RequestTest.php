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
   * @covers \Papaya\Request
   */
  class RequestTest extends TestCase {

    public function testConstructor() {
      $request = new Request();
      $this->assertEquals(
        ':', $request->getParameterGroupSeparator()
      );
      $this->assertEquals(
        '/', $request->getBasePath()
      );
    }

    /**
     * @covers \Papaya\Request::__construct
     */
    public function testConstructorWithConfigurationArgument() {
      $options = $this->mockPapaya()->options(
        [
          'PAPAYA_URL_LEVEL_SEPARATOR' => '!',
          'PAPAYA_PATH_WEB' => '/foo/'
        ]
      );
      $request = new Request($options);
      $this->assertEquals(
        '!', $request->getParameterGroupSeparator()
      );
      $this->assertEquals(
        '/foo/', $request->getBasePath()
      );
    }

    public function testSetConfiguration() {
      $options = $this->mockPapaya()->options(
        [
          'PAPAYA_URL_LEVEL_SEPARATOR' => '!',
          'PAPAYA_PATH_WEB' => '/foo/'
        ]
      );
      $request = new Request();
      $request->setConfiguration($options);
      $this->assertEquals(
        '!', $request->getParameterGroupSeparator()
      );
      $this->assertEquals(
        '/foo/',$request->getBasePath()
      );
    }

    /**
     * @covers \Papaya\Request::getBasePath
     */
    public function testGetBasePathDefault() {
      $request = new Request();
      $this->assertEquals(
        '/', $request->getBasePath()
      );
    }

    /**
     * @covers \Papaya\Request::getBasePath
     */
    public function testGetBasePath() {
      $request = new Request();
      /** @var \PHPUnit_Framework_MockObject_MockObject|URL $url */
      $url = $this->createMock(URL::class);
      $parser = $this->createMock(Request\Parser::class);
      $parser
        ->expects($this->once())
        ->method('parse')
        ->willReturn(['session' => 'sid123456']);
      $parser
        ->expects($this->once())
        ->method('isLast')
        ->willReturn(TRUE);
      $request->setParsers(
        [$parser]
      );
      $request->load($url);
      $this->assertEquals(
        '/sid123456/',
        $request->getBasePath()
      );
    }

    public function testGetUrl() {
      $request = new Request();
      $request->papaya($this->mockPapaya()->application());
      /** @var \PHPUnit_Framework_MockObject_MockObject|URL $url */
      $url = $this->createMock(URL::class);
      $request->load($url);
      $this->assertSame(
        $url,
        $request->getURL()
      );
    }

    public function testGetUrlImplicitLoadOfCurrentUrl() {
      $request = new Request();
      $request->papaya($this->mockPapaya()->application());
      $this->assertInstanceOf(
        URL\Current::class,
        $request->getURL()
      );
    }

    public function testGetPropertyUrl() {
      $request = new Request();
      $request->papaya($this->mockPapaya()->application());
      $this->assertInstanceOf(
        URL\Current::class,
        $request->url
      );
    }

    public function testGetPropertyLanguage() {
      $request = new Request();
      $request->papaya($this->mockPapaya()->application());
      $this->assertInstanceOf(
        Content\Language::class,
        $request->language
      );
    }

    public function testGetPropertyLanguageInitializeFromParameter() {
      $request = new Request();
      $request->papaya($this->mockPapaya()->application());
      $request->setParameters(
        Request::SOURCE_PATH,
        new Request\Parameters(['language' => 'de'])
      );
      $this->assertEquals(
        [['identifier' => 'de']],
        $request->language->getLazyLoadParameters()
      );
    }

    public function testGetPropertyLanguageInitializeFromOptions() {
      $request = new Request();
      $request->papaya($this->mockPapaya()->application());
      $request->papaya(
        $this->mockPapaya()->application(
          [
            'options' => $this->mockPapaya()->options(['PAPAYA_CONTENT_LANGUAGE' => 3])
          ]
        )
      );
      $this->assertEquals(
        [['id' => 3]],
        $request->language->getLazyLoadParameters()
      );
    }

    public function testSetPropertyLanguage() {
      $request = new Request();
      $request->papaya($this->mockPapaya()->application());
      $request->language = $language = $this->createMock(Content\Language::class);
      $this->assertSame(
        $language,
        $request->language
      );
    }

    public function testGetPropertyLanguageId() {
      $language = $this->createMock(Content\Language::class);
      $language
        ->expects($this->once())
        ->method('__get')
        ->with('id')
        ->willReturn('3');

      $request = new Request();
      $request->language = $language;
      $this->assertEquals(3, $request->languageId);
    }

    public function testGetPropertyLanguageCode() {
      $language = $this->createMock(Content\Language::class);
      $language
        ->expects($this->once())
        ->method('__get')
        ->with('identifier')
        ->willReturn('en');

      $request = new Request();
      $request->language = $language;
      $this->assertEquals('en', $request->languageIdentifier);
    }

    public function testGetPropertyModeGetAfterSet() {
      $mode = $this->createMock(Content\View\Mode::class);
      $request = new Request();
      $request->mode = $mode;
      $this->assertSame($mode, $request->mode);
    }

    public function testGetPropertyModeInitializeFromParameter() {
      $request = new Request();
      $request->setParameters(
        Request::SOURCE_PATH,
        new Request\Parameters(['output_mode' => 'ext'])
      );
      $this->assertEquals(
        [['extension' => 'ext']],
        $request->mode->getLazyLoadParameters()
      );
    }

    public function testGetPropertyModeInitializeFromParameterXmlPreviewMode() {
      $request = new Request();
      $request->setParameters(
        Request::SOURCE_PATH,
        new Request\Parameters(['output_mode' => 'xml'])
      );
      $this->assertNull($request->mode->getLazyLoadParameters());
      $this->assertEquals(
        [
          'id' => -1,
          'extension' => 'xml',
          'type' => 'page',
          'charset' => 'utf-8',
          'content_type' => 'application/xml',
          'path' => '',
          'module_guid' => '',
          'session_mode' => '',
          'session_redirect' => '',
          'session_cache' => ''
        ],
        iterator_to_array($request->mode)
      );
    }

    public function testGetPropertyModeId() {
      $mode = $this->createMock(Content\View\Mode::class);
      $mode
        ->expects($this->once())
        ->method('__get')
        ->with('id')
        ->willReturn(42);

      $request = new Request();
      $request->mode = $mode;
      $this->assertEquals(42, $request->modeId);
    }

    public function testGetPropertyIsAdministrationGetAfterSet() {
      $request = new Request();
      $this->assertTrue(isset($request->isAdministration));
      $this->assertFalse($request->isAdministration);
      $request->isAdministration = TRUE;
      $this->assertTrue($request->isAdministration);
    }

    public function testUnsetPropertyExpectingException() {
      $request = new Request();
      $this->expectException(\LogicException::class);
      /** @noinspection PhpUndefinedFieldInspection */
      unset($request->isAdministration);
    }

    public function testGetInvalidPropertyExpectingException() {
      $request = new Request();
      $this->expectException(\LogicException::class);
      $this->assertFalse(isset($request->INVALID_PROPERTY));
      /** @noinspection PhpUndefinedFieldInspection */
      $request->INVALID_PROPERTY;
    }

    public function testSetInvalidPropertyExpectingException() {
      $request = new Request();
      $this->expectException(\LogicException::class);
      /** @noinspection PhpUndefinedFieldInspection */
      $request->INVALID_PROPERTY = 'fail';
    }

    public function testGetPropertyPageIdFromParameters() {
      $request = new Request();
      $request->papaya($this->mockPapaya()->application());
      $request->setParameters(
        Request::SOURCE_PATH, new Request\Parameters(['page_id' => 42])
      );
      $this->assertTrue(isset($request->pageId));
      $this->assertEquals(42, $request->pageId);
    }

    public function testGetPropertyCategoryIdFromParameters() {
      $request = new Request();
      $request->papaya($this->mockPapaya()->application());
      $request->setParameters(
        Request::SOURCE_PATH, new Request\Parameters(['category_id' => 42])
      );
      $this->assertTrue(isset($request->categoryId));
      $this->assertEquals(42, $request->categoryId);
    }

    public function testGetPropertyPageIdFromOptions() {
      $request = new Request();
      $request->papaya(
        $this->mockPapaya()->application(
          [
            'options' => $this->mockPapaya()->options(['PAPAYA_PAGEID_DEFAULT' => 42])
          ]
        )
      );
      $this->assertEquals(42, $request->pageId);
    }

    public function testGetPropertyIsPreviewFromParameters() {
      $request = new Request();
      $request->papaya($this->mockPapaya()->application());
      $request->setParameters(
        Request::SOURCE_PATH, new Request\Parameters(['preview' => TRUE])
      );
      $this->assertTrue(isset($request->isPreview));
      $this->assertTrue($request->isPreview);
    }

    public function testGetParameterGroupSeparator() {
      $request = new Request();
      $this->assertEquals(
        ':',
        $request->getParameterGroupSeparator()
      );
    }

    /**
     * @dataProvider setParameterLevelSeparatorDataProvider
     * @param string $separator
     * @param string $expected
     */
    public function testSetParameterGroupSeparator($separator, $expected) {
      $request = new Request();
      $request->setParameterGroupSeparator($separator);
      $this->assertEquals(
        $expected,
        $request->getParameterGroupSeparator()
      );
    }

    public function testSetParameterGroupSeparatorExpectingError() {
      $request = new Request();
      $this->expectException(\InvalidArgumentException::class);
      $this->expectExceptionMessage('Invalid parameter group separator: X');
      $request->setParameterGroupSeparator('X');
    }

    public function testSetParsers() {
      $parser = $this->createMock(Request\Parser::class);
      $request = new Request();
      $request->setParsers([$parser]);
      $this->assertSame(
        [$parser],
        $request->getParsers()
      );
    }

    public function _testLazyParserInitialization() {
      $request = new Request();
      $request->papaya($this->mockPapaya()->application());
      /** @var \PHPUnit_Framework_MockObject_MockObject|URL $url */
      $url = $this->createMock(URL::class);
      $request->load($url);
      $this->assertGreaterThan(
        0,
        count($request->getParsers())
      );
    }

    public function testLoad() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|URL $url */
      $url = $this->createMock(URL::class);
      $parserOne = $this->createMock(Request\Parser::class);
      $parserOne
        ->expects($this->once())
        ->method('parse')
        ->with($this->equalTo($url))
        ->willReturn(['first' => 'success']);
      $parserOne
        ->expects($this->once())
        ->method('isLast')
        ->willReturn(FALSE);
      $parserTwo = $this->createMock(Request\Parser::class);
      $parserTwo
        ->expects($this->once())
        ->method('parse')
        ->with($this->equalTo($url))
        ->willReturn(FALSE);
      $parserThree = $this->createMock(Request\Parser::class);
      $parserThree
        ->expects($this->once())
        ->method('parse')
        ->with($this->equalTo($url))
        ->willReturn(['second' => 'success']);
      $parserThree
        ->expects($this->once())
        ->method('isLast')
        ->willReturn(TRUE);
      $request = new Request();
      $request->setParsers(
        [$parserOne, $parserTwo, $parserThree]
      );
      $this->assertTrue(
        $request->load($url)
      );
      $this->assertEquals(
        [
          'first' => 'success',
          'second' => 'success'
        ],
        $request->getParameters(Request::SOURCE_PATH)->toArray()
      );
    }

    public function testLoadExpectingFalse() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|URL $url */
      $url = $this->createMock(URL::class);
      $request = new Request();
      $request->papaya($this->mockPapaya()->application());
      $this->assertFalse(
        $request->load($url)
      );
    }

    public function testGetParameterExpectingDefaultValue() {
      $request = new Request();
      $request->papaya($this->mockPapaya()->application());
      $this->assertEquals(
        'sample',
        $request->getParameter('NON_EXISITNG', 'sample')
      );
    }

    public function testGetParameter() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|URL $url */
      $url = $this->createMock(URL::class);
      $request = new Request();
      $request->papaya($this->mockPapaya()->application());
      $request->setParsers([$this->getRequestParserMockFixture()]);
      $this->assertTrue(
        $request->load($url)
      );
      $this->assertEquals(
        'success',
        $request->getParameter('PARAMETER_PATH_SAMPLE', 'fail')
      );
    }

    public function testLoadParametersPath() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|URL $url */
      $url = $this->createMock(URL::class);
      $request = new Request();
      $request->papaya($this->mockPapaya()->application());
      $request->setParsers([$this->getRequestParserMockFixture()]);
      $this->assertTrue(
        $request->load($url)
      );
      $parameters = $request->loadParameters(Request::SOURCE_PATH);
      $this->assertEquals(
        'success',
        $parameters->get('PARAMETER_PATH_SAMPLE', 'fail')
      );
    }

    public function testLoadParametersPathCached() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|URL $url */
      $url = $this->createMock(URL::class);
      $request = new Request();
      $request->papaya($this->mockPapaya()->application());
      $request->setParsers([$this->getRequestParserMockFixture()]);
      $this->assertTrue(
        $request->load($url)
      );
      $request->loadParameters(Request::SOURCE_PATH);
      $parameters = $request->loadParameters(Request::SOURCE_PATH);
      $this->assertEquals(
        'success',
        $parameters->get('PARAMETER_PATH_SAMPLE', 'fail')
      );
    }

    public function testLoadParametersQuery() {
      $request = new Request();
      $request->papaya($this->mockPapaya()->application());
      $request->load($this->getUrlMockFixture('PARAMETER_QUERY_SAMPLE=success'));
      $parameters = $request->loadParameters(Request::SOURCE_QUERY);
      $this->assertEquals(
        'success',
        $parameters->get('PARAMETER_QUERY_SAMPLE', 'fail')
      );
    }

    public function testLoadParametersBody() {
      $request = new Request();
      $request->papaya($this->mockPapaya()->application());
      $_POST = [
        'PARAMETER_POST_SAMPLE' => 'success'
      ];
      $parameters = $request->loadParameters(Request::SOURCE_BODY);
      $this->assertEquals(
        'success',
        $parameters->get('PARAMETER_POST_SAMPLE', 'fail')
      );
    }

    public function testLoadParametersAllAndPathCached() {
      $_POST = [
        'PARAMETER_POST_SAMPLE' => 'success'
      ];
      $request = new Request();
      $request->papaya($this->mockPapaya()->application());
      $request->setParsers(
        [$this->getRequestParserMockFixture()]
      );
      $request->load($this->getUrlMockFixture('PARAMETER_QUERY_SAMPLE=success'));
      $request->loadParameters(Request::SOURCE_PATH);

      $parameters = $request->loadParameters();
      $this->assertEquals(
        [
          'PARAMETER_PATH_SAMPLE' => 'success',
          'PARAMETER_QUERY_SAMPLE' => 'success',
          'PARAMETER_POST_SAMPLE' => 'success'
        ],
        $parameters->toArray()
      );
    }

    public function testLoadParametersCookie() {
      $request = new Request();
      $request->papaya($this->mockPapaya()->application());
      $_COOKIE = [
        'PARAMETER_COOKIE_SAMPLE' => 'success'
      ];
      $parameters = $request->loadParameters(Request::SOURCE_COOKIE);
      $this->assertEquals(
        'success',
        $parameters->get('PARAMETER_COOKIE_SAMPLE', 'fail')
      );
    }

    public function testGetParameters() {
      $request = new Request();
      $request->papaya($this->mockPapaya()->application());
      $request->load($this->getUrlMockFixture('group[e1]=success'));
      $parameters = $request->getParameters(Request::SOURCE_QUERY);
      $this->assertEquals(
        [
          'group' => [
            'e1' => 'success'
          ]
        ],
        $parameters->toArray()
      );
    }

    public function testGetParameterGroup() {
      $request = new Request();
      $request->papaya($this->mockPapaya()->application());
      $request->load($this->getUrlMockFixture('group[e1]=success'));
      $parameters = $request->getParameterGroup('group', Request::SOURCE_QUERY);
      $this->assertInstanceOf(
        Request\Parameters::class,
        $parameters
      );
      $this->assertEquals(
        'success',
        $parameters->get('e1')
      );
    }

    public function testSetParameters() {
      $request = new Request();
      $request->papaya($this->mockPapaya()->application());
      $request->load($this->getUrlMockFixture('PARAMETER_QUERY_SAMPLE=fail'));
      $request->getParameterGroup('PARAMETER_QUERY_SAMPLE', Request::SOURCE_ALL);
      $parameters = new Request\Parameters();
      $parameters->merge(['PARAMETER_QUERY_SAMPLE' => 'success']);
      $request->setParameters(Request::SOURCE_QUERY, $parameters);
      $this->assertEquals(
        [
          Request::SOURCE_PATH,
          Request::SOURCE_QUERY,
          Request::SOURCE_BODY
        ],
        array_keys($this->readAttribute($request, '_parameterCache'))
      );
      $this->assertSame(
        ['PARAMETER_QUERY_SAMPLE' => 'success'],
        $request->getParameters(Request::SOURCE_QUERY)->toArray()
      );
    }

    public function testSetParametersWithInvalidSource() {
      $request = new Request();
      $this->expectException(\InvalidArgumentException::class);
      $request->setParameters(Request::SOURCE_ALL, new Request\Parameters());
    }

    public function testSetParametersWithInvalidParameters() {
      $request = new Request();
      $this->expectException(\InvalidArgumentException::class);
      $request->setParameters(Request::SOURCE_QUERY, NULL);
    }

    public function testGetMethodDefault() {
      $request = new Request();
      $this->assertEquals(
        'get',
        $request->getMethod()
      );
    }

    public function testGetPropertyMethod() {
      $request = new Request();
      $this->assertEquals(
        'get',
        $request->method
      );
    }

    public function testGetMethodExpectingPost() {
      $request = new Request();
      $_SERVER['REQUEST_METHOD'] = 'post';
      $this->assertEquals(
        'post',
        $request->getMethod()
      );
    }

    public function testGetMagicQuotesStatus() {
      $request = new Request();
      $this->assertInternalType(
        'boolean',
        $request->getMagicQuotesStatus()
      );
    }

    public function testAllowCompressionWithOldProtocolExpectingFalse() {
      $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
      $request = new Request();
      $this->assertFalse($request->allowCompression());
    }

    public function testAllowCompressionExpectingTrue() {
      if (!function_exists('gzencode')) {
        $this->markTestSkipped('Zlib extension not found.');
      }
      $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
      $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip,deflate';
      $request = new Request();
      $this->assertTrue($request->allowCompression());
    }

    public function testPropertyAllowCompressionExpectingTrue() {
      if (!function_exists('gzencode')) {
        $this->markTestSkipped('Zlib extension not found.');
      }
      $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
      $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip,deflate';
      $request = new Request();
      $this->assertTrue($request->allowCompression);
    }

    public function testAllowCompressionCachedExpectingTrue() {
      if (!function_exists('gzencode')) {
        $this->markTestSkipped('Zlib extension not found.');
      }
      $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
      $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip,deflate';
      $request = new Request();
      $request->allowCompression();
      $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
      $_SERVER['HTTP_ACCEPT_ENCODING'] = NULL;
      $this->assertTrue($request->allowCompression());
    }

    public function testAllowCompressionExpectingFalse() {
      $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
      $_SERVER['HTTP_ACCEPT_ENCODING'] = NULL;
      $request = new Request();
      $this->assertFalse($request->allowCompression());
    }

    public function testAllowEsiExpectingFalse() {
      $request = new Request();
      $this->assertFalse($request->allowEsi());
    }

    public function testAllowEsiExpectingTrue() {
      $_SERVER['HTTP_X_PAPAYA_ESI'] = 'yes';
      $request = new Request();
      $this->assertTrue($request->allowEsi());
    }

    /**
     * @dataProvider provideValidBrowserCacheData
     * @backupGlobals enabled
     * @param string $eTag
     * @param string $cacheTime
     */
    public function testValidateBrowserCacheExpectingTrue($eTag, $cacheTime) {
      $_SERVER['HTTP_IF_NONE_MATCH'] = $eTag;
      $_SERVER['HTTP_IF_MODIFIED_SINCE'] = $cacheTime;
      $request = new Request();
      $this->assertTrue(
        $request->validateBrowserCache('success', gmmktime(12, 0, 0, 07, 15, 2000))
      );
    }

    /**
     * @dataProvider provideInvalidBrowserCacheData
     * @backupGlobals enabled
     * @param string $eTag
     * @param string $cacheTime
     */
    public function testValidateBrowserCacheExpectingFalse($eTag, $cacheTime) {
      $_SERVER['HTTP_IF_NONE_MATCH'] = $eTag;
      $_SERVER['HTTP_IF_MODIFIED_SINCE'] = $cacheTime;
      $request = new Request();
      $this->assertFalse(
        $request->validateBrowserCache('success', gmmktime(12, 0, 0, 07, 15, 2000))
      );
    }

    public function testContentGetSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Request\Content $content */
      $content = $this->createMock(Request\Content::class);
      $request = new Request();
      $request->content($content);
      $this->assertTrue(isset($request->content));
      $this->assertSame($content, $request->content);
    }

    public function testContentImplicitCreate() {
      $request = new Request();
      $this->assertInstanceOf(Request\Content::class, $request->content());
    }

    public function testGetPropertyContent() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Request\Content $content */
      $content = $this
        ->getMockBuilder(Request\Content::class)
        ->setMethods(['__toString'])
        ->getMock();
      $content
        ->expects($this->once())
        ->method('__toString')
        ->willReturn('test');
      $request = new Request();
      $request->content($content);
      $this->assertEquals('test', (string)$request->content);
    }

    public function testGetPropertyContentLength() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Request\Content $content */
      $content = $this->createMock(Request\Content::class);
      $content
        ->expects($this->once())
        ->method('length')
        ->willReturn(42);
      $request = new Request();
      $request->content($content);
      $this->assertTrue(isset($request->contentLength));
      $this->assertEquals(42, $request->contentLength);
    }

    /***********************************
     * Fixtures
     ***********************************/

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Request\Parser
     */
    public function getRequestParserMockFixture() {
      $parser = $this->createMock(Request\Parser::class);
      $parser
        ->expects($this->once())
        ->method('parse')
        ->willReturn(
          ['PARAMETER_PATH_SAMPLE' => 'success']
        );
      $parser
        ->expects($this->once())
        ->method('isLast')
        ->willReturn(TRUE);
      return $parser;
    }

    /**
     * @param string $queryString
     * @return \PHPUnit_Framework_MockObject_MockObject|URL
     */
    public function getUrlMockFixture($queryString = '') {
      $url = $this
        ->getMockBuilder(URL::class)
        ->setMethods(['getQuery'])
        ->getMock();
      $url
        ->method('getQuery')
        ->willReturn($queryString);
      return $url;
    }

    /***********************************
     * Data provider
     ***********************************/

    public static function setParameterLevelSeparatorDataProvider() {
      return [
        ['', '[]'],
        ['[]', '[]'],
        [',', ','],
        [':', ':'],
        ['/', '/'],
        ['*', '*'],
        ['!', '!']
      ];
    }

    /*
    * validated against etag: success, time: 2000-07-15T12:00:00+0000
    */
    public static function provideValidBrowserCacheData() {
      return [
        'greater cachetime' => ['success', '2010-07-15T12:00:00+0000'],
        'equal cachetime' => ['success', '2000-07-15T12:00:00+0000'],
        'quoted etag, equal cachetime' => ['"success"', '2000-07-15T12:00:00+0000']
      ];
    }

    /*
    * validated against etag: success, time: 42
    */
    public static function provideInvalidBrowserCacheData() {
      return [
        'smaller cachetime' => ['success', '1990-07-15T12:00:00+0000'],
        'invalid etag, equal cachetime' => ['failed', '2000-07-15T12:00:00+0000'],
        'invalid etag, greater cachetime' => ['failed', '2010-07-15T12:00:00+0000'],
        'quoted etag, smaller cachetime' => ['"success"', '1990-07-15T12:00:00+0000']
      ];
    }
  }
}

