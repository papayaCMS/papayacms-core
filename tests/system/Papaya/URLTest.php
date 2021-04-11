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
   * @covers \Papaya\URL
   */
  class URLTest extends TestCase {

    public function testConstructor() {
      $urlObject = new URL();
      $this->assertEquals([], iterator_to_array($urlObject));
    }

    public function testConstructorWithUrl() {
      $urlObject = new URL(
        'http://www.domain.tld'
      );
      $this->assertEquals(
        [
          'scheme' => 'http',
          'host' => 'www.domain.tld'
        ],
        $this->readAttribute($urlObject, '_elements')
      );
    }

    /**
     * @dataProvider setUrlDataProvider
     * @param string $url
     * @param array $expected
     */
    public function testSetUrl($url, array $expected) {
      $urlObject = new URL();
      $urlObject->setURLString($url);
      $this->assertAttributeEquals(
        $expected, '_elements', $urlObject
      );
    }

    public function testToString() {
      $urlObject = new URL();
      $urlObject->setURLString('http://www.domain.tld');
      $this->assertEquals(
        'http://www.domain.tld', (string)$urlObject
      );
    }

    public function testToStringCapturesInvalidArgumentException() {
      $urlObject = new Url_TestProxy_ForToString();
      $urlObject->exception = new \InvalidArgumentException();
      $this->assertEquals('', (string)$urlObject);
    }

    public function testToStringCapturesBadMethodCallException() {
      $urlObject = new Url_TestProxy_ForToString();
      $urlObject->exception = new \BadMethodCallException();
      $this->assertEquals('', (string)$urlObject);
    }


    /**
     * @dataProvider getUrlDataProvider
     * @param string $url
     */
    public function testGetUrl($url) {
      $urlObject = new URL();
      $urlObject->setURLString($url);
      $this->assertEquals(
        $url,
        $urlObject->getURL()
      );
    }

    /**
     * @dataProvider getUrlDataProvider
     */
    public function testGetPathUrl() {
      $urlObject = new URL();
      $urlObject->setURLString('https://username:password@www.domain.tld:8080/path?arg=value#anchor');
      $this->assertEquals(
        'https://username:password@www.domain.tld:8080/path',
        $urlObject->getPathURL()
      );
    }

    /**
     * @dataProvider getHostUrlDataProvider
     * @param string $url
     * @param string $hostUrl
     */
    public function testGetHostUrl($url, $hostUrl) {
      $urlObject = new URL();
      $urlObject->setURLString($url);
      $this->assertEquals(
        $hostUrl,
        $urlObject->getHostURL()
      );
    }

    public function testSetScheme() {
      $urlObject = new URL();
      $urlObject->setURLString('http://www.domain.tld');
      $urlObject->setScheme('ftp');
      $this->assertAttributeEquals(
        [
          'scheme' => 'ftp',
          'host' => 'www.domain.tld'
        ],
        '_elements',
        $urlObject
      );
      $this->assertEquals(
        'ftp://www.domain.tld', $urlObject->getURL()
      );
    }

    public function testSetSchemeExpectingException() {
      $urlObject = new URL();
      $urlObject->setURLString('http://www.domain.tld');
      $this->expectException(\InvalidArgumentException::class);
      $urlObject->setScheme('http://');
    }

    /**
     * @dataProvider setHostDataProvider
     * @param string $url
     * @param string $host
     * @param array $elements
     * @param string $expected
     */
    public function testSetHost($url, $host, $elements, $expected) {
      $urlObject = new URL();
      $urlObject->setURLString($url);
      $urlObject->setHost($host);
      $this->assertAttributeEquals(
        $elements,
        '_elements',
        $urlObject
      );
      $this->assertEquals($expected, $urlObject->getURL());
    }

    /**
     * @dataProvider setHostDataProviderExceptions
     * @param string $host
     */
    public function testSetHostExpectingException($host) {
      $this->expectException(\InvalidArgumentException::class);
      $urlObject = new URL();
      $urlObject->setURLString('http://www.domain.tld');
      $urlObject->setHost($host);
    }

    public function testSetPort() {
      $urlObject = new URL();
      $urlObject->setURLString('http://www.domain.tld:80');
      $urlObject->setPort('8080');
      $this->assertAttributeEquals(
        [
          'scheme' => 'http',
          'host' => 'www.domain.tld',
          'port' => '8080',
        ],
        '_elements',
        $urlObject
      );
      $this->assertEquals(
        'http://www.domain.tld:8080', $urlObject->getURL()
      );
    }

    public function testSetPortExpectingException() {
      $urlObject = new URL();
      $urlObject->setURLString('http://www.domain.tld');
      $this->expectException(\InvalidArgumentException::class);
      $urlObject->setPort('not-a-number-123');
    }

    public function testSetPath() {
      $urlObject = new URL();
      $urlObject->setURLString('http://www.domain.tld/foo');
      $urlObject->setPath('/bar');
      $this->assertEquals(
        [
          'scheme' => 'http',
          'host' => 'www.domain.tld',
          'path' => '/bar'
        ],
        iterator_to_array($urlObject)
      );
      $this->assertEquals(
        'http://www.domain.tld/bar', $urlObject->getURL()
      );
    }

    public function testSetPathExpectingException() {
      $urlObject = new URL();
      $urlObject->setURLString('http://www.domain.tld');
      $this->expectException(\InvalidArgumentException::class);
      $urlObject->setPath('bar');
    }

    public function testSetQuery() {
      $urlObject = new URL();
      $urlObject->setURLString('http://www.domain.tld');
      $urlObject->setQuery('foo=bar');
      $this->assertEquals(
        [
          'scheme' => 'http',
          'host' => 'www.domain.tld',
          'query' => 'foo=bar'
        ],
        iterator_to_array($urlObject)
      );
      $this->assertEquals(
        'http://www.domain.tld?foo=bar', $urlObject->getURL()
      );
    }

    public function testSetQueryExpectingException() {
      $urlObject = new URL();
      $urlObject->setURLString('http://www.domain.tld');
      $this->expectException(\InvalidArgumentException::class);
      $urlObject->setQuery('?bar');
    }

    /**
     * @dataProvider provideValidDataForMagicMethodCall
     * @param mixed $expected
     * @param string $method
     */
    public function testMagicMethodCall($expected, $method) {
      $urlObject = new URL('http://username:password@hostname:8080/path?arg=value#anchor');
      $this->assertEquals(
        $expected, $urlObject->$method()
      );
    }

    public function testMagicMethodCallExpectingException() {
      $urlObject = new URL('http://username:password@hostname:8080/path?arg=value#anchor');
      $this->expectException(\BadMethodCallException::class);
      /** @noinspection PhpUndefinedMethodInspection */
      $urlObject->invalidMethod();
    }

    /**
     * @dataProvider provideValidDataForMagicMethodGet
     * @param mixed $expected
     * @param string $property
     */
    public function testMagicMethodGet($expected, $property) {
      $urlObject = new URL('http://username:password@hostname:8080/path?arg=value#anchor');
      $this->assertTrue(isset($urlObject->$property));
      $this->assertEquals(
        $expected, $urlObject->$property
      );
    }

    public function testMagicMethodGetExpectingException() {
      $urlObject = new URL('http://username:password@hostname:8080/path?arg=value#anchor');
      $this->expectException(\BadMethodCallException::class);
      /** @noinspection PhpUndefinedFieldInspection */
      $urlObject->invalidProperty;
    }

    /**
     * @dataProvider provideValidDataForMagicMethodSet
     * @param mixed $expected
     * @param string $property
     */
    public function testMagicMethodSet($expected, $property) {
      $urlObject = new URL('http://username:password@hostname:8080/path?arg=value#anchor');
      $urlObject->$property = $expected;
      $this->assertTrue(isset($urlObject->$property));
      $this->assertEquals(
        $expected, $urlObject->$property
      );
    }

    /**
     * @dataProvider provideInvalidDataForMagicMethodSet
     * @param string $property
     * @param mixed $value
     */
    public function testMagicMethodSetWithInvalidValueExpectingException($property, $value) {
      $urlObject = new URL('http://test.tld');
      $this->expectException(\InvalidArgumentException::class);
      $urlObject->$property = $value;
    }

    public function testMagicMethodUnsetExpectingException() {
      $urlObject = new URL('http://test.tld');
      $this->expectException(\BadMethodCallException::class);
      unset($urlObject->password);
    }

    public function testMagicMethodSetReadonlyExpectingException() {
      $urlObject = new URL('http://username:password@hostname:8080/path?arg=value#anchor');
      $this->expectException(\BadMethodCallException::class);
      $urlObject->user = 'readonly';
    }

    public function testMagicMethodSetInvalidExpectingException() {
      $urlObject = new URL('http://username:password@hostname:8080/path?arg=value#anchor');
      $this->expectException(\BadMethodCallException::class);
      /** @noinspection PhpUndefinedFieldInspection */
      $urlObject->invalidProperty = 'non existing';
    }

    /*************************************
     * Data Providers
     *************************************/

    public static function getUrlDataProvider() {
      return [
        ['http://username:password@hostname/path?arg=value#anchor'],
        ['http://username:password@hostname/path?arg=value'],
        ['http://username:password@hostname/path'],
        ['http://username:password@hostname']
      ];
    }

    public static function getHostUrlDataProvider() {
      return [
        [
          '',
          ''
        ],
        [
          'http://username:password@hostname:8080/path?arg=value#anchor',
          'http://username:password@hostname:8080'
        ],
        [
          'http://username:password@hostname/path?arg=value#anchor',
          'http://username:password@hostname'
        ],
        [
          'http://@hostname/path?arg=value#anchor',
          'http://hostname'
        ],
        [
          'http://127.0.0.1/index.html',
          'http://127.0.0.1',
        ]
      ];
    }

    public static function setUrlDataProvider() {
      return [
        [
          '',
          []
        ],
        [
          'http://www.sample.tld',
          [
            'scheme' => 'http',
            'host' => 'www.sample.tld'
          ]
        ],
        [
          'http://www.sample.tld:8080',
          [
            'scheme' => 'http',
            'host' => 'www.sample.tld',
            'port' => '8080'
          ]
        ],
        [
          'http://username:password@hostname/path?arg=value#anchor',
          [
            'scheme' => 'http',
            'host' => 'hostname',
            'user' => 'username',
            'pass' => 'password',
            'path' => '/path',
            'query' => 'arg=value',
            'fragment' => 'anchor'
          ]
        ]
      ];
    }

    public static function setHostDataProvider() {
      return [
        'Valid: hostname' => [
          'http://www.domain.tld',
          'www.example.com',
          [
            'scheme' => 'http',
            'host' => 'www.example.com'
          ],
          'http://www.example.com',
        ],
        'Valid: IP' => [
          'http://www.example.com',
          '127.0.0.1',
          [
            'scheme' => 'http',
            'host' => '127.0.0.1'
          ],
          'http://127.0.0.1',
        ],
        [
          'https://UPPERCASE.tld',
          'UPPERCASE.tld',
          [
            'scheme' => 'https',
            'host' => 'UPPERCASE.tld'
          ],
          'https://UPPERCASE.tld'
        ]
      ];
    }

    public static function setHostDataProviderExceptions() {
      return [
        'no FQH' => ['invalidFQH'],
        'no IP' => ['1.2.4'],
        'numeric tld' => ['hostname.123'],
      ];
    }

    public static function provideValidDataForMagicMethodCall() {
      return [
        ['http', 'getScheme'],
        ['password', 'getPassword'],
        ['password', 'getPass'],
        ['hostname', 'getHost'],
        ['8080', 'getPort'],
        ['/path', 'getPath'],
        ['arg=value', 'getQuery'],
        ['anchor', 'getFragment']
      ];
    }

    public static function provideValidDataForMagicMethodGet() {
      return [
        ['http', 'scheme'],
        ['password', 'password'],
        ['password', 'pass'],
        ['hostname', 'host'],
        ['8080', 'port'],
        ['/path', 'path'],
        ['arg=value', 'query'],
        ['anchor', 'fragment']
      ];
    }

    public static function provideValidDataForMagicMethodSet() {
      return [
        ['https', 'scheme'],
        ['anchor', 'fragment']
      ];
    }

    public static function provideInvalidDataForMagicMethodSet() {
      return [
        ['scheme', 'nöö'],
        ['fragment', '#']
      ];
    }
  }

  class Url_TestProxy_ForToString extends URL {

    /**
     * @var \Exception
     */
    public $exception;

    /**
     * @return string|void
     * @throws \Exception
     */
    public function getURL() {
      throw $this->exception;
    }
  }
}
