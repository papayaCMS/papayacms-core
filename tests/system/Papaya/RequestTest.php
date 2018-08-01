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

class PapayaRequestTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Request::__construct
  */
  public function testConstructor() {
    $request = new \Papaya\Request();
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
  * @covers \Papaya\Request::__construct
  */
  public function testConstructorWithConfigurationArgument() {
    $options = $this->mockPapaya()->options(
      array(
        'PAPAYA_URL_LEVEL_SEPARATOR' => '!',
        'PAPAYA_PATH_WEB' => '/foo/'
      )
    );
    $request = new \Papaya\Request($options);
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
  * @covers \Papaya\Request::setConfiguration
  */
  public function testSetConfiguration() {
    $options = $this->mockPapaya()->options(
      array(
        'PAPAYA_URL_LEVEL_SEPARATOR' => '!',
        'PAPAYA_PATH_WEB' => '/foo/'
      )
    );
    $request = new \Papaya\Request;
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
  * @covers \Papaya\Request::getBasePath
  */
  public function testGetBasePathDefault() {
    $request = new \Papaya\Request();
    $this->assertEquals(
      '/', $request->getBasePath()
    );
  }

  /**
  * @covers \Papaya\Request::getBasePath
  */
  public function testGetBasePath() {
    $request = new \Papaya\Request();
    /** @var PHPUnit_Framework_MockObject_MockObject|Url $url */
    $url = $this->createMock(Url::class);
    $parser = $this->createMock(\Papaya\Request\Parser::class);
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
  * @covers \Papaya\Request::getUrl
  */
  public function testGetUrl() {
    $request = new \Papaya\Request();
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
  * @covers \Papaya\Request::getUrl
  */
  public function testGetUrlImplicitLoadOfCurrentUrl() {
    $request = new \Papaya\Request();
    $request->papaya($this->mockPapaya()->application());
    $this->assertInstanceOf(
      \PapayaUrlCurrent::class,
      $request->getUrl()
    );
  }

  /**
  * @covers \Papaya\Request::__get
  */
  public function testGetPropertyUrl() {
    $request = new \Papaya\Request();
    $request->papaya($this->mockPapaya()->application());
    $this->assertInstanceOf(
      \PapayaUrlCurrent::class,
      $request->url
    );
  }

  /**
  * @covers \Papaya\Request::__get
  * @covers \Papaya\Request::language
  */
  public function testGetPropertyLanguage() {
    $request = new \Papaya\Request();
    $request->papaya($this->mockPapaya()->application());
    $this->assertInstanceOf(
      Language::class,
      $request->language
    );
  }

  /**
  * @covers \Papaya\Request::__get
  * @covers \Papaya\Request::language
  */
  public function testGetPropertyLanguageInitializeFromParameter() {
    $request = new \Papaya\Request();
    $request->papaya($this->mockPapaya()->application());
    $request->setParameters(
      \Papaya\Request::SOURCE_PATH,
      new \Papaya\Request\Parameters(array('language' => 'de'))
    );
    $this->assertEquals(
      array(array('identifier' => 'de')),
      $request->language->getLazyLoadParameters()
    );
  }

  /**
  * @covers \Papaya\Request::__get
  * @covers \Papaya\Request::language
  */
  public function testGetPropertyLanguageInitializeFromOptions() {
    $request = new \Papaya\Request();
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
  * @covers \Papaya\Request::__get
  * @covers \Papaya\Request::__set
  * @covers \Papaya\Request::language
  */
  public function testSetPropertyLanguage() {
    $request = new \Papaya\Request();
    $request->papaya($this->mockPapaya()->application());
    $request->language = $language = $this->createMock(Language::class);
    $this->assertSame(
      $language,
      $request->language
    );
  }

  /**
  * @covers \Papaya\Request::__get
  * @covers \Papaya\Request::language
  */
  public function testGetPropertyLanguageId() {
    $language = $this->createMock(Language::class);
    $language
      ->expects($this->once())
      ->method('__get')
      ->with('id')
      ->will($this->returnValue('3'));

    $request = new \Papaya\Request();
    $request->language = $language;
    $this->assertEquals(3, $request->languageId);
  }

  /**
  * @covers \Papaya\Request::__get
  * @covers \Papaya\Request::language
  */
  public function testGetPropertyLanguageCode() {
    $language = $this->createMock(Language::class);
    $language
      ->expects($this->once())
      ->method('__get')
      ->with('identifier')
      ->will($this->returnValue('en'));

    $request = new \Papaya\Request();
    $request->language = $language;
    $this->assertEquals('en', $request->languageIdentifier);
  }

  /**
  * @covers \Papaya\Request::__get
  * @covers \Papaya\Request::__set
  * @covers \Papaya\Request::mode
  */
  public function testGetPropertyModeGetAfterSet() {
    $mode = $this->createMock(Mode::class);
    $request = new \Papaya\Request();
    $request->mode = $mode;
    $this->assertSame($mode, $request->mode);
  }

  /**
  * @covers \Papaya\Request::__get
  * @covers \Papaya\Request::mode
  */
  public function testGetPropertyModeInitializeFromParameter() {
    $request = new \Papaya\Request();
    $request->setParameters(
      \Papaya\Request::SOURCE_PATH,
      new \Papaya\Request\Parameters(array('output_mode' => 'ext'))
    );
    $this->assertEquals(
      array(array('extension' => 'ext')),
      $request->mode->getLazyLoadParameters()
    );
  }

  /**
  * @covers \Papaya\Request::__get
  * @covers \Papaya\Request::mode
  */
  public function testGetPropertyModeInitializeFromParameterXmlPreviewMode() {
    $request = new \Papaya\Request();
    $request->setParameters(
      \Papaya\Request::SOURCE_PATH,
      new \Papaya\Request\Parameters(array('output_mode' => 'xml'))
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
  * @covers \Papaya\Request::__get
  * @covers \Papaya\Request::mode
  */
  public function testGetPropertyModeId() {
    $mode = $this->createMock(Mode::class);
    $mode
      ->expects($this->once())
      ->method('__get')
      ->with('id')
      ->will($this->returnValue(42));

    $request = new \Papaya\Request();
    $request->mode = $mode;
    $this->assertEquals(42, $request->modeId);
  }

  /**
  * @covers \Papaya\Request::__get
  */
  public function testGetInvalidPropertyExpectingException() {
    $request = new \Papaya\Request();
    $this->expectException(LogicException::class);
    /** @noinspection PhpUndefinedFieldInspection */
    $request->INVALID_PROPERTY;
  }

  /**
  * @covers \Papaya\Request::__set
  */
  public function testSetInvalidPropertyExpectingException() {
    $request = new \Papaya\Request();
    $this->expectException(LogicException::class);
    /** @noinspection PhpUndefinedFieldInspection */
    $request->INVALID_PROPERTY = 'fail';
  }

  /**
  * @covers \Papaya\Request::__get
  */
  public function testGetPropertyPageIdFromParameters() {
    $request = new \Papaya\Request();
    $request->papaya($this->mockPapaya()->application());
    $request->setParameters(
      \Papaya\Request::SOURCE_PATH, new \Papaya\Request\Parameters(array('page_id' => 42))
    );
    $this->assertEquals(42, $request->pageId);
  }

  /**
  * @covers \Papaya\Request::__get
  */
  public function testGetPropertyPageIdFromOptions() {
    $request = new \Papaya\Request();
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
  * @covers \Papaya\Request::__get
  */
  public function testGetPropertyIsPreviewFromParameters() {
    $request = new \Papaya\Request();
    $request->papaya($this->mockPapaya()->application());
    $request->setParameters(
      \Papaya\Request::SOURCE_PATH, new \Papaya\Request\Parameters(array('preview' => TRUE))
    );
    $this->assertTrue($request->isPreview);
  }

  /**
  * @covers \Papaya\Request::getParameterGroupSeparator
  */
  public function testGetParameterGroupSeparator() {
    $request = new \Papaya\Request();
    $this->assertEquals(
      ':',
      $request->getParameterGroupSeparator()
    );
  }

  /**
   * @covers \Papaya\Request::setParameterGroupSeparator
   * @dataProvider setParameterLevelSeparatorDataProvider
   * @param string $separator
   * @param string $expected
   */
  public function testSetParameterGroupSeparator($separator, $expected) {
    $request = new \Papaya\Request();
    $request->setParameterGroupSeparator($separator);
    $this->assertEquals(
      $expected,
      $this->readAttribute($request, '_separator')
    );
  }

  /**
  * @covers \Papaya\Request::setParameterGroupSeparator
  */
  public function testSetParameterGroupSeparatorExpectingError() {
    $request = new \Papaya\Request();
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid parameter level separator: X');
    $request->setParameterGroupSeparator('X');
  }

  /**
  * @covers \Papaya\Request::setParsers
  */
  public function testSetParsers() {
    $parser = $this->createMock(\Papaya\Request\Parser::class);
    $request = new \Papaya\Request();
    $request->setParsers(array($parser));
    $this->assertSame(
      array($parser),
      $this->readAttribute($request, '_parsers')
    );
  }

  /**
  * @covers \Papaya\Request::_initParsers
  */
  public function testLazyParserInitialization() {
    $request = new \Papaya\Request();
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
  * @covers \Papaya\Request::load
  * @covers \Papaya\Request::_initParsers
  */
  public function testLoad() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Url $url */
    $url = $this->createMock(Url::class);
    $parserOne = $this->createMock(\Papaya\Request\Parser::class);
    $parserOne
      ->expects($this->once())
      ->method('parse')
      ->with($this->equalTo($url))
      ->will($this->returnValue(array('first' => 'success')));
    $parserOne
      ->expects($this->once())
      ->method('isLast')
      ->will($this->returnValue(FALSE));
    $parserTwo = $this->createMock(\Papaya\Request\Parser::class);
    $parserTwo
      ->expects($this->once())
      ->method('parse')
      ->with($this->equalTo($url))
      ->will($this->returnValue(FALSE));
    $parserThree = $this->createMock(\Papaya\Request\Parser::class);
    $parserThree
      ->expects($this->once())
      ->method('parse')
      ->with($this->equalTo($url))
      ->will($this->returnValue(array('second' => 'success')));
    $parserThree
      ->expects($this->once())
      ->method('isLast')
      ->will($this->returnValue(TRUE));
    $request = new \Papaya\Request();
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
  * @covers \Papaya\Request::load
  */
  public function testLoadExpectingFalse() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Url $url */
    $url = $this->createMock(Url::class);
    $request = new \Papaya\Request();
    $request->papaya($this->mockPapaya()->application());
    $this->assertFalse(
      $request->load($url)
    );
  }

  /**
  * @covers \Papaya\Request::getParameter
  */
  public function testGetParameterExpectingDefaultValue() {
    $request = new \Papaya\Request();
    $request->papaya($this->mockPapaya()->application());
    $this->assertEquals(
      'sample',
      $request->getParameter('NON_EXISITNG', 'sample')
    );
  }

  /**
  * @covers \Papaya\Request::getParameter
  */
  public function testGetParameter() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Url $url */
    $url = $this->createMock(Url::class);
    $request = new \Papaya\Request();
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
  * @covers \Papaya\Request::loadParameters
  * @covers \Papaya\Request::_loadParametersForSource
  */
  public function testLoadParametersPath() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Url $url */
    $url = $this->createMock(Url::class);
    $request = new \Papaya\Request();
    $request->papaya($this->mockPapaya()->application());
    $request->setParsers(array($this->getRequestParserMockFixture()));
    $this->assertTrue(
      $request->load($url)
    );
    $parameters = $request->loadParameters(\Papaya\Request::SOURCE_PATH);
    $this->assertEquals(
      'success',
      $parameters->get('PARAMETER_PATH_SAMPLE', 'fail')
    );
  }

  /**
  * @covers \Papaya\Request::loadParameters
  * @covers \Papaya\Request::_loadParametersForSource
  */
  public function testLoadParametersPathCached() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Url $url */
    $url = $this->createMock(Url::class);
    $request = new \Papaya\Request();
    $request->papaya($this->mockPapaya()->application());
    $request->setParsers(array($this->getRequestParserMockFixture()));
    $this->assertTrue(
      $request->load($url)
    );
    $request->loadParameters(\Papaya\Request::SOURCE_PATH);
    $parameters = $request->loadParameters(\Papaya\Request::SOURCE_PATH);
    $this->assertEquals(
      'success',
      $parameters->get('PARAMETER_PATH_SAMPLE', 'fail')
    );
  }

  /**
  * @covers \Papaya\Request::loadParameters
  * @covers \Papaya\Request::_loadParametersForSource
  */
  public function testLoadParametersQuery() {
    $request = new \Papaya\Request();
    $request->papaya($this->mockPapaya()->application());
    $request->load($this->getUrlMockFixture('PARAMETER_QUERY_SAMPLE=success'));
    $parameters = $request->loadParameters(\Papaya\Request::SOURCE_QUERY);
    $this->assertEquals(
      'success',
      $parameters->get('PARAMETER_QUERY_SAMPLE', 'fail')
    );
  }

  /**
  * @covers \Papaya\Request::loadParameters
  * @covers \Papaya\Request::_loadParametersForSource
  * @backupGlobals
  */
  public function testLoadParametersBody() {
    $request = new \Papaya\Request();
    $request->papaya($this->mockPapaya()->application());
    $_POST = array(
      'PARAMETER_POST_SAMPLE' => 'success'
    );
    $parameters = $request->loadParameters(\Papaya\Request::SOURCE_BODY);
    $this->assertEquals(
      'success',
      $parameters->get('PARAMETER_POST_SAMPLE', 'fail')
    );
  }

  /**
  * @covers \Papaya\Request::loadParameters
  * @covers \Papaya\Request::_loadParametersForSource
  * @backupGlobals
  */
  public function testLoadParametersAllAndPathCached() {
    $_POST = array(
      'PARAMETER_POST_SAMPLE' => 'success'
    );
    $request = new \Papaya\Request();
    $request->papaya($this->mockPapaya()->application());
    $request->setParsers(
      array($this->getRequestParserMockFixture())
    );
    $request->load($this->getUrlMockFixture('PARAMETER_QUERY_SAMPLE=success'));
    $request->loadParameters(\Papaya\Request::SOURCE_PATH);

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
  * @covers \Papaya\Request::loadParameters
  * @covers \Papaya\Request::_loadParametersForSource
  * @backupGlobals
  */
  public function testLoadParametersCookie() {
    $request = new \Papaya\Request();
    $request->papaya($this->mockPapaya()->application());
    $_COOKIE = array(
      'PARAMETER_COOKIE_SAMPLE' => 'success'
    );
    $parameters = $request->loadParameters(\Papaya\Request::SOURCE_COOKIE);
    $this->assertEquals(
      'success',
      $parameters->get('PARAMETER_COOKIE_SAMPLE', 'fail')
    );
  }

  /**
  * @covers \Papaya\Request::getParameters
  */
  public function testGetParameters() {
    $request = new \Papaya\Request();
    $request->papaya($this->mockPapaya()->application());
    $request->load($this->getUrlMockFixture('group[e1]=success'));
    $parameters = $request->getParameters(\Papaya\Request::SOURCE_QUERY);
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
  * @covers \Papaya\Request::getParameterGroup
  */
  public function testGetParameterGroup() {
    $request = new \Papaya\Request();
    $request->papaya($this->mockPapaya()->application());
    $request->load($this->getUrlMockFixture('group[e1]=success'));
    $parameters = $request->getParameterGroup('group', \Papaya\Request::SOURCE_QUERY);
    $this->assertInstanceOf(
      \Papaya\Request\Parameters::class,
      $parameters
    );
    $this->assertEquals(
      'success',
      $parameters->get('e1')
    );
  }

  /**
  * @covers \Papaya\Request::setParameters
  */
  public function testSetParameters() {
    $request = new \Papaya\Request();
    $request->papaya($this->mockPapaya()->application());
    $request->load($this->getUrlMockFixture('PARAMETER_QUERY_SAMPLE=fail'));
    $request->getParameterGroup('PARAMETER_QUERY_SAMPLE', \Papaya\Request::SOURCE_ALL);
    $parameters = new \Papaya\Request\Parameters();
    $parameters->merge(array('PARAMETER_QUERY_SAMPLE' => 'success'));
    $request->setParameters(\Papaya\Request::SOURCE_QUERY, $parameters);
    $this->assertEquals(
      array(
        \Papaya\Request::SOURCE_PATH,
        \Papaya\Request::SOURCE_QUERY,
        \Papaya\Request::SOURCE_BODY
      ),
      array_keys($this->readAttribute($request, '_parameterCache'))
    );
    $this->assertSame(
      array('PARAMETER_QUERY_SAMPLE' => 'success'),
      $request->getParameters(\Papaya\Request::SOURCE_QUERY)->toArray()
    );
  }

  /**
  * @covers \Papaya\Request::setParameters
  */
  public function testSetParametersWithInvalidSource() {
    $request = new \Papaya\Request();
    $this->expectException(InvalidArgumentException::class);
    $request->setParameters(\Papaya\Request::SOURCE_ALL, new \Papaya\Request\Parameters());
  }

  /**
  * @covers \Papaya\Request::setParameters
  */
  public function testSetParametersWithInvalidParameters() {
    $request = new \Papaya\Request();
    $this->expectException(InvalidArgumentException::class);
    $request->setParameters(\Papaya\Request::SOURCE_QUERY, NULL);
  }

  /**
  * @covers \Papaya\Request::getMethod
  */
  public function testGetMethodDefault() {
    $request = new \Papaya\Request();
    $this->assertEquals(
      'get',
      $request->getMethod()
    );
  }

  /**
  * @covers \Papaya\Request::__get
  */
  public function testGetPropertyMethod() {
    $request = new \Papaya\Request();
    $this->assertEquals(
      'get',
      $request->method
    );
  }

  /**
  * @covers \Papaya\Request::getMethod
  * @backupGlobals
  */
  public function testGetMethodExpectingPost() {
    $request = new \Papaya\Request();
    $_SERVER['REQUEST_METHOD'] = 'post';
    $this->assertEquals(
      'post',
      $request->getMethod()
    );
  }

  /**
  * @covers \Papaya\Request::getMagicQuotesStatus
  */
  public function testGetMagicQuotesStatus() {
    $request = new \Papaya\Request();
    $this->assertInternalType(
      'boolean',
      $request->getMagicQuotesStatus()
    );
  }

  /**
  * @covers \Papaya\Request::allowCompression
  * @backupGlobals enabled
  */
  public function testAllowCompressionWithOldProtocolExpectingFalse() {
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
    $request = new \Papaya\Request();
    $this->assertFalse($request->allowCompression());
  }

  /**
  * @covers \Papaya\Request::allowCompression
  * @backupGlobals enabled
  */
  public function testAllowCompressionExpectingTrue() {
    if (!function_exists('gzencode')) {
      $this->markTestSkipped('Zlib extension not found.');
    }
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
    $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip,deflate';
    $request = new \Papaya\Request();
    $this->assertTrue($request->allowCompression());
  }

  /**
  * @covers \Papaya\Request::__get
  * @covers \Papaya\Request::allowCompression
  * @backupGlobals enabled
  */
  public function testPropertyAllowCompressionExpectingTrue() {
    if (!function_exists('gzencode')) {
      $this->markTestSkipped('Zlib extension not found.');
    }
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
    $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip,deflate';
    $request = new \Papaya\Request();
    $this->assertTrue($request->allowCompression);
  }

  /**
  * @covers \Papaya\Request::allowCompression
  * @backupGlobals enabled
  */
  public function testAllowCompressionCachedExpectingTrue() {
    if (!function_exists('gzencode')) {
      $this->markTestSkipped('Zlib extension not found.');
    }
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
    $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip,deflate';
    $request = new \Papaya\Request();
    $request->allowCompression();
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
    $_SERVER['HTTP_ACCEPT_ENCODING'] = NULL;
    $this->assertTrue($request->allowCompression());
  }

  /**
  * @covers \Papaya\Request::allowCompression
  * @backupGlobals enabled
  */
  public function testAllowCompressionExpectingFalse() {
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
    $_SERVER['HTTP_ACCEPT_ENCODING'] = NULL;
    $request = new \Papaya\Request();
    $this->assertFalse($request->allowCompression());
  }

  /**
  * @covers \Papaya\Request::allowEsi
  * @backupGlobals enabled
  */
  public function testAllowEsiExpectingFalse() {
    $request = new \Papaya\Request();
    $this->assertFalse($request->allowEsi());
  }

  /**
  * @covers \Papaya\Request::allowEsi
  * @backupGlobals enabled
  */
  public function testAllowEsiExpectingTrue() {
    $_SERVER['HTTP_X_PAPAYA_ESI'] = 'yes';
    $request = new \Papaya\Request();
    $this->assertTrue($request->allowEsi());
  }

  /**
   * @covers \Papaya\Request::validateBrowserCache
   * @dataProvider provideValidBrowserCacheData
   * @backupGlobals enabled
   * @param string $eTag
   * @param string $cacheTime
   */
  public function testValidateBrowserCacheExpectingTrue($eTag, $cacheTime) {
    $_SERVER['HTTP_IF_NONE_MATCH'] = $eTag;
    $_SERVER['HTTP_IF_MODIFIED_SINCE'] = $cacheTime;
    $request = new \Papaya\Request();
    $this->assertTrue(
      $request->validateBrowserCache('success', gmmktime(12, 0, 0, 07, 15, 2000))
    );
  }

  /**
   * @covers \Papaya\Request::validateBrowserCache
   * @dataProvider provideInvalidBrowserCacheData
   * @backupGlobals enabled
   * @param string $eTag
   * @param string $cacheTime
   */
  public function testValidateBrowserCacheExpectingFalse($eTag, $cacheTime) {
    $_SERVER['HTTP_IF_NONE_MATCH'] = $eTag;
    $_SERVER['HTTP_IF_MODIFIED_SINCE'] = $cacheTime;
    $request = new \Papaya\Request();
    $this->assertFalse(
      $request->validateBrowserCache('success', gmmktime(12, 0, 0, 07, 15, 2000))
    );
  }

  /**
   * @covers \Papaya\Request::content
   * @covers \Papaya\Request::__get
   */
  public function testContentGetSet() {
    $content = $this->createMock(\Papaya\Request\Content::class);
    $request = new \Papaya\Request();
    $request->content($content);
    $this->assertSame($content, $request->content);
  }

  /**
   * @covers \Papaya\Request::content
   */
  public function testContentImplicitCreate() {
    $request = new \Papaya\Request();
    $this->assertInstanceOf(\Papaya\Request\Content::class, $request->content());
  }

  /**
   * @covers \Papaya\Request::content
   * @covers \Papaya\Request::__get
   */
  public function testGetPropertyContent() {
    $content = $this
      ->getMockBuilder(\Papaya\Request\Content::class)
      ->setMethods(array('__toString'))
      ->getMock();
    $content
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('test'));
    $request = new \Papaya\Request();
    $request->content($content);
    $this->assertEquals('test', (string)$request->content);
  }

  /**
   * @covers \Papaya\Request::content
   * @covers \Papaya\Request::__get
   */
  public function testGetPropertyContentLength() {
    $content = $this->createMock(\Papaya\Request\Content::class);
    $content
      ->expects($this->once())
      ->method('length')
      ->will($this->returnValue(42));
    $request = new \Papaya\Request();
    $request->content($content);
    $this->assertEquals(42, $request->contentLength);
  }

  /***********************************
  * Fixtures
  ***********************************/

  public function getRequestParserMockFixture() {
    $parser = $this->createMock(\Papaya\Request\Parser::class);
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

