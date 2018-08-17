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

  class URLTest extends \PapayaTestCase {

    /**
     * @covers URL::__construct
     */
    public function testConstructor() {
      $urlObject = new URL();
      $this->assertNull($this->readAttribute($urlObject, '_elements'));
    }

    /**
     * @covers URL::__construct
     */
    public function testConstructorWithUrl() {
      $urlObject = new URL(
        'http://www.domain.tld'
      );
      $this->assertEquals(
        array(
          'scheme' => 'http',
          'host' => 'www.domain.tld'
        ),
        $this->readAttribute($urlObject, '_elements')
      );
    }

    /**
     * @covers       URL::setURLString
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

    /**
     * @covers URL::__toString
     */
    public function testToString() {
      $urlObject = new URL();
      $urlObject->setURLString('http://www.domain.tld');
      $this->assertEquals(
        'http://www.domain.tld', (string)$urlObject
      );
    }

    /**
     * @covers URL::__toString
     */
    public function testToStringCapturesInvalidArgumentException() {
      $urlObject = new Url_TestProxy_ForToString();
      $urlObject->exception = new \InvalidArgumentException();
      $this->assertEquals('', (string)$urlObject);
    }

    /**
     * @covers URL::__toString
     */
    public function testToStringCapturesBadMethodCallException() {
      $urlObject = new Url_TestProxy_ForToString();
      $urlObject->exception = new \BadMethodCallException();
      $this->assertEquals('', (string)$urlObject);
    }


    /**
     * @covers       URL::getURL
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
     * @covers       URL::getPathURL
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
     * @covers       URL::getHostURL
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

    /**
     * @covers URL::setScheme
     */
    public function testSetScheme() {
      $urlObject = new URL();
      $urlObject->setURLString('http://www.domain.tld');
      $urlObject->setScheme('ftp');
      $this->assertAttributeEquals(
        array(
          'scheme' => 'ftp',
          'host' => 'www.domain.tld'
        ),
        '_elements',
        $urlObject
      );
      $this->assertEquals(
        'ftp://www.domain.tld', $urlObject->getURL()
      );
    }

    /**
     * @covers URL::setScheme
     */
    public function testSetSchemeExpectingException() {
      $urlObject = new URL();
      $urlObject->setURLString('http://www.domain.tld');
      $this->expectException(\InvalidArgumentException::class);
      $urlObject->setScheme('http://');
    }

    /**
     * @covers       URL::setHost
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
     * @covers       URL::setHost
     * @expectedException \InvalidArgumentException
     * @dataProvider setHostDataProviderExceptions
     * @param string $host
     */
    public function testSetHostExpectingException($host) {
      $urlObject = new URL();
      $urlObject->setURLString('http://www.domain.tld');
      $urlObject->setHost($host);
    }

    /**
     * @covers URL::setPort
     */
    public function testSetPort() {
      $urlObject = new URL();
      $urlObject->setURLString('http://www.domain.tld:80');
      $urlObject->setPort('8080');
      $this->assertAttributeEquals(
        array(
          'scheme' => 'http',
          'host' => 'www.domain.tld',
          'port' => '8080',
        ),
        '_elements',
        $urlObject
      );
      $this->assertEquals(
        'http://www.domain.tld:8080', $urlObject->getURL()
      );
    }

    /**
     * @covers URL::setPort
     * @expectedException \InvalidArgumentException
     */
    public function testSetPortExpectingException() {
      $urlObject = new URL();
      $urlObject->setURLString('http://www.domain.tld');
      $urlObject->setPort('not-a-number-123');
    }

    /**
     * @covers URL::setPath
     */
    public function testSetPath() {
      $urlObject = new URL();
      $urlObject->setURLString('http://www.domain.tld/foo');
      $urlObject->setPath('/bar');
      $this->assertAttributeEquals(
        array(
          'scheme' => 'http',
          'host' => 'www.domain.tld',
          'path' => '/bar'
        ),
        '_elements',
        $urlObject
      );
      $this->assertEquals(
        'http://www.domain.tld/bar', $urlObject->getURL()
      );
    }

    /**
     * @covers URL::setPath
     */
    public function testSetPathExpectingException() {
      $urlObject = new URL();
      $urlObject->setURLString('http://www.domain.tld');
      $this->expectException(\InvalidArgumentException::class);
      $urlObject->setPath('bar');
    }

    /**
     * @covers URL::setQuery
     */
    public function testSetQuery() {
      $urlObject = new URL();
      $urlObject->setURLString('http://www.domain.tld');
      $urlObject->setQuery('foo=bar');
      $this->assertAttributeEquals(
        array(
          'scheme' => 'http',
          'host' => 'www.domain.tld',
          'query' => 'foo=bar'
        ),
        '_elements',
        $urlObject
      );
      $this->assertEquals(
        'http://www.domain.tld?foo=bar', $urlObject->getURL()
      );
    }

    /**
     * @covers URL::setQuery
     */
    public function testSetQueryExpectingException() {
      $urlObject = new URL();
      $urlObject->setURLString('http://www.domain.tld');
      $this->expectException(\InvalidArgumentException::class);
      $urlObject->setQuery('?bar');
    }

    /**
     * @covers       URL::__call
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

    /**
     * @covers URL::__call
     */
    public function testMagicMethodCallExpectingException() {
      $urlObject = new URL('http://username:password@hostname:8080/path?arg=value#anchor');
      $this->expectException(\BadMethodCallException::class);
      /** @noinspection PhpUndefinedMethodInspection */
      $urlObject->invalidMethod();
    }

    /**
     * @covers       URL::__get
     * @dataProvider provideValidDataForMagicMethodGet
     * @param mixed $expected
     * @param string $property
     */
    public function testMagicMethodGet($expected, $property) {
      $urlObject = new URL('http://username:password@hostname:8080/path?arg=value#anchor');
      $this->assertEquals(
        $expected, $urlObject->$property
      );
    }

    /**
     * @covers URL::__get
     */
    public function testMagicMethodGetExpectingException() {
      $urlObject = new URL('http://username:password@hostname:8080/path?arg=value#anchor');
      $this->expectException(\BadMethodCallException::class);
      /** @noinspection PhpUndefinedFieldInspection */
      $urlObject->invalidProperty;
    }

    /**
     * @covers       URL
     * @dataProvider provideValidDataForMagicMethodSet
     * @param mixed $expected
     * @param string $property
     */
    public function testMagicMethodSet($expected, $property) {
      $urlObject = new URL('http://username:password@hostname:8080/path?arg=value#anchor');
      $urlObject->$property = $expected;
      $this->assertEquals(
        $expected, $urlObject->$property
      );
    }

    /**
     * @covers       URL
     * @dataProvider provideInvalidDataForMagicMethodSet
     * @param string $property
     * @param mixed $value
     */
    public function testMagicMethodSetWithInvalidValueExpectionException($property, $value) {
      $urlObject = new URL('http://test.tld');
      $this->expectException(\InvalidArgumentException::class);
      $urlObject->$property = $value;
    }

    /**
     * @covers URL::__set
     */
    public function testMagicMethodSetReadonlyExpectingException() {
      $urlObject = new URL('http://username:password@hostname:8080/path?arg=value#anchor');
      $this->expectException(\BadMethodCallException::class);
      $urlObject->user = 'readonly';
    }

    /**
     * @covers URL::__set
     */
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
      return array(
        array('http://username:password@hostname/path?arg=value#anchor'),
        array('http://username:password@hostname/path?arg=value'),
        array('http://username:password@hostname/path'),
        array('http://username:password@hostname')
      );
    }

    public static function getHostUrlDataProvider() {
      return array(
        array(
          '',
          ''
        ),
        array(
          'http://username:password@hostname:8080/path?arg=value#anchor',
          'http://username:password@hostname:8080'
        ),
        array(
          'http://username:password@hostname/path?arg=value#anchor',
          'http://username:password@hostname'
        ),
        array(
          'http://@hostname/path?arg=value#anchor',
          'http://hostname'
        ),
        array(
          'http://127.0.0.1/index.html',
          'http://127.0.0.1',
        )
      );
    }

    public static function setUrlDataProvider() {
      return array(
        array(
          '',
          array()
        ),
        array(
          'http://www.sample.tld',
          array(
            'scheme' => 'http',
            'host' => 'www.sample.tld'
          )
        ),
        array(
          'http://www.sample.tld:8080',
          array(
            'scheme' => 'http',
            'host' => 'www.sample.tld',
            'port' => '8080'
          )
        ),
        array(
          'http://username:password@hostname/path?arg=value#anchor',
          array(
            'scheme' => 'http',
            'host' => 'hostname',
            'user' => 'username',
            'pass' => 'password',
            'path' => '/path',
            'query' => 'arg=value',
            'fragment' => 'anchor'
          )
        )
      );
    }

    public static function setHostDataProvider() {
      return array(
        'Valid: hostname' => array(
          'http://www.domain.tld',
          'www.example.com',
          array(
            'scheme' => 'http',
            'host' => 'www.example.com'
          ),
          'http://www.example.com',
        ),
        'Valid: IP' => array(
          'http://www.example.com',
          '127.0.0.1',
          array(
            'scheme' => 'http',
            'host' => '127.0.0.1'
          ),
          'http://127.0.0.1',
        ),
        array(
          'https://UPPERCASE.tld',
          'UPPERCASE.tld',
          array(
            'scheme' => 'https',
            'host' => 'UPPERCASE.tld'
          ),
          'https://UPPERCASE.tld'
        )
      );
    }

    public static function setHostDataProviderExceptions() {
      return array(
        'no FQH' => array('invalidFQH'),
        'no IP' => array('1.2.4'),
        'numeric tld' => array('hostname.123'),
      );
    }

    public static function provideValidDataForMagicMethodCall() {
      return array(
        array('http', 'getScheme'),
        array('password', 'getPassword'),
        array('password', 'getPass'),
        array('hostname', 'getHost'),
        array('8080', 'getPort'),
        array('/path', 'getPath'),
        array('arg=value', 'getQuery'),
        array('anchor', 'getFragment')
      );
    }

    public static function provideValidDataForMagicMethodGet() {
      return array(
        array('http', 'scheme'),
        array('password', 'password'),
        array('password', 'pass'),
        array('hostname', 'host'),
        array('8080', 'port'),
        array('/path', 'path'),
        array('arg=value', 'query'),
        array('anchor', 'fragment')
      );
    }

    public static function provideValidDataForMagicMethodSet() {
      return array(
        array('https', 'scheme'),
        array('anchor', 'fragment')
      );
    }

    public static function provideInvalidDataForMagicMethodSet() {
      return array(
        array('scheme', 'nöö'),
        array('fragment', '#')
      );
    }
  }

  class Url_TestProxy_ForToString extends URL {

    /**
     * @var \Exception
     */
    public $exception;

    public function getURL() {
      throw $this->exception;
    }
  }
}
