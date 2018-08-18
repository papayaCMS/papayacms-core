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

namespace Papaya\UI;

require_once __DIR__.'/../../../bootstrap.php';

class ReferenceTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Reference::__construct
   */
  public function testConstructorWithUrl() {
    $url = $this->createMock(\Papaya\URL::class);
    $reference = new Reference($url);
    $this->assertSame($url, $reference->url());
  }

  /**
   * @covers \Papaya\UI\Reference::create
   */
  public function testStaticFunctionCreate() {
    $this->assertInstanceOf(
      Reference::class,
      Reference::create()
    );
  }

  /**
   * @covers \Papaya\UI\Reference::create
   */
  public function testStaticFunctionCreateWithUrl() {
    $url = $this->createMock(\Papaya\URL::class);
    $reference = Reference::create($url);
    $this->assertSame($url, $reference->url());
  }

  /**
   * @covers \Papaya\UI\Reference::valid
   */
  public function testValidGetAfterSetExpectingFalse() {
    $url = $this->createMock(\Papaya\URL::class);
    $reference = Reference::create($url);
    $reference->valid(FALSE);
    $this->assertFalse($reference->valid());
  }

  /**
   * @covers \Papaya\UI\Reference::valid
   */
  public function testValidGetAfterSetExpectingTrue() {
    $url = $this->createMock(\Papaya\URL::class);
    $reference = Reference::create($url);
    $reference->valid(TRUE);
    $this->assertTrue($reference->valid());
  }

  /**
   * @covers \Papaya\UI\Reference::load
   */
  public function testLoadRequest() {
    $url = $this->createMock(\Papaya\URL::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Request $request */
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getUrl')
      ->will($this->returnValue($url));
    $request
      ->expects($this->once())
      ->method('getParameterGroupSeparator')
      ->will($this->returnValue('/'));
    $reference = new Reference();
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
   * @covers \Papaya\UI\Reference::prepare
   */
  public function testPrepare() {
    $url = $this->createMock(\Papaya\URL::class);
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getUrl')
      ->will($this->returnValue($url));
    $request
      ->expects($this->once())
      ->method('getParameterGroupSeparator')
      ->will($this->returnValue('/'));
    $reference = new Reference();
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
   * @covers \Papaya\UI\Reference::__toString
   * @covers \Papaya\UI\Reference::getRelative
   */
  public function testMagicMethodToString() {
    $url = new \Papaya\URL('http://www.sample.tld/target/file.html');
    $reference = new Reference();
    $reference->url($url);
    $this->assertEquals(
      'http://www.sample.tld/target/file.html', (string)$reference
    );
  }

  /**
   * @covers \Papaya\UI\Reference::getRelative
   */
  public function testGetRelative() {
    $url = new \Papaya\URL('http://www.sample.tld/target/file.html');
    $currentUrl = new \Papaya\URL('http://www.sample.tld/source/file.html');
    $reference = new Reference();
    $reference->url($url);
    $this->assertEquals(
      '../target/file.html', $reference->getRelative($currentUrl)
    );
  }

  /**
   * @covers \Papaya\UI\Reference::getRelative
   */
  public function testGetRelativeWithInvalidReference() {
    $reference = new Reference();
    $reference->valid(FALSE);
    $this->assertEquals(
      '', $reference->getRelative()
    );
  }

  /**
   * @covers \Papaya\UI\Reference::getRelative
   */
  public function testGetRelativeAfterSettingParameters() {
    $url = new \Papaya\URL('http://www.sample.tld/target/file.html');
    $currentUrl = new \Papaya\URL('http://www.sample.tld/source/file.html');
    $reference = new Reference();
    $reference->url($url);
    $reference->setParameters(array('foo' => 'bar'));
    $this->assertEquals(
      '../target/file.html?foo=bar', $reference->getRelative($currentUrl)
    );
  }

  /**
   * @covers \Papaya\UI\Reference::getRelative
   */
  public function testGetRelativeWithoutQueryString() {
    $url = new \Papaya\URL('http://www.sample.tld/target/file.html');
    $currentUrl = new \Papaya\URL('http://www.sample.tld/source/file.html');
    $reference = new Reference();
    $reference->url($url);
    $reference->setParameters(array('foo' => 'bar'));
    $this->assertEquals(
      '../target/file.html', $reference->getRelative($currentUrl, FALSE)
    );
  }

  /**
   * @covers \Papaya\UI\Reference::get
   */
  public function testGet() {
    $url = $this->createMock(\Papaya\URL::class);
    $url->expects($this->once())
      ->method('getPathUrl')
      ->will($this->returnValue('http://www.sample.tld/path/file.html'));
    $reference = new Reference();
    $reference->url($url);
    $this->assertEquals(
      'http://www.sample.tld/path/file.html',
      $reference->get()
    );
  }

  /**
   * @covers \Papaya\UI\Reference::get
   */
  public function testGetRemoveSessionIdFromPath() {
    $url = $this->createMock(\Papaya\URL::class);
    $url->expects($this->once())
      ->method('getPathUrl')
      ->will($this->returnValue('http://www.sample.tld/sid123456/path/file.html'));
    $reference = new Reference();
    $reference->url($url);
    $this->assertEquals(
      'http://www.sample.tld/path/file.html',
      $reference->get(TRUE)
    );
  }

  /**
   * @covers \Papaya\UI\Reference::get
   */
  public function testGetWithQueryString() {
    $url = $this->createMock(\Papaya\URL::class);
    $url->expects($this->once())
      ->method('getPathUrl')
      ->will($this->returnValue('http://www.sample.tld/path/file.html'));
    $reference = new Reference();
    $reference->url($url);
    $reference->setParameters(array('arg' => 1));
    $this->assertEquals(
      'http://www.sample.tld/path/file.html?arg=1',
      $reference->get()
    );
  }

  /**
   * @covers \Papaya\UI\Reference::get
   */
  public function testGetWithQueryStringRemoveSessionIdFromParameters() {
    $url = $this->createMock(\Papaya\URL::class);
    $url->expects($this->once())
      ->method('getPathUrl')
      ->will($this->returnValue('http://www.sample.tld/sid123456/path/file.html'));
    $reference = new Reference();
    $reference->url($url);
    $reference->setParameters(array('arg' => 1, 'sid' => '1234'));
    $this->assertEquals(
      'http://www.sample.tld/path/file.html?arg=1',
      $reference->get(TRUE)
    );
  }

  /**
   * @covers \Papaya\UI\Reference::get
   * @covers \Papaya\UI\Reference::setFragment
   * @covers \Papaya\UI\Reference::getFragment
   */
  public function testGetWithFragment() {
    $url = $this->createMock(\Papaya\URL::class);
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
    $reference = new Reference();
    $reference->url($url);
    $reference->setFragment('#anchor');
    $this->assertEquals(
      'http://www.sample.tld/path/file.html#anchor',
      $reference->get()
    );
  }

  /**
   * @covers \Papaya\UI\Reference::get
   */
  public function testGetWithInvalidReference() {
    $reference = new Reference();
    $reference->valid(FALSE);
    $this->assertEquals(
      '', $reference->get()
    );
  }

  /**
   * @covers \Papaya\UI\Reference::url
   */
  public function testUrlGetAfterSet() {
    $reference = new Reference();
    $url = $this->createMock(\Papaya\URL::class);
    $this->assertSame(
      $url,
      $reference->url($url)
    );
  }

  /**
   * @covers \Papaya\UI\Reference::url
   */
  public function testUrlImplicitCreate() {
    $reference = new Reference();
    $reference->papaya($this->mockPapaya()->application());
    $this->assertInstanceOf(
      \Papaya\URL::class,
      $reference->url()
    );
  }

  /**
   * @covers \Papaya\UI\Reference::getParameterGroupSeparator
   */
  public function testGetParameterGroupSeparator() {
    $reference = new Reference();
    $reference->papaya($this->mockPapaya()->application());
    $this->assertEquals(
      '[]',
      $reference->getParameterGroupSeparator()
    );
  }

  /**
   * @covers \Papaya\UI\Reference::setParameterGroupSeparator
   * @dataProvider setParameterLevelSeparatorDataProvider
   * @param string $separator
   * @param string $expected
   */
  public function testSetParameterGroupSeparator($separator, $expected) {
    $reference = new Reference();
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
   * @covers \Papaya\UI\Reference::setParameterGroupSeparator
   */
  public function testSetParameterGroupSeparatorWithInvalidSeparator() {
    $reference = new Reference();
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid parameter level separator: X');
    $reference->setParameterGroupSeparator('X');
  }

  /**
   * @covers \Papaya\UI\Reference::getParameters
   */
  public function testGetParamtersWithImplizitCreate() {
    $reference = new Reference();
    $parameters = $reference->getParameters();
    $this->assertInstanceOf(\Papaya\Request\Parameters::class, $parameters);
  }

  /**
   * @covers \Papaya\UI\Reference::setParameters
   */
  public function testSetParametersWithGroup() {
    $reference = new Reference();
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
   * @covers \Papaya\UI\Reference::setParameters
   */
  public function testSetParametersTwoTimes() {
    $reference = new Reference();
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
   * @covers \Papaya\UI\Reference::setParameters
   */
  public function testSetParametersWithParametersObjectAndGroup() {
    $reference = new Reference();
    $reference->setParameters(
      new \Papaya\Request\Parameters(array('mode' => 'sample')),
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
   * @covers \Papaya\UI\Reference::setParameters
   */
  public function testSetParametersWithInvalidData() {
    $reference = new Reference();
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
   * @covers \Papaya\UI\Reference::setParameters
   */
  public function testSetParametersWithoutGroup() {
    $reference = new Reference();
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
   * @covers \Papaya\UI\Reference::getParameters
   */
  public function testGetParameters() {
    $reference = new Reference();
    $reference->setParameters(
      array(
        'cmd' => 'show',
        'id' => 1
      )
    );
    $this->assertEquals(
      new \Papaya\Request\Parameters(
        array(
          'cmd' => 'show',
          'id' => 1
        )
      ),
      $reference->getParameters()
    );
  }

  /**
   * @covers \Papaya\UI\Reference::getQueryString
   * @dataProvider getQueryStringDataProvider
   * @param string $separator
   * @param string|NULL $parameterGroup
   * @param array $parameters
   * @param string $expected
   */
  public function testGetQueryString($separator, $parameterGroup, array $parameters, $expected) {
    $reference = new Reference();
    $reference
      ->setParameterGroupSeparator($separator)
      ->setParameters($parameters, $parameterGroup);
    $this->assertSame(
      $expected,
      $reference->getQueryString()
    );
  }

  /**
   * @covers \Papaya\UI\Reference::getQueryString
   */
  public function testGetQueryStringEmpty() {
    $reference = new Reference();
    $this->assertEquals(
      '',
      $reference->getQueryString()
    );
  }

  /**
   * @covers \Papaya\UI\Reference::getParametersList
   */
  public function testGetParametersList() {
    $reference = new Reference();
    $reference
      ->setParameterGroupSeparator('/')
      ->setParameters(array('foo' => 'bar'), 'foobar');
    $this->assertSame(
      array('foobar/foo' => 'bar'),
      $reference->getParametersList()
    );
  }

  /**
   * @covers \Papaya\UI\Reference::getParametersList
   */
  public function testGetParametersListWhileEmpty() {
    $reference = new Reference();
    $this->assertSame(
      array(), $reference->getParametersList()
    );
  }

  /**
   * @covers \Papaya\UI\Reference::setBasePath
   * @dataProvider setBasePathDataProvider
   * @param string $path
   * @param string $expected
   */
  public function testSetBasePath($path, $expected) {
    $reference = new Reference();
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
   * @covers \Papaya\UI\Reference::setRelative
   * @dataProvider setRelativeUrlDataProvider
   * @param string $expected
   * @param string $relativeUrl
   */
  public function testSetRelative($expected, $relativeUrl) {
    $reference = new Reference();
    $reference->url(new \Papaya\URL('http://www.sample.tld/path/file.html?foo=bar'));
    $reference->setRelative($relativeUrl);
    $this->assertEquals(
      $expected,
      $reference->get()
    );
  }

  /**
   * @covers \Papaya\UI\Reference::__clone
   */
  public function testMagicMethodClone() {
    $reference = new Reference();
    $reference->url($this->createMock(\Papaya\URL::class));
    $reference->setParameters(array('foo' => 'bar'));
    $clone = clone $reference;
    $this->assertInstanceOf(\Papaya\URL::class, $clone->url());
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
