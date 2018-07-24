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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaUiReferenceTest extends PapayaTestCase {

  /**
  * @covers PapayaUiReference::__construct
  */
  public function testConstructorWithUrl() {
    $url = $this->createMock(Url::class);
    $reference = new PapayaUiReference($url);
    $this->assertSame($url, $reference->url());
  }

  /**
  * @covers PapayaUiReference::create
  */
  public function testStaticFunctionCreate() {
    $this->assertInstanceOf(
      PapayaUiReference::class,
      PapayaUiReference::create()
    );
  }

  /**
  * @covers PapayaUiReference::create
  */
  public function testStaticFunctionCreateWithUrl() {
    $url = $this->createMock(Url::class);
    $reference = PapayaUiReference::create($url);
    $this->assertSame($url, $reference->url());
  }

  /**
  * @covers PapayaUiReference::valid
  */
  public function testValidGetAfterSetExpectingFalse() {
    $url = $this->createMock(Url::class);
    $reference = PapayaUiReference::create($url);
    $reference->valid(FALSE);
    $this->assertFalse($reference->valid());
  }

  /**
  * @covers PapayaUiReference::valid
  */
  public function testValidGetAfterSetExpectingTrue() {
    $url = $this->createMock(Url::class);
    $reference = PapayaUiReference::create($url);
    $reference->valid(TRUE);
    $this->assertTrue($reference->valid());
  }

  /**
  * @covers PapayaUiReference::load
  */
  public function testLoadRequest() {
    $url = $this->createMock(Url::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaRequest $request */
    $request = $this->createMock(PapayaRequest::class);
    $request
      ->expects($this->once())
      ->method('getUrl')
      ->will($this->returnValue($url));
    $request
      ->expects($this->once())
      ->method('getParameterGroupSeparator')
      ->will($this->returnValue('/'));
    $reference = new PapayaUiReference();
    $reference->load($request);
    $this->assertNotSame(
      $url, $reference->url()
    );
    $this->assertSame(
      '/',
      $this->readAttribute($reference, '_parameterGroupSeparator')
    );
  }

  /**
  * @covers PapayaUiReference::prepare
  */
  public function testPrepare() {
    $url = $this->createMock(Url::class);
    $request = $this->createMock(PapayaRequest::class);
    $request
      ->expects($this->once())
      ->method('getUrl')
      ->will($this->returnValue($url));
    $request
      ->expects($this->once())
      ->method('getParameterGroupSeparator')
      ->will($this->returnValue('/'));
    $reference = new PapayaUiReference();
    $reference->papaya(
      $this->mockPapaya()->application(
        array('Request' => $request)
      )
    );
    $this->assertNotSame(
      $url, $reference->url()
    );
  }

  /**
  * @covers PapayaUiReference::__toString
  * @covers PapayaUiReference::getRelative
  */
  public function testMagicMethodToString() {
    $url = new Url('http://www.sample.tld/target/file.html');
    $reference = new PapayaUiReference();
    $reference->url($url);
    $this->assertEquals(
      'http://www.sample.tld/target/file.html', (string)$reference
    );
  }

  /**
  * @covers PapayaUiReference::getRelative
  */
  public function testGetRelative() {
    $url = new Url('http://www.sample.tld/target/file.html');
    $currentUrl = new Url('http://www.sample.tld/source/file.html');
    $reference = new PapayaUiReference();
    $reference->url($url);
    $this->assertEquals(
      '../target/file.html', $reference->getRelative($currentUrl)
    );
  }

  /**
  * @covers PapayaUiReference::getRelative
  */
  public function testGetRelativeWithInvalidReference() {
    $reference = new PapayaUiReference();
    $reference->valid(FALSE);
    $this->assertEquals(
      '', $reference->getRelative()
    );
  }

  /**
  * @covers PapayaUiReference::getRelative
  */
  public function testGetRelativeAfterSettingParameters() {
    $url = new Url('http://www.sample.tld/target/file.html');
    $currentUrl = new Url('http://www.sample.tld/source/file.html');
    $reference = new PapayaUiReference();
    $reference->url($url);
    $reference->setParameters(array('foo' => 'bar'));
    $this->assertEquals(
      '../target/file.html?foo=bar', $reference->getRelative($currentUrl)
    );
  }

  /**
  * @covers PapayaUiReference::getRelative
  */
  public function testGetRelativeWithoutQueryString() {
    $url = new Url('http://www.sample.tld/target/file.html');
    $currentUrl = new Url('http://www.sample.tld/source/file.html');
    $reference = new PapayaUiReference();
    $reference->url($url);
    $reference->setParameters(array('foo' => 'bar'));
    $this->assertEquals(
      '../target/file.html', $reference->getRelative($currentUrl, FALSE)
    );
  }

  /**
  * @covers PapayaUiReference::get
  */
  public function testGet() {
    $url = $this->createMock(Url::class);
    $url->expects($this->once())
        ->method('getPathUrl')
        ->will($this->returnValue('http://www.sample.tld/path/file.html'));
    $reference = new PapayaUiReference();
    $reference->url($url);
    $this->assertEquals(
      'http://www.sample.tld/path/file.html',
      $reference->get()
    );
  }

  /**
   * @covers PapayaUiReference::get
   */
  public function testGetRemoveSessionIdFromPath() {
    $url = $this->createMock(Url::class);
    $url->expects($this->once())
        ->method('getPathUrl')
        ->will($this->returnValue('http://www.sample.tld/sid123456/path/file.html'));
    $reference = new PapayaUiReference();
    $reference->url($url);
    $this->assertEquals(
      'http://www.sample.tld/path/file.html',
      $reference->get(TRUE)
    );
  }

  /**
  * @covers PapayaUiReference::get
  */
  public function testGetWithQueryString() {
    $url = $this->createMock(Url::class);
    $url->expects($this->once())
        ->method('getPathUrl')
        ->will($this->returnValue('http://www.sample.tld/path/file.html'));
    $reference = new PapayaUiReference();
    $reference->url($url);
    $reference->setParameters(array('arg' => 1));
    $this->assertEquals(
      'http://www.sample.tld/path/file.html?arg=1',
      $reference->get()
    );
  }

  /**
   * @covers PapayaUiReference::get
   */
  public function testGetWithQueryStringRemoveSessionIdFromParameters() {
    $url = $this->createMock(Url::class);
    $url->expects($this->once())
        ->method('getPathUrl')
        ->will($this->returnValue('http://www.sample.tld/sid123456/path/file.html'));
    $reference = new PapayaUiReference();
    $reference->url($url);
    $reference->setParameters(array('arg' => 1, 'sid' => '1234'));
    $this->assertEquals(
      'http://www.sample.tld/path/file.html?arg=1',
      $reference->get(TRUE)
    );
  }

  /**
  * @covers PapayaUiReference::get
  * @covers PapayaUiReference::setFragment
  * @covers PapayaUiReference::getFragment
  */
  public function testGetWithFragment() {
    $url = $this->createMock(Url::class);
    $url->expects($this->atLeastOnce())
        ->method('getPathUrl')
        ->will($this->returnValue('http://www.sample.tld/path/file.html'));
    $url->expects($this->atLeastOnce())
        ->method('setFragment')
        ->with('anchor');
    $url->expects($this->atLeastOnce())
        ->method('__call')
        ->with('getFragment')
        ->will($this->returnValue('anchor'));
    $reference = new PapayaUiReference();
    $reference->url($url);
    $reference->setFragment('#anchor');
    $this->assertEquals(
      'http://www.sample.tld/path/file.html#anchor',
      $reference->get()
    );
  }

  /**
  * @covers PapayaUiReference::get
  */
  public function testGetWithInvalidReference() {
    $reference = new PapayaUiReference();
    $reference->valid(FALSE);
    $this->assertEquals(
      '', $reference->get()
    );
  }

  /**
  * @covers PapayaUiReference::url
  */
  public function testUrlGetAfterSet() {
    $reference = new PapayaUiReference();
    $url = $this->createMock(Url::class);
    $this->assertSame(
      $url,
      $reference->url($url)
    );
  }

  /**
  * @covers PapayaUiReference::url
  */
  public function testUrlImplicitCreate() {
    $reference = new PapayaUiReference();
    $reference->papaya($this->mockPapaya()->application());
    $this->assertInstanceOf(
      Url::class,
      $reference->url()
    );
  }

  /**
  * @covers PapayaUiReference::getParameterGroupSeparator
  */
  public function testGetParameterGroupSeparator() {
    $reference = new PapayaUiReference();
    $reference->papaya($this->mockPapaya()->application());
    $this->assertEquals(
      '[]',
      $reference->getParameterGroupSeparator()
    );
  }

  /**
   * @covers PapayaUiReference::setParameterGroupSeparator
   * @dataProvider setParameterLevelSeparatorDataProvider
   * @param string $separator
   * @param string $expected
   */
  public function testSetParameterGroupSeparator($separator, $expected) {
    $reference = new PapayaUiReference();
    $this->assertSame(
      $reference,
      $reference->setParameterGroupSeparator($separator)
    );
    $this->assertEquals(
      $expected,
      $this->readAttribute($reference, '_parameterGroupSeparator')
    );
  }

  /**
  * @covers PapayaUiReference::setParameterGroupSeparator
  */
  public function testSetParameterGroupSeparatorWithInvalidSeparator() {
    $reference = new PapayaUiReference();
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid parameter level separator: X');
    $reference->setParameterGroupSeparator('X');
  }

  /**
  * @covers PapayaUiReference::getParameters
  */
  public function testGetParamtersWithImplizitCreate() {
    $reference = new PapayaUiReference();
    $parameters = $reference->getParameters();
    $this->assertInstanceOf(PapayaRequestParameters::class, $parameters);
  }

  /**
  * @covers PapayaUiReference::setParameters
  */
  public function testSetParametersWithGroup() {
    $reference = new PapayaUiReference();
    $this->assertSame(
      $reference,
      $reference->setParameters(
        array(
          'cmd' => 'show',
          'id' => 1
        ),
        'test'
      )
    );
    $parameters = $this->readAttribute($reference, '_parametersObject');
    $this->assertEquals(
      array(
        'test' => array(
          'cmd' => 'show',
          'id' => 1
        )
      ),
      (array)$parameters
    );
  }

  /**
  * @covers PapayaUiReference::setParameters
  */
  public function testSetParametersTwoTimes() {
    $reference = new PapayaUiReference();
    $reference->setParameters(
      array(
        'test' => array('mode' => 'sample')
      )
    );
    $reference->setParameters(
      array(
        'cmd' => 'show',
        'id' => 1
      ),
      'test'
    );
    $parameters = $this->readAttribute($reference, '_parametersObject');
    $this->assertEquals(
      array(
        'test' => array(
          'mode' => 'sample',
          'cmd' => 'show',
          'id' => 1
        )
      ),
      (array)$parameters
    );
  }

  /**
  * @covers PapayaUiReference::setParameters
  */
  public function testSetParametersWithParametersObjectAndGroup() {
    $reference = new PapayaUiReference();
    $reference->setParameters(
      new PapayaRequestParameters(array('mode' => 'sample')),
      'test'
    );
    $parameters = $this->readAttribute($reference, '_parametersObject');
    $this->assertEquals(
      array(
        'test' => array(
          'mode' => 'sample'
        )
      ),
      (array)$parameters
    );
  }

  /**
  * @covers PapayaUiReference::setParameters
  */
  public function testSetParametersWithInvalidData() {
    $reference = new PapayaUiReference();
    $this->assertSame(
      $reference,
      $reference->setParameters(
        NULL,
        'test'
      )
    );
    $parameters = $this->readAttribute($reference, '_parametersObject');
    $this->assertSame(
      array(),
      (array)$parameters
    );
  }

  /**
  * @covers PapayaUiReference::setParameters
  */
  public function testSetParametersWithoutGroup() {
    $reference = new PapayaUiReference();
    $this->assertSame(
      $reference,
      $reference->setParameters(
        array(
          'cmd' => 'show',
          'id' => 1
        )
      )
    );
    $parameters = $this->readAttribute($reference, '_parametersObject');
    $this->assertEquals(
      array(
        'cmd' => 'show',
        'id' => 1
      ),
      (array)$parameters
    );
  }

  /**
  * @covers PapayaUiReference::getParameters
  */
  public function testGetParameters() {
    $reference = new PapayaUiReference();
    $reference->setParameters(
      array(
        'cmd' => 'show',
        'id' => 1
      )
    );
    $this->assertEquals(
      new PapayaRequestParameters(
        array(
          'cmd' => 'show',
          'id' => 1
        )
      ),
      $reference->getParameters()
    );
  }

  /**
   * @covers PapayaUiReference::getQueryString
   * @dataProvider getQueryStringDataProvider
   * @param string $separator
   * @param string|NULL $parameterGroup
   * @param array $parameters
   * @param string $expected
   */
  public function testGetQueryString($separator, $parameterGroup, array $parameters, $expected) {
    $reference = new PapayaUiReference();
    $reference
      ->setParameterGroupSeparator($separator)
      ->setParameters($parameters, $parameterGroup);
    $this->assertSame(
      $expected,
      $reference->getQueryString()
    );
  }

  /**
  * @covers PapayaUiReference::getQueryString
  */
  public function testGetQueryStringEmpty() {
    $reference = new PapayaUiReference();
    $this->assertEquals(
      '',
      $reference->getQueryString()
    );
  }

  /**
  * @covers PapayaUiReference::getParametersList
  */
  public function testGetParametersList() {
    $reference = new PapayaUiReference();
    $reference
      ->setParameterGroupSeparator('/')
      ->setParameters(array('foo' => 'bar'), 'foobar');
    $this->assertSame(
      array('foobar/foo' => 'bar'),
      $reference->getParametersList()
    );
  }

  /**
  * @covers PapayaUiReference::getParametersList
  */
  public function testGetParametersListWhileEmpty() {
    $reference = new PapayaUiReference();
    $this->assertSame(
      array(), $reference->getParametersList()
    );
  }

  /**
   * @covers PapayaUiReference::setBasePath
   * @dataProvider setBasePathDataProvider
   * @param string $path
   * @param string $expected
   */
  public function testSetBasePath($path, $expected) {
    $reference = new PapayaUiReference();
    $this->assertSame(
      $reference,
      $reference->setBasePath($path)
    );
    $this->assertEquals(
      $expected,
      $this->readAttribute($reference, '_basePath')
    );
  }

  /**
   * @covers PapayaUiReference::setRelative
   * @dataProvider setRelativeUrlDataProvider
   * @param string $expected
   * @param string $relativeUrl
   */
  public function testSetRelative($expected, $relativeUrl) {
    $reference = new PapayaUiReference();
    $reference->url(new Url('http://www.sample.tld/path/file.html?foo=bar'));
    $reference->setRelative($relativeUrl);
    $this->assertEquals(
      $expected,
      $reference->get()
    );
  }

  /**
  * @covers PapayaUiReference::__clone
  */
  public function testMagicMethodClone() {
    $reference = new PapayaUiReference();
    $reference->url($this->createMock(Url::class));
    $reference->setParameters(array('foo' => 'bar'));
    $clone = clone $reference;
    $this->assertInstanceOf(Url::class, $clone->url());
    $this->assertNotSame($reference->url(), $clone->url());
    $this->assertNotSame($reference->getParameters(), $clone->getParameters());
  }

  /****************************************
  * Data Provider
  ****************************************/

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

  public static function getQueryStringDataProvider() {
    return array(
      array(
        '[]',
        NULL,
        array('cmd' => 'show', 'id' => 1),
        '?cmd=show&id=1'
      ),
      array(
        '[]',
        'tt',
        array('cmd' => 'show', 'id' => 1),
        '?tt[cmd]=show&tt[id]=1'
      ),
      array(
        '',
        'tt',
        array('cmd' => 'show', 'id' => 1),
        '?tt[cmd]=show&tt[id]=1'
      ),
      array(
        ':',
        'tt',
        array('cmd' => 'show', 'id' => 1),
        '?tt:cmd=show&tt:id=1'
      )
    );
  }

  public static function setBasePathDataProvider() {
    return array(
      array(
        '/samplepath/',
        '/samplepath/'
      ),
      array(
        'samplepath',
        '/samplepath/'
      ),
      array(
        '',
        '/'
      )
    );
  }

  public static function setRelativeUrlDataProvider() {
    return array(
      array('http://www.sample.tld/path/script.php', 'script.php'),
      array('http://www.sample.tld/script.php', '../script.php'),
      array('http://www.sample.tld/script.php', '/script.php'),
      array('http://www.sample.tld/', '../'),
      array('http://www.foobar.tld/script.php', 'http://www.foobar.tld/script.php')
    );
  }
}
