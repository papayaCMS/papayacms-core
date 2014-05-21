<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaSessionIdTest extends PapayaTestCase {

  private $_requestParameters = array();

  /**
  * @covers PapayaSessionId::__construct
  */
  public function testConstructor() {
    $sid = new PapayaSessionId('sample');
    $this->assertAttributeEquals(
      'sample', '_name', $sid
    );
  }

  /**
  * @covers PapayaSessionId::getName
  */
  public function testGetName() {
    $sid = new PapayaSessionId('sample');
    $this->assertEquals(
      'sample', $sid->getName()
    );
  }

  /**
  * @covers PapayaSessionId::__toString
  */
  public function testMagicMethodToString() {
    $request = $this->getParameterStubFixture(
      array(
        PapayaRequest::SOURCE_COOKIE => array('sample' => '012345678901234567890123456789ab')
      )
    );
    $sid = new PapayaSessionId('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $this->assertEquals(
      '012345678901234567890123456789ab', (string)$sid
    );
  }

  /**
  * @covers PapayaSessionId::existsIn
  * @dataProvider provideValidParametersForExistsIn
  */
  public function testExistsInExpectingTrue($source, $parameters) {
    $request = $this->getParameterStubFixture($parameters);
    $sid = new PapayaSessionId('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $this->assertTrue($sid->existsIn($source));
  }

  /**
  * @covers PapayaSessionId::existsIn
  * @dataProvider provideInvalidParametersForExistsIn
  */
  public function testExistsInExpectingFalse($source, $parameters) {
    $request = $this->getParameterStubFixture($parameters);
    $sid = new PapayaSessionId('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $this->assertFalse($sid->existsIn($source));
  }

  /**
  * @covers PapayaSessionId::existsIn
  */
  public function testExistsInWithInvalidSourceExpectingFalse() {
    $request = $this->getParameterStubFixture(array());
    $sid = new PapayaSessionId('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $this->assertFalse($sid->existsIn(-1));
  }

  /**
  * @covers PapayaSessionId::validate
  * @dataProvider provideValidSessionIds
  */
  public function testValidateExpectingSessionId($sessionId) {
    $sid = new PapayaSessionId('sample');
    $this->assertEquals($sessionId, $sid->validate($sessionId));
  }

  /**
  * @covers PapayaSessionId::validate
  * @dataProvider provideInvalidSessionIds
  */
  public function testValidateExpectingNull($sessionId) {
    $sid = new PapayaSessionId('sample');
    $this->assertNull($sid->validate($sessionId));
  }

  /**
  * @covers PapayaSessionId::getId
  * @covers PapayaSessionId::_readCookie
  * @covers PapayaSessionId::_isCookieUnique
  * @backupGlobals enabled
  * @dataProvider provideCookieStrings
  */
  public function testGetIdFromCookie($cookieString) {
    $request = $this->getParameterStubFixture(
      array(
        PapayaRequest::SOURCE_COOKIE => array('sample' => '012345678901234567890123456789ab')
      )
    );
    $_SERVER['HTTP_COOKIE'] = $cookieString;
    $sid = new PapayaSessionId('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $this->assertEquals(
      '012345678901234567890123456789ab', $sid->getId()
    );
  }

  /**
  * @covers PapayaSessionId::_readCookie
  * @covers PapayaSessionId::_isCookieUnique
  * @backupGlobals enabled
  * @dataProvider provideAmbiguousCookieStrings
  */
  public function testGetIdWithAmbiguousCookie($cookieString) {
    $request = $this->getParameterStubFixture(
      array(
        PapayaRequest::SOURCE_COOKIE => array('sample' => '012345678901234567890123456789ab')
      )
    );
    $_SERVER['HTTP_COOKIE'] = $cookieString;
    $sid = new PapayaSessionId('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $this->assertEquals(
      '', $sid->getId()
    );
  }

  /**
  * @covers PapayaSessionId::getId
  * @covers PapayaSessionId::_readCookie
  * @covers PapayaSessionId::_readPath
  */
  public function testGetIdFromPath() {
    $request = $this->getParameterStubFixture(
      array(
        PapayaRequest::SOURCE_PATH => array('session' => 'sample012345678901234567890123456789ab')
      )
    );
    $sid = new PapayaSessionId('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $this->assertEquals(
      '012345678901234567890123456789ab', $sid->getId()
    );
  }

  /**
  * @covers PapayaSessionId::getId
  * @covers PapayaSessionId::_readCookie
  * @covers PapayaSessionId::_readPath
  */
  public function testGetIdFromPathFallbackToDefaultName() {
    $request = $this->getParameterStubFixture(
      array(
        PapayaRequest::SOURCE_PATH => array('session' => 'sid012345678901234567890123456789ab')
      )
    );
    $sid = new PapayaSessionId('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $this->assertEquals(
      '012345678901234567890123456789ab', $sid->getId()
    );
  }

  /**
  * @covers PapayaSessionId::getId
  * @covers PapayaSessionId::_readPath
  * @covers PapayaSessionId::_readQuery
  */
  public function testGetIdFromQueryString() {
    $request = $this->getParameterStubFixture(
      array(
        PapayaRequest::SOURCE_QUERY => array('sample' => '012345678901234567890123456789ab')
      )
    );
    $sid = new PapayaSessionId('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $this->assertEquals(
      '012345678901234567890123456789ab', $sid->getId()
    );
  }

  /**
  * @covers PapayaSessionId::getId
  * @covers PapayaSessionId::_readBody
  */
  public function testGetIdFromRequestBody() {
    $request = $this->getParameterStubFixture(
      array(
        PapayaRequest::SOURCE_BODY => array('sample' => '012345678901234567890123456789ab')
      )
    );
    $sid = new PapayaSessionId('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $this->assertEquals(
      '012345678901234567890123456789ab', $sid->getId()
    );
  }

  /**
  * @covers PapayaSessionId::getId
  */
  public function testGetIdFromCachedValue() {
    $request = $this->getMock('PapayaRequest', array('getParameter'));
    $request
      ->expects($this->once())
      ->method('getParameter')
      ->withAnyParameters()
      ->will($this->returnValue('012345678901234567890123456789ab'));
    $sid = new PapayaSessionId('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $sid->getId();
    $this->assertEquals(
      '012345678901234567890123456789ab', $sid->getId()
    );
  }

  /**
  * @covers PapayaSessionId::getId
  */
  public function testGetIdExpectingEmptyString() {
    $request = $this->getParameterStubFixture(array());
    $sid = new PapayaSessionId('sample');
    $sid->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    $this->assertEquals(
      '', $sid->getId()
    );
  }

  /***********************
  * Fixtures
  ***********************/

  public function getParameterStubFixture($parameters) {
    $this->requestParameters = $parameters;
    $request = $this->getMock('PapayaRequest', array('getParameter'));
    $request
      ->expects($this->any())
      ->method('getParameter')
      ->withAnyParameters()
      ->will($this->returnCallback(array($this, 'getParameterCallback')));
    return $request;
  }

  public function getParameterCallback($name, $default, $filter, $source) {
    if (isset($this->requestParameters[$source][$name])) {
      return $this->requestParameters[$source][$name];
    } else {
      return $default;
    }
  }

  /***********************
  * Data Provider
  ***********************/

  public static function provideCookieStrings() {
    return array(
      'empty' => array(''),
      'same prefix' => array(
        'sample=25b482735512613d6b61983c400bd3d9; sampleadmin=e3ad802bfca87740d29ddc43c4397c44'
      ),
      'same suffix' => array(
        'sample=25b482735512613d6b61983c400bd3d9; adminsample=e3ad802bfca87740d29ddc43c4397c44'
      ),
      'other' => array(
        'foo=e3ad802bfca87740d29ddc43c4397c44'
      )
    );
  }

  public static function provideAmbiguousCookieStrings() {
    return array(
      'duplicate' => array(
        'sample=25b482735512613d6b61983c400bd3d9; sample=e3ad802bfca87740d29ddc43c4397c44'
      )
    );
  }

  public static function provideValidParametersForExistsIn() {
    return array(
      'any from cookie' => array(
        PapayaSessionId::SOURCE_ANY,
        array(
          PapayaRequest::SOURCE_COOKIE => array('sample' => '25b482735512613d6b61983c400bd3d9')
        )
      ),
      'any from path' => array(
        PapayaSessionId::SOURCE_ANY,
        array(
          PapayaRequest::SOURCE_PATH => array('session' => 'sample25b482735512613d6b61983c400bd3d9')
        )
      ),
      'cookie' => array(
        PapayaSessionId::SOURCE_COOKIE,
        array(
          PapayaRequest::SOURCE_COOKIE => array('sample' => '25b482735512613d6b61983c400bd3d9')
        )
      ),
      'path' => array(
        PapayaSessionId::SOURCE_PATH,
        array(
          PapayaRequest::SOURCE_PATH => array('session' => 'sample25b482735512613d6b61983c400bd3d9')
        )
      ),
      'query' => array(
        PapayaSessionId::SOURCE_QUERY,
        array(
          PapayaRequest::SOURCE_QUERY => array('sample' => '25b482735512613d6b61983c400bd3d9')
        )
      ),
      'request body' => array(
        PapayaSessionId::SOURCE_BODY,
        array(
          PapayaRequest::SOURCE_BODY => array('sample' => '25b482735512613d6b61983c400bd3d9')
        )
      ),
    );
  }

  public static function provideInvalidParametersForExistsIn() {
    return array(
      'any on empty' => array(
        PapayaSessionId::SOURCE_ANY,
        array()
      )
    );
  }

  public static function provideValidSessionIds() {
    return array(
      'md5 - 4' => array('25b482735512613d6b61983c400bd3d9')
    );
  }

  public static function provideInvalidSessionIds() {
    return array(
      'empty' => array(''),
      array('foo')
    );
  }
}