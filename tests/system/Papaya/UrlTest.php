<?php
require_once(dirname(__FILE__).'/../../bootstrap.php');

class PapayaUrlTest extends PapayaTestCase {

  /**
  * @covers PapayaUrl::__construct
  */
  public function testConstructor() {
    $urlObject = new PapayaUrl();
    $this->assertNull($this->readAttribute($urlObject, '_elements'));
  }

  /**
  * @covers PapayaUrl::__construct
  */
  public function testConstructorWithUrl() {
    $urlObject = new PapayaUrl(
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
  * @covers PapayaUrl::setUrl
  * @dataProvider setUrlDataProvider
  */
  public function testSetUrl($url, $expected) {
    $urlObject = new PapayaUrl();
    $urlObject->setUrl($url);
    $this->assertAttributeEquals(
      $expected, '_elements', $urlObject
    );
  }

  /**
  * @covers PapayaUrl::__toString
  */
  public function testToString() {
    $urlObject = new PapayaUrl();
    $urlObject->setUrl('http://www.domain.tld');
    $this->assertEquals(
      'http://www.domain.tld', (string)$urlObject
    );
  }

  /**
  * @covers PapayaUrl::__toString
  */
  public function testToStringCapturesInvalidArgumentException() {
    $urlObject = new PapayaUrl_TestProxy_ForToString();
    $urlObject->exception = new InvalidArgumentException();
    $this->assertEquals('', (string)$urlObject);
  }

  /**
  * @covers PapayaUrl::__toString
  */
  public function testToStringCapturesBadMethodCallException() {
    $urlObject = new PapayaUrl_TestProxy_ForToString();
    $urlObject->exception = new BadMethodCallException();
    $this->assertEquals('', (string)$urlObject);
  }


  /**
  * @covers PapayaUrl::getUrl
  * @dataProvider getUrlDataProvider
  */
  public function testGetUrl($url) {
    $urlObject = new PapayaUrl();
    $urlObject->setUrl($url);
    $this->assertEquals(
      $url,
      $urlObject->getUrl()
    );
  }

  /**
  * @covers PapayaUrl::getPathUrl
  * @dataProvider getUrlDataProvider
  */
  public function testGetPathUrl() {
    $urlObject = new PapayaUrl();
    $urlObject->setUrl('https://username:password@www.domain.tld:8080/path?arg=value#anchor');
    $this->assertEquals(
      'https://username:password@www.domain.tld:8080/path',
      $urlObject->getPathUrl()
    );
  }

  /**
  * @covers PapayaUrl::getHostUrl
  * @dataProvider getHostUrlDataProvider
  */
  public function testGetHostUrl($url, $hostUrl) {
    $urlObject = new PapayaUrl();
    $urlObject->setUrl($url);
    $this->assertEquals(
      $hostUrl,
      $urlObject->getHostUrl()
    );
  }

  /**
  * @covers PapayaUrl::setScheme
  */
  public function testSetScheme() {
    $urlObject = new PapayaUrl();
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
  * @covers PapayaUrl::setScheme
  */
  public function testSetSchemeExpectingException() {
    $urlObject = new PapayaUrl();
    $urlObject->setUrl('http://www.domain.tld');
    $this->setExpectedException('InvalidArgumentException');
    $urlObject->setScheme('http://');
  }

  /**
  * @covers PapayaUrl::setHost
  * @dataProvider setHostDataProvider
  */
  public function testSetHost($url, $host, $elements, $expected) {
    $urlObject = new PapayaUrl();
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
  * @covers PapayaUrl::setHost
  * @expectedException InvalidArgumentException
  * @dataProvider setHostDataProviderExceptions
  */
  public function testSetHostExpectingException($host) {
    $urlObject = new PapayaUrl();
    $urlObject->setUrl('http://www.domain.tld');
    $urlObject->setHost($host);
  }

  /**
  * @covers PapayaUrl::setPort
  */
  public function testSetPort() {
    $urlObject = new PapayaUrl();
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
  * @covers PapayaUrl::setPort
  * @expectedException InvalidArgumentException
  */
  public function testSetPortExpectingException() {
    $urlObject = new PapayaUrl();
    $urlObject->setUrl('http://www.domain.tld');
    $urlObject->setPort('not-a-number-123');
  }

  /**
  * @covers PapayaUrl::setPath
  */
  public function testSetPath() {
    $urlObject = new PapayaUrl();
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
  * @covers PapayaUrl::setPath
  */
  public function testSetPathExpectingException() {
    $urlObject = new PapayaUrl();
    $urlObject->setUrl('http://www.domain.tld');
    $this->setExpectedException('InvalidArgumentException');
    $urlObject->setPath('bar');
  }

  /**
  * @covers PapayaUrl::setQuery
  */
  public function testSetQuery() {
    $urlObject = new PapayaUrl();
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
  * @covers PapayaUrl::setQuery
  */
  public function testSetQueryExpectingException() {
    $urlObject = new PapayaUrl();
    $urlObject->setUrl('http://www.domain.tld');
    $this->setExpectedException('InvalidArgumentException');
    $urlObject->setQuery('?bar');
  }

  /**
  * @covers PapayaUrl::__call
  * @dataProvider provideValidDataForMagicMethodCall
  */
  public function testMagicMethodCall($expected, $method) {
    $urlObject = new PapayaUrl('http://username:password@hostname:8080/path?arg=value#anchor');
    $this->assertEquals(
      $expected, $urlObject->$method()
    );
  }

  /**
  * @covers PapayaUrl::__call
  */
  public function testMagicMethodCallExpectingException() {
    $urlObject = new PapayaUrl('http://username:password@hostname:8080/path?arg=value#anchor');
    $this->setExpectedException('BadMethodCallException');
    /** @noinspection PhpUndefinedMethodInspection */
    $urlObject->invalidMethod();
  }

  /**
  * @covers PapayaUrl::__get
  * @dataProvider provideValidDataForMagicMethodGet
  */
  public function testMagicMethodGet($expected, $property) {
    $urlObject = new PapayaUrl('http://username:password@hostname:8080/path?arg=value#anchor');
    $this->assertEquals(
      $expected, $urlObject->$property
    );
  }

  /**
  * @covers PapayaUrl::__get
  */
  public function testMagicMethodGetExpectingException() {
    $urlObject = new PapayaUrl('http://username:password@hostname:8080/path?arg=value#anchor');
    $this->setExpectedException('BadMethodCallException');
    /** @noinspection PhpUndefinedFieldInspection */
    $urlObject->invalidProperty;
  }

  /**
  * @covers PapayaUrl
  * @dataProvider provideValidDataForMagicMethodSet
  */
  public function testMagicMethodSet($expected, $property) {
    $urlObject = new PapayaUrl('http://username:password@hostname:8080/path?arg=value#anchor');
    $urlObject->$property = $expected;
    $this->assertEquals(
      $expected, $urlObject->$property
    );
  }

  /**
  * @covers PapayaUrl
  * @dataProvider provideInvalidDataForMagicMethodSet
  */
  public function testMagicMethodSetWithInvalidValueExpectionException($property, $value) {
    $urlObject = new PapayaUrl('http://test.tld');
    $this->setExpectedException('InvalidArgumentException');
    $urlObject->$property = $value;
  }

  /**
  * @covers PapayaUrl::__set
  */
  public function testMagicMethodSetReadonlyExpectingException() {
    $urlObject = new PapayaUrl('http://username:password@hostname:8080/path?arg=value#anchor');
    $this->setExpectedException('BadMethodCallException');
    $urlObject->user = 'readonly';
  }

  /**
  * @covers PapayaUrl::__set
  */
  public function testMagicMethodSetInvalidExpectingException() {
    $urlObject = new PapayaUrl('http://username:password@hostname:8080/path?arg=value#anchor');
    $this->setExpectedException('BadMethodCallException');
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

class PapayaUrl_TestProxy_ForToString extends PapayaUrl {

  /**
   * @var Exception
   */
  public $exception = NULL;

  public function getUrl() {
    throw $this->exception;
  }
}