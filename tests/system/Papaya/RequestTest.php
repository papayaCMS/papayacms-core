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

use Papaya\Content\Language;
use Papaya\Content\View\Mode;
use Papaya\Url;

require_once __DIR__.'/../../bootstrap.php';

class PapayaRequestTest extends PapayaTestCase {

  /**
  * @covers PapayaRequest::__construct
  */
  public function testConstructor() {
    $request = new PapayaRequest();
    $this->assertEquals(
      ':',
      $this->readAttribute($request, '_separator')
    );
    $this->assertEquals(
      '/',
      $this->readAttribute($request, '_installationPath')
    );
  }

  /**
  * @covers PapayaRequest::__construct
  */
  public function testConstructorWithConfigurationArgument() {
    $options = $this->mockPapaya()->options(
      array(
        'PAPAYA_URL_LEVEL_SEPARATOR' => '!',
        'PAPAYA_PATH_WEB' => '/foo/'
      )
    );
    $request = new PapayaRequest($options);
    $this->assertEquals(
      '!',
      $this->readAttribute($request, '_separator')
    );
    $this->assertEquals(
      '/foo/',
      $this->readAttribute($request, '_installationPath')
    );
  }

  /**
  * @covers PapayaRequest::setConfiguration
  */
  public function testSetConfiguration() {
    $options = $this->mockPapaya()->options(
      array(
        'PAPAYA_URL_LEVEL_SEPARATOR' => '!',
        'PAPAYA_PATH_WEB' => '/foo/'
      )
    );
    $request = new PapayaRequest;
    $request->setConfiguration($options);
    $this->assertEquals(
      '!',
      $this->readAttribute($request, '_separator')
    );
    $this->assertEquals(
      '/foo/',
      $this->readAttribute($request, '_installationPath')
    );
  }

  /**
  * @covers PapayaRequest::getBasePath
  */
  public function testGetBasePathDefault() {
    $request = new PapayaRequest();
    $this->assertEquals(
      '/', $request->getBasePath()
    );
  }

  /**
  * @covers PapayaRequest::getBasePath
  */
  public function testGetBasePath() {
    $request = new PapayaRequest();
    /** @var PHPUnit_Framework_MockObject_MockObject|Url $url */
    $url = $this->createMock(Url::class);
    $parser = $this->createMock(PapayaRequestParser::class);
    $parser
      ->expects($this->once())
      ->method('parse')
      ->will($this->returnValue(array('session' => 'sid123456')));
    $parser
      ->expects($this->once())
      ->method('isLast')
      ->will($this->returnValue(TRUE));
    $request->setParsers(
      array($parser)
    );
    $request->load($url);
    $this->assertEquals(
      '/sid123456/',
      $request->getBasePath()
    );
  }

  /**
  * @covers PapayaRequest::getUrl
  */
  public function testGetUrl() {
    $request = new PapayaRequest();
    $request->papaya($this->mockPapaya()->application());
    /** @var PHPUnit_Framework_MockObject_MockObject|Url $url */
    $url = $this->createMock(Url::class);
    $request->load($url);
    $this->assertSame(
      $url,
      $request->getUrl()
    );
  }

  /**
  * @covers PapayaRequest::getUrl
  */
  public function testGetUrlImplicitLoadOfCurrentUrl() {
    $request = new PapayaRequest();
    $request->papaya($this->mockPapaya()->application());
    $this->assertInstanceOf(
      PapayaUrlCurrent::class,
      $request->getUrl()
    );
  }

  /**
  * @covers PapayaRequest::__get
  */
  public function testGetPropertyUrl() {
    $request = new PapayaRequest();
    $request->papaya($this->mockPapaya()->application());
    $this->assertInstanceOf(
      PapayaUrlCurrent::class,
      $request->url
    );
  }

  /**
  * @covers PapayaRequest::__get
  * @covers PapayaRequest::language
  */
  public function testGetPropertyLanguage() {
    $request = new PapayaRequest();
    $request->papaya($this->mockPapaya()->application());
    $this->assertInstanceOf(
      Language::class,
      $request->language
    );
  }

  /**
  * @covers PapayaRequest::__get
  * @covers PapayaRequest::language
  */
  public function testGetPropertyLanguageInitializeFromParameter() {
    $request = new PapayaRequest();
    $request->papaya($this->mockPapaya()->application());
    $request->setParameters(
      PapayaRequest::SOURCE_PATH,
      new PapayaRequestParameters(array('language' => 'de'))
    );
    $this->assertEquals(
      array(array('identifier' => 'de')),
      $request->language->getLazyLoadParameters()
    );
  }

  /**
  * @covers PapayaRequest::__get
  * @covers PapayaRequest::language
  */
  public function testGetPropertyLanguageInitializeFromOptions() {
    $request = new PapayaRequest();
    $request->papaya($this->mockPapaya()->application());
    $request->papaya(
      $this->mockPapaya()->application(
        array(
          'options' => $this->mockPapaya()->options(array('PAPAYA_CONTENT_LANGUAGE' => 3))
        )
      )
    );
    $this->assertEquals(
      array(array('id' => 3)),
      $request->language->getLazyLoadParameters()
    );
  }

  /**
  * @covers PapayaRequest::__get
  * @covers PapayaRequest::__set
  * @covers PapayaRequest::language
  */
  public function testSetPropertyLanguage() {
    $request = new PapayaRequest();
    $request->papaya($this->mockPapaya()->application());
    $request->language = $language = $this->createMock(Language::class);
    $this->assertSame(
      $language,
      $request->language
    );
  }

  /**
  * @covers PapayaRequest::__get
  * @covers PapayaRequest::language
  */
  public function testGetPropertyLanguageId() {
    $language = $this->createMock(Language::class);
    $language
      ->expects($this->once())
      ->method('__get')
      ->with('id')
      ->will($this->returnValue('3'));

    $request = new PapayaRequest();
    $request->language = $language;
    $this->assertEquals(3, $request->languageId);
  }

  /**
  * @covers PapayaRequest::__get
  * @covers PapayaRequest::language
  */
  public function testGetPropertyLanguageCode() {
    $language = $this->createMock(Language::class);
    $language
      ->expects($this->once())
      ->method('__get')
      ->with('identifier')
      ->will($this->returnValue('en'));

    $request = new PapayaRequest();
    $request->language = $language;
    $this->assertEquals('en', $request->languageIdentifier);
  }

  /**
  * @covers PapayaRequest::__get
  * @covers PapayaRequest::__set
  * @covers PapayaRequest::mode
  */
  public function testGetPropertyModeGetAfterSet() {
    $mode = $this->createMock(Mode::class);
    $request = new PapayaRequest();
    $request->mode = $mode;
    $this->assertSame($mode, $request->mode);
  }

  /**
  * @covers PapayaRequest::__get
  * @covers PapayaRequest::mode
  */
  public function testGetPropertyModeInitializeFromParameter() {
    $request = new PapayaRequest();
    $request->setParameters(
      PapayaRequest::SOURCE_PATH,
      new PapayaRequestParameters(array('output_mode' => 'ext'))
    );
    $this->assertEquals(
      array(array('extension' => 'ext')),
      $request->mode->getLazyLoadParameters()
    );
  }

  /**
  * @covers PapayaRequest::__get
  * @covers PapayaRequest::mode
  */
  public function testGetPropertyModeInitializeFromParameterXmlPreviewMode() {
    $request = new PapayaRequest();
    $request->setParameters(
      PapayaRequest::SOURCE_PATH,
      new PapayaRequestParameters(array('output_mode' => 'xml'))
    );
    $this->assertNull($request->mode->getLazyLoadParameters());
    $this->assertEquals(
      array(
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
      ),
      iterator_to_array($request->mode)
    );
  }

  /**
  * @covers PapayaRequest::__get
  * @covers PapayaRequest::mode
  */
  public function testGetPropertyModeId() {
    $mode = $this->createMock(Mode::class);
    $mode
      ->expects($this->once())
      ->method('__get')
      ->with('id')
      ->will($this->returnValue(42));

    $request = new PapayaRequest();
    $request->mode = $mode;
    $this->assertEquals(42, $request->modeId);
  }

  /**
  * @covers PapayaRequest::__get
  */
  public function testGetInvalidPropertyExpectingException() {
    $request = new PapayaRequest();
    $this->expectException(LogicException::class);
    /** @noinspection PhpUndefinedFieldInspection */
    $request->INVALID_PROPERTY;
  }

  /**
  * @covers PapayaRequest::__set
  */
  public function testSetInvalidPropertyExpectingException() {
    $request = new PapayaRequest();
    $this->expectException(LogicException::class);
    /** @noinspection PhpUndefinedFieldInspection */
    $request->INVALID_PROPERTY = 'fail';
  }

  /**
  * @covers PapayaRequest::__get
  */
  public function testGetPropertyPageIdFromParameters() {
    $request = new PapayaRequest();
    $request->papaya($this->mockPapaya()->application());
    $request->setParameters(
      PapayaRequest::SOURCE_PATH, new PapayaRequestParameters(array('page_id' => 42))
    );
    $this->assertEquals(42, $request->pageId);
  }

  /**
  * @covers PapayaRequest::__get
  */
  public function testGetPropertyPageIdFromOptions() {
    $request = new PapayaRequest();
    $request->papaya(
      $this->mockPapaya()->application(
        array(
          'options' => $this->mockPapaya()->options(array('PAPAYA_PAGEID_DEFAULT' => 42))
        )
      )
    );
    $this->assertEquals(42, $request->pageId);
  }

  /**
  * @covers PapayaRequest::__get
  */
  public function testGetPropertyIsPreviewFromParameters() {
    $request = new PapayaRequest();
    $request->papaya($this->mockPapaya()->application());
    $request->setParameters(
      PapayaRequest::SOURCE_PATH, new PapayaRequestParameters(array('preview' => TRUE))
    );
    $this->assertTrue($request->isPreview);
  }

  /**
  * @covers PapayaRequest::getParameterGroupSeparator
  */
  public function testGetParameterGroupSeparator() {
    $request = new PapayaRequest();
    $this->assertEquals(
      ':',
      $request->getParameterGroupSeparator()
    );
  }

  /**
   * @covers PapayaRequest::setParameterGroupSeparator
   * @dataProvider setParameterLevelSeparatorDataProvider
   * @param string $separator
   * @param string $expected
   */
  public function testSetParameterGroupSeparator($separator, $expected) {
    $request = new PapayaRequest();
    $request->setParameterGroupSeparator($separator);
    $this->assertEquals(
      $expected,
      $this->readAttribute($request, '_separator')
    );
  }

  /**
  * @covers PapayaRequest::setParameterGroupSeparator
  */
  public function testSetParameterGroupSeparatorExpectingError() {
    $request = new PapayaRequest();
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid parameter level separator: X');
    $request->setParameterGroupSeparator('X');
  }

  /**
  * @covers PapayaRequest::setParsers
  */
  public function testSetParsers() {
    $parser = $this->createMock(PapayaRequestParser::class);
    $request = new PapayaRequest();
    $request->setParsers(array($parser));
    $this->assertSame(
      array($parser),
      $this->readAttribute($request, '_parsers')
    );
  }

  /**
  * @covers PapayaRequest::_initParsers
  */
  public function testLazyParserInitialization() {
    $request = new PapayaRequest();
    $request->papaya($this->mockPapaya()->application());
    /** @var PHPUnit_Framework_MockObject_MockObject|Url $url */
    $url = $this->createMock(Url::class);
    $request->load($url);
    $this->assertGreaterThan(
      0,
      count($this->readAttribute($request, '_parsers'))
    );
  }

  /**
  * @covers PapayaRequest::load
  * @covers PapayaRequest::_initParsers
  */
  public function testLoad() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Url $url */
    $url = $this->createMock(Url::class);
    $parserOne = $this->createMock(PapayaRequestParser::class);
    $parserOne
      ->expects($this->once())
      ->method('parse')
      ->with($this->equalTo($url))
      ->will($this->returnValue(array('first' => 'success')));
    $parserOne
      ->expects($this->once())
      ->method('isLast')
      ->will($this->returnValue(FALSE));
    $parserTwo = $this->createMock(PapayaRequestParser::class);
    $parserTwo
      ->expects($this->once())
      ->method('parse')
      ->with($this->equalTo($url))
      ->will($this->returnValue(FALSE));
    $parserThree = $this->createMock(PapayaRequestParser::class);
    $parserThree
      ->expects($this->once())
      ->method('parse')
      ->with($this->equalTo($url))
      ->will($this->returnValue(array('second' => 'success')));
    $parserThree
      ->expects($this->once())
      ->method('isLast')
      ->will($this->returnValue(TRUE));
    $request = new PapayaRequest();
    $request->setParsers(
      array($parserOne, $parserTwo, $parserThree)
    );
    $this->assertTrue(
      $request->load($url)
    );
    $this->assertEquals(
      array(
        'first' => 'success',
        'second' => 'success'
      ),
      $this->readAttribute($request, '_pathData')
    );
  }

  /**
  * @covers PapayaRequest::load
  */
  public function testLoadExpectingFalse() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Url $url */
    $url = $this->createMock(Url::class);
    $request = new PapayaRequest();
    $request->papaya($this->mockPapaya()->application());
    $this->assertFalse(
      $request->load($url)
    );
  }

  /**
  * @covers PapayaRequest::getParameter
  */
  public function testGetParameterExpectingDefaultValue() {
    $request = new PapayaRequest();
    $request->papaya($this->mockPapaya()->application());
    $this->assertEquals(
      'sample',
      $request->getParameter('NON_EXISITNG', 'sample')
    );
  }

  /**
  * @covers PapayaRequest::getParameter
  */
  public function testGetParameter() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Url $url */
    $url = $this->createMock(Url::class);
    $request = new PapayaRequest();
    $request->papaya($this->mockPapaya()->application());
    $request->setParsers(array($this->getRequestParserMockFixture()));
    $this->assertTrue(
      $request->load($url)
    );
    $this->assertEquals(
      'success',
      $request->getParameter('PARAMETER_PATH_SAMPLE', 'fail')
    );
  }

  /**
  * @covers PapayaRequest::loadParameters
  * @covers PapayaRequest::_loadParametersForSource
  */
  public function testLoadParametersPath() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Url $url */
    $url = $this->createMock(Url::class);
    $request = new PapayaRequest();
    $request->papaya($this->mockPapaya()->application());
    $request->setParsers(array($this->getRequestParserMockFixture()));
    $this->assertTrue(
      $request->load($url)
    );
    $parameters = $request->loadParameters(PapayaRequest::SOURCE_PATH);
    $this->assertEquals(
      'success',
      $parameters->get('PARAMETER_PATH_SAMPLE', 'fail')
    );
  }

  /**
  * @covers PapayaRequest::loadParameters
  * @covers PapayaRequest::_loadParametersForSource
  */
  public function testLoadParametersPathCached() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Url $url */
    $url = $this->createMock(Url::class);
    $request = new PapayaRequest();
    $request->papaya($this->mockPapaya()->application());
    $request->setParsers(array($this->getRequestParserMockFixture()));
    $this->assertTrue(
      $request->load($url)
    );
    $request->loadParameters(PapayaRequest::SOURCE_PATH);
    $parameters = $request->loadParameters(PapayaRequest::SOURCE_PATH);
    $this->assertEquals(
      'success',
      $parameters->get('PARAMETER_PATH_SAMPLE', 'fail')
    );
  }

  /**
  * @covers PapayaRequest::loadParameters
  * @covers PapayaRequest::_loadParametersForSource
  */
  public function testLoadParametersQuery() {
    $request = new PapayaRequest();
    $request->papaya($this->mockPapaya()->application());
    $request->load($this->getUrlMockFixture('PARAMETER_QUERY_SAMPLE=success'));
    $parameters = $request->loadParameters(PapayaRequest::SOURCE_QUERY);
    $this->assertEquals(
      'success',
      $parameters->get('PARAMETER_QUERY_SAMPLE', 'fail')
    );
  }

  /**
  * @covers PapayaRequest::loadParameters
  * @covers PapayaRequest::_loadParametersForSource
  * @backupGlobals
  */
  public function testLoadParametersBody() {
    $request = new PapayaRequest();
    $request->papaya($this->mockPapaya()->application());
    $_POST = array(
      'PARAMETER_POST_SAMPLE' => 'success'
    );
    $parameters = $request->loadParameters(PapayaRequest::SOURCE_BODY);
    $this->assertEquals(
      'success',
      $parameters->get('PARAMETER_POST_SAMPLE', 'fail')
    );
  }

  /**
  * @covers PapayaRequest::loadParameters
  * @covers PapayaRequest::_loadParametersForSource
  * @backupGlobals
  */
  public function testLoadParametersAllAndPathCached() {
    $_POST = array(
      'PARAMETER_POST_SAMPLE' => 'success'
    );
    $request = new PapayaRequest();
    $request->papaya($this->mockPapaya()->application());
    $request->setParsers(
      array($this->getRequestParserMockFixture())
    );
    $request->load($this->getUrlMockFixture('PARAMETER_QUERY_SAMPLE=success'));
    $request->loadParameters(PapayaRequest::SOURCE_PATH);

    $parameters = $request->loadParameters();
    $this->assertEquals(
      array(
        'PARAMETER_PATH_SAMPLE' => 'success',
        'PARAMETER_QUERY_SAMPLE' => 'success',
        'PARAMETER_POST_SAMPLE' => 'success'
      ),
      $parameters->toArray()
    );
  }

  /**
  * @covers PapayaRequest::loadParameters
  * @covers PapayaRequest::_loadParametersForSource
  * @backupGlobals
  */
  public function testLoadParametersCookie() {
    $request = new PapayaRequest();
    $request->papaya($this->mockPapaya()->application());
    $_COOKIE = array(
      'PARAMETER_COOKIE_SAMPLE' => 'success'
    );
    $parameters = $request->loadParameters(PapayaRequest::SOURCE_COOKIE);
    $this->assertEquals(
      'success',
      $parameters->get('PARAMETER_COOKIE_SAMPLE', 'fail')
    );
  }

  /**
  * @covers PapayaRequest::getParameters
  */
  public function testGetParameters() {
    $request = new PapayaRequest();
    $request->papaya($this->mockPapaya()->application());
    $request->load($this->getUrlMockFixture('group[e1]=success'));
    $parameters = $request->getParameters(PapayaRequest::SOURCE_QUERY);
    $this->assertEquals(
      array(
        'group' => array(
          'e1' => 'success'
        )
      ),
      $parameters->toArray()
    );
  }

  /**
  * @covers PapayaRequest::getParameterGroup
  */
  public function testGetParameterGroup() {
    $request = new PapayaRequest();
    $request->papaya($this->mockPapaya()->application());
    $request->load($this->getUrlMockFixture('group[e1]=success'));
    $parameters = $request->getParameterGroup('group', PapayaRequest::SOURCE_QUERY);
    $this->assertInstanceOf(
      PapayaRequestParameters::class,
      $parameters
    );
    $this->assertEquals(
      'success',
      $parameters->get('e1')
    );
  }

  /**
  * @covers PapayaRequest::setParameters
  */
  public function testSetParameters() {
    $request = new PapayaRequest();
    $request->papaya($this->mockPapaya()->application());
    $request->load($this->getUrlMockFixture('PARAMETER_QUERY_SAMPLE=fail'));
    $request->getParameterGroup('PARAMETER_QUERY_SAMPLE', PapayaRequest::SOURCE_ALL);
    $parameters = new PapayaRequestParameters();
    $parameters->merge(array('PARAMETER_QUERY_SAMPLE' => 'success'));
    $request->setParameters(PapayaRequest::SOURCE_QUERY, $parameters);
    $this->assertEquals(
      array(
        PapayaRequest::SOURCE_PATH,
        PapayaRequest::SOURCE_QUERY,
        PapayaRequest::SOURCE_BODY
      ),
      array_keys($this->readAttribute($request, '_parameterCache'))
    );
    $this->assertSame(
      array('PARAMETER_QUERY_SAMPLE' => 'success'),
      $request->getParameters(PapayaRequest::SOURCE_QUERY)->toArray()
    );
  }

  /**
  * @covers PapayaRequest::setParameters
  */
  public function testSetParametersWithInvalidSource() {
    $request = new PapayaRequest();
    $this->expectException(InvalidArgumentException::class);
    $request->setParameters(PapayaRequest::SOURCE_ALL, new PapayaRequestParameters());
  }

  /**
  * @covers PapayaRequest::setParameters
  */
  public function testSetParametersWithInvalidParameters() {
    $request = new PapayaRequest();
    $this->expectException(InvalidArgumentException::class);
    $request->setParameters(PapayaRequest::SOURCE_QUERY, NULL);
  }

  /**
  * @covers PapayaRequest::getMethod
  */
  public function testGetMethodDefault() {
    $request = new PapayaRequest();
    $this->assertEquals(
      'get',
      $request->getMethod()
    );
  }

  /**
  * @covers PapayaRequest::__get
  */
  public function testGetPropertyMethod() {
    $request = new PapayaRequest();
    $this->assertEquals(
      'get',
      $request->method
    );
  }

  /**
  * @covers PapayaRequest::getMethod
  * @backupGlobals
  */
  public function testGetMethodExpectingPost() {
    $request = new PapayaRequest();
    $_SERVER['REQUEST_METHOD'] = 'post';
    $this->assertEquals(
      'post',
      $request->getMethod()
    );
  }

  /**
  * @covers PapayaRequest::getMagicQuotesStatus
  */
  public function testGetMagicQuotesStatus() {
    $request = new PapayaRequest();
    $this->assertInternalType(
      'boolean',
      $request->getMagicQuotesStatus()
    );
  }

  /**
  * @covers PapayaRequest::allowCompression
  * @backupGlobals enabled
  */
  public function testAllowCompressionWithOldProtocolExpectingFalse() {
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
    $request = new PapayaRequest();
    $this->assertFalse($request->allowCompression());
  }

  /**
  * @covers PapayaRequest::allowCompression
  * @backupGlobals enabled
  */
  public function testAllowCompressionExpectingTrue() {
    if (!function_exists('gzencode')) {
      $this->markTestSkipped('Zlib extension not found.');
    }
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
    $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip,deflate';
    $request = new PapayaRequest();
    $this->assertTrue($request->allowCompression());
  }

  /**
  * @covers PapayaRequest::__get
  * @covers PapayaRequest::allowCompression
  * @backupGlobals enabled
  */
  public function testPropertyAllowCompressionExpectingTrue() {
    if (!function_exists('gzencode')) {
      $this->markTestSkipped('Zlib extension not found.');
    }
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
    $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip,deflate';
    $request = new PapayaRequest();
    $this->assertTrue($request->allowCompression);
  }

  /**
  * @covers PapayaRequest::allowCompression
  * @backupGlobals enabled
  */
  public function testAllowCompressionCachedExpectingTrue() {
    if (!function_exists('gzencode')) {
      $this->markTestSkipped('Zlib extension not found.');
    }
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
    $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip,deflate';
    $request = new PapayaRequest();
    $request->allowCompression();
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
    $_SERVER['HTTP_ACCEPT_ENCODING'] = NULL;
    $this->assertTrue($request->allowCompression());
  }

  /**
  * @covers PapayaRequest::allowCompression
  * @backupGlobals enabled
  */
  public function testAllowCompressionExpectingFalse() {
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
    $_SERVER['HTTP_ACCEPT_ENCODING'] = NULL;
    $request = new PapayaRequest();
    $this->assertFalse($request->allowCompression());
  }

  /**
  * @covers PapayaRequest::allowEsi
  * @backupGlobals enabled
  */
  public function testAllowEsiExpectingFalse() {
    $request = new PapayaRequest();
    $this->assertFalse($request->allowEsi());
  }

  /**
  * @covers PapayaRequest::allowEsi
  * @backupGlobals enabled
  */
  public function testAllowEsiExpectingTrue() {
    $_SERVER['HTTP_X_PAPAYA_ESI'] = 'yes';
    $request = new PapayaRequest();
    $this->assertTrue($request->allowEsi());
  }

  /**
   * @covers PapayaRequest::validateBrowserCache
   * @dataProvider provideValidBrowserCacheData
   * @backupGlobals enabled
   * @param string $eTag
   * @param string $cacheTime
   */
  public function testValidateBrowserCacheExpectingTrue($eTag, $cacheTime) {
    $_SERVER['HTTP_IF_NONE_MATCH'] = $eTag;
    $_SERVER['HTTP_IF_MODIFIED_SINCE'] = $cacheTime;
    $request = new PapayaRequest();
    $this->assertTrue(
      $request->validateBrowserCache('success', gmmktime(12, 0, 0, 07, 15, 2000))
    );
  }

  /**
   * @covers PapayaRequest::validateBrowserCache
   * @dataProvider provideInvalidBrowserCacheData
   * @backupGlobals enabled
   * @param string $eTag
   * @param string $cacheTime
   */
  public function testValidateBrowserCacheExpectingFalse($eTag, $cacheTime) {
    $_SERVER['HTTP_IF_NONE_MATCH'] = $eTag;
    $_SERVER['HTTP_IF_MODIFIED_SINCE'] = $cacheTime;
    $request = new PapayaRequest();
    $this->assertFalse(
      $request->validateBrowserCache('success', gmmktime(12, 0, 0, 07, 15, 2000))
    );
  }

  /**
   * @covers PapayaRequest::content
   * @covers PapayaRequest::__get
   */
  public function testContentGetSet() {
    $content = $this->createMock(PapayaRequestContent::class);
    $request = new PapayaRequest();
    $request->content($content);
    $this->assertSame($content, $request->content);
  }

  /**
   * @covers PapayaRequest::content
   */
  public function testContentImplicitCreate() {
    $request = new PapayaRequest();
    $this->assertInstanceOf(PapayaRequestContent::class, $request->content());
  }

  /**
   * @covers PapayaRequest::content
   * @covers PapayaRequest::__get
   */
  public function testGetPropertyContent() {
    $content = $this
      ->getMockBuilder(PapayaRequestContent::class)
      ->setMethods(array('__toString'))
      ->getMock();
    $content
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('test'));
    $request = new PapayaRequest();
    $request->content($content);
    $this->assertEquals('test', (string)$request->content);
  }

  /**
   * @covers PapayaRequest::content
   * @covers PapayaRequest::__get
   */
  public function testGetPropertyContentLength() {
    $content = $this->createMock(PapayaRequestContent::class);
    $content
      ->expects($this->once())
      ->method('length')
      ->will($this->returnValue(42));
    $request = new PapayaRequest();
    $request->content($content);
    $this->assertEquals(42, $request->contentLength);
  }

  /***********************************
  * Fixtures
  ***********************************/

  public function getRequestParserMockFixture() {
    $parser = $this->createMock(PapayaRequestParser::class);
    $parser
      ->expects($this->once())
      ->method('parse')
      ->will(
        $this->returnValue(
          array('PARAMETER_PATH_SAMPLE' => 'success')
        )
      );
    $parser
      ->expects($this->once())
      ->method('isLast')
      ->will($this->returnValue(TRUE));
    return $parser;
  }

  /**
   * @param string $queryString
   * @return PHPUnit_Framework_MockObject_MockObject|Url
   */
  public function getUrlMockFixture($queryString = '') {
    $url = $this
      ->getMockBuilder(Url::class)
      ->setMethods(array('getQuery'))
      ->getMock();
    $url
      ->expects($this->any())
      ->method('getQuery')
      ->will($this->returnValue($queryString));
    return $url;
  }

  /***********************************
  * Data provider
  ***********************************/

  public static function setParameterLevelSeparatorDataProvider() {
    return array(
      array('', '[]'),
      array('[]', '[]'),
      array(',', ','),
      array(':', ':'),
      array('/', '/'),
      array('*', '*'),
      array('!', '!')
    );
  }

  /*
  * validated against etag: success, time: 2000-07-15T12:00:00+0000
  */
  public static function provideValidBrowserCacheData() {
    return array(
      'greater cachetime' => array('success', '2010-07-15T12:00:00+0000'),
      'equal cachetime' => array('success', '2000-07-15T12:00:00+0000'),
      'quoted etag, equal cachetime' => array('"success"', '2000-07-15T12:00:00+0000')
    );
  }

  /*
  * validated against etag: success, time: 42
  */
  public static function provideInvalidBrowserCacheData() {
    return array(
      'smaller cachetime' => array('success', '1990-07-15T12:00:00+0000'),
      'invalid etag, equal cachetime' => array('failed', '2000-07-15T12:00:00+0000'),
      'invalid etag, greater cachetime' => array('failed', '2010-07-15T12:00:00+0000'),
      'quoted etag, smaller cachetime' => array('"success"', '1990-07-15T12:00:00+0000')
    );
  }
}

