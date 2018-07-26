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

require_once __DIR__.'/../../bootstrap.php';

class PapayaUrlTest extends \PapayaTestCase {

  /**
  * @covers Url::__construct
  */
  public function testConstructor() {
    $urlObject = new Url();
    $this->assertNull($this->readAttribute($urlObject, '_elements'));
  }

  /**
  * @covers Url::__construct
  */
  public function testConstructorWithUrl() {
    $urlObject = new Url(
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
   * @covers Url::setUrl
   * @dataProvider setUrlDataProvider
   * @param string $url
   * @param array $expected
   */
  public function testSetUrl($url, array $expected) {
    $urlObject = new Url();
    $urlObject->setUrl($url);
    $this->assertAttributeEquals(
      $expected, '_elements', $urlObject
    );
  }

  /**
  * @covers Url::__toString
  */
  public function testToString() {
    $urlObject = new Url();
    $urlObject->setUrl('http://www.domain.tld');
    $this->assertEquals(
      'http://www.domain.tld', (string)$urlObject
    );
  }

  /**
  * @covers Url::__toString
  */
  public function testToStringCapturesInvalidArgumentException() {
    $urlObject = new \PapayaUrl_TestProxy_ForToString();
    $urlObject->exception = new InvalidArgumentException();
    $this->assertEquals('', (string)$urlObject);
  }

  /**
  * @covers Url::__toString
  */
  public function testToStringCapturesBadMethodCallException() {
    $urlObject = new \PapayaUrl_TestProxy_ForToString();
    $urlObject->exception = new BadMethodCallException();
    $this->assertEquals('', (string)$urlObject);
  }


  /**
   * @covers Url::getUrl
   * @dataProvider getUrlDataProvider
   * @param string $url
   */
  public function testGetUrl($url) {
    $urlObject = new Url();
    $urlObject->setUrl($url);
    $this->assertEquals(
      $url,
      $urlObject->getUrl()
    );
  }

  /**
  * @covers Url::getPathUrl
  * @dataProvider getUrlDataProvider
  */
  public function testGetPathUrl() {
    $urlObject = new Url();
    $urlObject->setUrl('https://username:password@www.domain.tld:8080/path?arg=value#anchor');
    $this->assertEquals(
      'https://username:password@www.domain.tld:8080/path',
      $urlObject->getPathUrl()
    );
  }

  /**
   * @covers Url::getHostUrl
   * @dataProvider getHostUrlDataProvider
   * @param string $url
   * @param string $hostUrl
   */
  public function testGetHostUrl($url, $hostUrl) {
    $urlObject = new Url();
    $urlObject->setUrl($url);
    $this->assertEquals(
      $hostUrl,
      $urlObject->getHostUrl()
    );
  }

  /**
  * @covers Url::setScheme
  */
  public function testSetScheme() {
    $urlObject = new Url();
    $urlObject->setUrl('http://www.domain.tld');
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
      'ftp://www.domain.tld', $urlObject->getUrl()
    );
  }

  /**
  * @covers Url::setScheme
  */
  public function testSetSchemeExpectingException() {
    $urlObject = new Url();
    $urlObject->setUrl('http://www.domain.tld');
    $this->expectException(InvalidArgumentException::class);
    $urlObject->setScheme('http://');
  }

  /**
   * @covers Url::setHost
   * @dataProvider setHostDataProvider
   * @param string $url
   * @param string $host
   * @param array $elements
   * @param string $expected
   */
  public function testSetHost($url, $host, $elements, $expected) {
    $urlObject = new Url();
    $urlObject->setUrl($url);
    $urlObject->setHost($host);
    $this->assertAttributeEquals(
      $elements,
      '_elements',
      $urlObject
    );
    $this->assertEquals($expected, $urlObject->getUrl());
  }

  /**
   * @covers Url::setHost
   * @expectedException InvalidArgumentException
   * @dataProvider setHostDataProviderExceptions
   * @param string $host
   */
  public function testSetHostExpectingException($host) {
    $urlObject = new Url();
    $urlObject->setUrl('http://www.domain.tld');
    $urlObject->setHost($host);
  }

  /**
  * @covers Url::setPort
  */
  public function testSetPort() {
    $urlObject = new Url();
    $urlObject->setUrl('http://www.domain.tld:80');
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
      'http://www.domain.tld:8080', $urlObject->getUrl()
    );
  }

  /**
  * @covers Url::setPort
  * @expectedException InvalidArgumentException
  */
  public function testSetPortExpectingException() {
    $urlObject = new Url();
    $urlObject->setUrl('http://www.domain.tld');
    $urlObject->setPort('not-a-number-123');
  }

  /**
  * @covers Url::setPath
  */
  public function testSetPath() {
    $urlObject = new Url();
    $urlObject->setUrl('http://www.domain.tld/foo');
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
      'http://www.domain.tld/bar', $urlObject->getUrl()
    );
  }

  /**
  * @covers Url::setPath
  */
  public function testSetPathExpectingException() {
    $urlObject = new Url();
    $urlObject->setUrl('http://www.domain.tld');
    $this->expectException(InvalidArgumentException::class);
    $urlObject->setPath('bar');
  }

  /**
  * @covers Url::setQuery
  */
  public function testSetQuery() {
    $urlObject = new Url();
    $urlObject->setUrl('http://www.domain.tld');
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
      'http://www.domain.tld?foo=bar', $urlObject->getUrl()
    );
  }

  /**
  * @covers Url::setQuery
  */
  public function testSetQueryExpectingException() {
    $urlObject = new Url();
    $urlObject->setUrl('http://www.domain.tld');
    $this->expectException(InvalidArgumentException::class);
    $urlObject->setQuery('?bar');
  }

  /**
   * @covers Url::__call
   * @dataProvider provideValidDataForMagicMethodCall
   * @param mixed $expected
   * @param string $method
   */
  public function testMagicMethodCall($expected, $method) {
    $urlObject = new Url('http://username:password@hostname:8080/path?arg=value#anchor');
    $this->assertEquals(
      $expected, $urlObject->$method()
    );
  }

  /**
  * @covers Url::__call
  */
  public function testMagicMethodCallExpectingException() {
    $urlObject = new Url('http://username:password@hostname:8080/path?arg=value#anchor');
    $this->expectException(BadMethodCallException::class);
    /** @noinspection PhpUndefinedMethodInspection */
    $urlObject->invalidMethod();
  }

  /**
   * @covers Url::__get
   * @dataProvider provideValidDataForMagicMethodGet
   * @param mixed $expected
   * @param string $property
   */
  public function testMagicMethodGet($expected, $property) {
    $urlObject = new Url('http://username:password@hostname:8080/path?arg=value#anchor');
    $this->assertEquals(
      $expected, $urlObject->$property
    );
  }

  /**
  * @covers Url::__get
  */
  public function testMagicMethodGetExpectingException() {
    $urlObject = new Url('http://username:password@hostname:8080/path?arg=value#anchor');
    $this->expectException(BadMethodCallException::class);
    /** @noinspection PhpUndefinedFieldInspection */
    $urlObject->invalidProperty;
  }

  /**
   * @covers       Url
   * @dataProvider provideValidDataForMagicMethodSet
   * @param mixed $expected
   * @param string $property
   */
  public function testMagicMethodSet($expected, $property) {
    $urlObject = new Url('http://username:password@hostname:8080/path?arg=value#anchor');
    $urlObject->$property = $expected;
    $this->assertEquals(
      $expected, $urlObject->$property
    );
  }

  /**
   * @covers       Url
   * @dataProvider provideInvalidDataForMagicMethodSet
   * @param string $property
   * @param mixed $value
   */
  public function testMagicMethodSetWithInvalidValueExpectionException($property, $value) {
    $urlObject = new Url('http://test.tld');
    $this->expectException(InvalidArgumentException::class);
    $urlObject->$property = $value;
  }

  /**
  * @covers Url::__set
  */
  public function testMagicMethodSetReadonlyExpectingException() {
    $urlObject = new Url('http://username:password@hostname:8080/path?arg=value#anchor');
    $this->expectException(BadMethodCallException::class);
    $urlObject->user = 'readonly';
  }

  /**
  * @covers Url::__set
  */
  public function testMagicMethodSetInvalidExpectingException() {
    $urlObject = new Url('http://username:password@hostname:8080/path?arg=value#anchor');
    $this->expectException(BadMethodCallException::class);
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

class PapayaUrl_TestProxy_ForToString extends Url {

  /**
   * @var Exception
   */
  public $exception;

  public function getUrl() {
    throw $this->exception;
  }
}
