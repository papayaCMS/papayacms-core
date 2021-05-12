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

namespace Papaya\UI {

  use Papaya\Request;
  use Papaya\Request\Parameters as RequestParameters;
  use Papaya\TestCase;
  use Papaya\URL;

  require_once __DIR__.'/../../../bootstrap.php';

  /**
   * @covers \Papaya\UI\Reference
   */
  class ReferenceTest extends TestCase {

    public function testConstructorWithUrl() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|URL $url */
      $url = $this->createMock(URL::class);
      $reference = new Reference($url);
      $this->assertSame($url, $reference->url());
    }

    public function testStaticFunctionCreate() {
      $this->assertInstanceOf(
        Reference::class,
        Reference::create()
      );
    }

    public function testStaticFunctionCreateWithUrl() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|URL $url */
      $url = $this->createMock(URL::class);
      $reference = Reference::create($url);
      $this->assertSame($url, $reference->url());
    }

    public function testValidGetAfterSetExpectingFalse() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|URL $url */
      $url = $this->createMock(URL::class);
      $reference = Reference::create($url);
      $reference->valid(FALSE);
      $this->assertFalse($reference->valid());
    }

    public function testValidGetAfterSetExpectingTrue() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|URL $url */
      $url = $this->createMock(URL::class);
      $reference = Reference::create($url);
      $reference->valid(TRUE);
      $this->assertTrue($reference->valid());
    }

    public function testLoadRequest() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|URL $url */
      $url = $this->createMock(URL::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Request $request */
      $request = $this->createMock(Request::class);
      $request
        ->expects($this->once())
        ->method('getUrl')
        ->willReturn($url);
      $request
        ->expects($this->once())
        ->method('getParameterGroupSeparator')
        ->willReturn('/');
      $reference = new Reference();
      $reference->load($request);
      $this->assertNotSame(
        $url, $reference->url()
      );
      $this->assertSame(
        '/',
        $reference->getParameterGroupSeparator()
      );
    }

    public function testPrepare() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|URL $url */
      $url = $this->createMock(URL::class);
      $request = $this->createMock(Request::class);
      $request
        ->expects($this->once())
        ->method('getUrl')
        ->willReturn($url);
      $request
        ->expects($this->once())
        ->method('getParameterGroupSeparator')
        ->willReturn('/');
      $reference = new Reference();
      $reference->papaya(
        $this->mockPapaya()->application(
          ['Request' => $request]
        )
      );
      $this->assertNotSame(
        $url, $reference->url()
      );
    }

    public function testMagicMethodToString() {
      $url = new URL('http://www.sample.tld/target/file.html');
      $reference = new Reference();
      $reference->url($url);
      $this->assertEquals(
        'http://www.sample.tld/target/file.html', (string)$reference
      );
    }

    public function testGetRelative() {
      $url = new URL('http://www.sample.tld/target/file.html');
      $currentUrl = new URL('http://www.sample.tld/source/file.html');
      $reference = new Reference();
      $reference->url($url);
      $this->assertEquals(
        '../target/file.html', $reference->getRelative($currentUrl)
      );
    }

    public function testGetRelativeWithInvalidReference() {
      $reference = new Reference();
      $reference->valid(FALSE);
      $this->assertEquals(
        '', $reference->getRelative()
      );
    }

    public function testGetRelativeAfterSettingParameters() {
      $url = new URL('http://www.sample.tld/target/file.html');
      $currentUrl = new URL('http://www.sample.tld/source/file.html');
      $reference = new Reference();
      $reference->url($url);
      $reference->setParameters(['foo' => 'bar']);
      $this->assertEquals(
        '../target/file.html?foo=bar', $reference->getRelative($currentUrl)
      );
    }

    public function testGetRelativeWithoutQueryString() {
      $url = new URL('http://www.sample.tld/target/file.html');
      $currentUrl = new URL('http://www.sample.tld/source/file.html');
      $reference = new Reference();
      $reference->url($url);
      $reference->setParameters(['foo' => 'bar']);
      $this->assertEquals(
        '../target/file.html', $reference->getRelative($currentUrl, FALSE)
      );
    }

    public function testGet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|URL $url */
      $url = $this->createMock(URL::class);
      $url
        ->expects($this->once())
        ->method('getPathUrl')
        ->willReturn('http://www.sample.tld/path/file.html');
      $reference = new Reference();
      $reference->url($url);
      $this->assertEquals(
        'http://www.sample.tld/path/file.html',
        $reference->get()
      );
    }

    public function testGetRemoveSessionIdFromPath() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|URL $url */
      $url = $this->createMock(URL::class);
      $url
        ->expects($this->once())
        ->method('getPathUrl')
        ->willReturn('http://www.sample.tld/sid123456/path/file.html');
      $reference = new Reference();
      $reference->url($url);
      $this->assertEquals(
        'http://www.sample.tld/path/file.html',
        $reference->get(TRUE)
      );
    }

    public function testGetWithQueryString() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|URL $url */
      $url = $this->createMock(URL::class);
      $url
        ->expects($this->once())
        ->method('getPathUrl')
        ->willReturn('http://www.sample.tld/path/file.html');
      $reference = new Reference();
      $reference->url($url);
      $reference->setParameters(['arg' => 1]);
      $this->assertEquals(
        'http://www.sample.tld/path/file.html?arg=1',
        $reference->get()
      );
    }

    public function testGetWithQueryStringRemoveSessionIdFromParameters() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|URL $url */
      $url = $this->createMock(URL::class);
      $url
        ->expects($this->once())
        ->method('getPathUrl')
        ->willReturn('http://www.sample.tld/sid123456/path/file.html');
      $reference = new Reference();
      $reference->url($url);
      $reference->setParameters(['arg' => 1, 'sid' => '1234']);
      $this->assertEquals(
        'http://www.sample.tld/path/file.html?arg=1',
        $reference->get(TRUE)
      );
    }

    public function testGetWithFragment() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|URL $url */
      $url = $this->createMock(URL::class);
      $url
        ->expects($this->atLeastOnce())
        ->method('getPathUrl')
        ->willReturn('http://www.sample.tld/path/file.html');
      $url
        ->expects($this->atLeastOnce())
        ->method('setFragment')
        ->with('anchor');
      $url->expects($this->atLeastOnce())
        ->method('__call')
        ->with('getFragment')
        ->willReturn('anchor');
      $reference = new Reference();
      $reference->url($url);
      $reference->setFragment('#anchor');
      $this->assertEquals(
        'http://www.sample.tld/path/file.html#anchor',
        $reference->get()
      );
    }

    public function testGetWithInvalidReference() {
      $reference = new Reference();
      $reference->valid(FALSE);
      $this->assertEquals(
        '', $reference->get()
      );
    }

    public function testUrlGetAfterSet() {
      $reference = new Reference();
      /** @var \PHPUnit_Framework_MockObject_MockObject|URL $url */
      $url = $this->createMock(URL::class);
      $this->assertSame(
        $url,
        $reference->url($url)
      );
    }

    public function testUrlImplicitCreate() {
      $reference = new Reference();
      $reference->papaya($this->mockPapaya()->application());
      $this->assertInstanceOf(
        URL::class,
        $reference->url()
      );
    }

    public function testGetParameterGroupSeparator() {
      $reference = new Reference();
      $reference->papaya($this->mockPapaya()->application());
      $this->assertEquals(
        '[]',
        $reference->getParameterGroupSeparator()
      );
    }

    /**
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
        $reference->getParameterGroupSeparator()
      );
    }

    public function testSetParameterGroupSeparatorWithInvalidSeparator() {
      $reference = new Reference();
      $this->expectException(\InvalidArgumentException::class);
      $this->expectExceptionMessage('Invalid parameter level separator: X');
      $reference->setParameterGroupSeparator('X');
    }

    public function testGetParametersWithImplicitCreate() {
      $reference = new Reference();
      $parameters = $reference->getParameters();
      $this->assertInstanceOf(RequestParameters::class, $parameters);
    }

    public function testSetParametersWithGroup() {
      $reference = new Reference();
      $this->assertSame(
        $reference,
        $reference->setParameters(
          [
            'cmd' => 'show',
            'id' => 1
          ],
          'test'
        )
      );
      $parameters = $reference->getParameters();
      $this->assertEquals(
        [
          'test' => [
            'cmd' => 'show',
            'id' => 1
          ]
        ],
        (array)$parameters
      );
    }

    public function testSetParametersTwoTimes() {
      $reference = new Reference();
      $reference->setParameters(
        [
          'test' => ['mode' => 'sample']
        ]
      );
      $reference->setParameters(
        [
          'cmd' => 'show',
          'id' => 1
        ],
        'test'
      );
      $parameters = $reference->getParameters();
      $this->assertEquals(
        [
          'test' => [
            'mode' => 'sample',
            'cmd' => 'show',
            'id' => 1
          ]
        ],
        (array)$parameters
      );
    }

    public function testSetParametersWithParametersObjectAndGroup() {
      $reference = new Reference();
      $reference->setParameters(
        new RequestParameters(['mode' => 'sample']),
        'test'
      );
      $parameters = $reference->getParameters();
      $this->assertEquals(
        [
          'test' => [
            'mode' => 'sample'
          ]
        ],
        (array)$parameters
      );
    }

    public function testSetParametersWithInvalidData() {
      $reference = new Reference();
      $this->assertSame(
        $reference,
        $reference->setParameters(
          NULL,
          'test'
        )
      );
      $parameters = $reference->getParameters();
      $this->assertSame(
        [],
        (array)$parameters
      );
    }

    public function testSetParametersWithoutGroup() {
      $reference = new Reference();
      $this->assertSame(
        $reference,
        $reference->setParameters(
          [
            'cmd' => 'show',
            'id' => 1
          ]
        )
      );
      $parameters = $reference->getParameters();
      $this->assertEquals(
        [
          'cmd' => 'show',
          'id' => 1
        ],
        (array)$parameters
      );
    }

    public function testGetParameters() {
      $reference = new Reference();
      $reference->setParameters(
        [
          'cmd' => 'show',
          'id' => 1
        ]
      );
      $this->assertEquals(
        new RequestParameters(
          [
            'cmd' => 'show',
            'id' => 1
          ]
        ),
        $reference->getParameters()
      );
    }

    /**
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

    public function testGetQueryStringEmpty() {
      $reference = new Reference();
      $this->assertEquals(
        '',
        $reference->getQueryString()
      );
    }

    public function testGetParametersList() {
      $reference = new Reference();
      $reference
        ->setParameterGroupSeparator('/')
        ->setParameters(['foo' => 'bar'], 'foobar');
      $this->assertSame(
        ['foobar/foo' => 'bar'],
        $reference->getParametersList()
      );
    }

    public function testGetParametersListWhileEmpty() {
      $reference = new Reference();
      $this->assertSame(
        [], $reference->getParametersList()
      );
    }

    /**
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
        $reference->getBasePath()
      );
    }

    /**
     * @dataProvider setRelativeUrlDataProvider
     * @param string $expected
     * @param string $relativeUrl
     */
    public function testSetRelative($expected, $relativeUrl) {
      $reference = new Reference();
      $reference->url(new URL('http://www.sample.tld/path/file.html?foo=bar'));
      $reference->setRelative($relativeUrl);
      $this->assertEquals(
        $expected,
        $reference->get()
      );
    }

    public function testMagicMethodClone() {
      $reference = new Reference();
      /** @var \PHPUnit_Framework_MockObject_MockObject|URL $url */
      $url = $this->createMock(URL::class);
      $reference->url($url);
      $reference->setParameters(['foo' => 'bar']);
      $clone = clone $reference;
      $this->assertInstanceOf(URL::class, $clone->url());
      $this->assertNotSame($reference->url(), $clone->url());
      $this->assertNotSame($reference->getParameters(), $clone->getParameters());
    }

    /****************************************
     * Data Provider
     ****************************************/

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

    public static function getQueryStringDataProvider() {
      return [
        [
          '[]',
          NULL,
          ['cmd' => 'show', 'id' => 1],
          '?cmd=show&id=1'
        ],
        [
          '[]',
          'tt',
          ['cmd' => 'show', 'id' => 1],
          '?tt[cmd]=show&tt[id]=1'
        ],
        [
          '',
          'tt',
          ['cmd' => 'show', 'id' => 1],
          '?tt[cmd]=show&tt[id]=1'
        ],
        [
          ':',
          'tt',
          ['cmd' => 'show', 'id' => 1],
          '?tt:cmd=show&tt:id=1'
        ]
      ];
    }

    public static function setBasePathDataProvider() {
      return [
        [
          '/samplepath/',
          '/samplepath/'
        ],
        [
          'samplepath',
          '/samplepath/'
        ],
        [
          '',
          '/'
        ]
      ];
    }

    public static function setRelativeUrlDataProvider() {
      return [
        ['http://www.sample.tld/path/script.php', 'script.php'],
        ['http://www.sample.tld/script.php', '../script.php'],
        ['http://www.sample.tld/script.php', '/script.php'],
        ['http://www.sample.tld/', '../'],
        ['http://www.foobar.tld/script.php', 'http://www.foobar.tld/script.php']
      ];
    }
  }
}
