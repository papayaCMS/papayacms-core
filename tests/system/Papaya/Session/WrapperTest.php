<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaSessionWrapperTest extends PapayaTestCase {

  public function setUp() {
    ini_set('session.use_cookies', FALSE);
    session_cache_limiter(FALSE);
  }

  /**
  * @covers PapayaSessionWrapper::registerHandler
  */
  public function testRegisterHandler() {
    $wrapper = new PapayaSessionWrapper();
    $this->assertTrue($wrapper->registerHandler('PapayaSessionHandler_TestClass'));
  }

  /**
  * @covers PapayaSessionWrapper::registerHandler
  */
  public function testRegisterHandlerExpectingException() {
    $wrapper = new PapayaSessionWrapper();
    $this->setExpectedException('InvalidArgumentException');
    $wrapper->registerHandler('INVALID_NON_EXISTING_CLASS');
  }

  /**
  * @covers PapayaSessionWrapper::getId
  */
  public function testGetId() {
    $wrapper = new PapayaSessionWrapper();
    $this->assertEquals('', $wrapper->getId());
  }

  /**
  * @covers PapayaSessionWrapper::getId
  * @covers PapayaSessionWrapper::setId
  */
  public function testSetId() {
    $wrapper = new PapayaSessionWrapper();
    $this->assertEquals('', $wrapper->setId('12345678901234567890ab'));
    $this->assertEquals('12345678901234567890ab', $wrapper->getId());
  }

  /**
  * @covers PapayaSessionWrapper::getName
  * @covers PapayaSessionWrapper::setName
  */
  public function testSetAndGetName() {
    $wrapper = new PapayaSessionWrapper();
    $wrapper->setName('sample');
    $this->assertEquals('sample', $wrapper->getName());
  }

  /**
  * @covers PapayaSessionWrapper::getCookieParams
  * @covers PapayaSessionWrapper::setCookieParams
  */
  public function testGetAndGetCookieParams() {
    ini_set('session.use_cookies', TRUE);
    $params = array(
      'lifetime' => 1800,
      'path' => '/foo/',
      'domain' => 'sample.tld',
      'secure' => TRUE,
      'httponly' => TRUE
    );
    $wrapper = new PapayaSessionWrapper();
    $wrapper->setCookieParams($params);
    $this->assertEquals($params, $wrapper->getCookieParams());
  }

  /**
  * @covers PapayaSessionWrapper::getCacheLimiter
  * @covers PapayaSessionWrapper::setCacheLimiter
  */
  public function testSetAndGetCacheLimiter() {
    ini_set('session.use_cookies', FALSE);
    $wrapper = new PapayaSessionWrapper();
    $wrapper->setCacheLimiter('private');
    $this->assertEquals('private', $wrapper->getCacheLimiter());
  }

  /**
  * @covers PapayaSessionWrapper::start
  */
  public function testStart() {
    PapayaSessionHandler_TestClass::$calls = array();
    $wrapper = new PapayaSessionWrapper();
    $wrapper->registerHandler('PapayaSessionHandler_TestClass');
    $this->assertTrue($wrapper->start());
    $this->assertEquals(
      array(
        'PapayaSessionHandler_TestClass::open' => 1,
        'PapayaSessionHandler_TestClass::read' => 1
      ),
      PapayaSessionHandler_TestClass::$calls
    );
    $wrapper->writeClose();
  }

  /**
  * @covers PapayaSessionWrapper::writeClose
  */
  public function testWriteClose() {
    PapayaSessionHandler_TestClass::$calls = array();
    $wrapper = new PapayaSessionWrapper();
    $wrapper->registerHandler('PapayaSessionHandler_TestClass');
    $wrapper->start();
    $wrapper->writeClose();
    $this->assertEquals(
      array(
        'PapayaSessionHandler_TestClass::open' => 1,
        'PapayaSessionHandler_TestClass::read' => 1,
        'PapayaSessionHandler_TestClass::write' => 1,
        'PapayaSessionHandler_TestClass::close' => 1
      ),
      PapayaSessionHandler_TestClass::$calls
    );
  }

  /**
  * @covers PapayaSessionWrapper::destroy
  */
  public function testDestroy() {
    PapayaSessionHandler_TestClass::$calls = array();
    $wrapper = new PapayaSessionWrapper();
    $wrapper->registerHandler('PapayaSessionHandler_TestClass');
    $wrapper->start();
    $wrapper->destroy();
    $this->assertEquals(
      array(
        'PapayaSessionHandler_TestClass::open' => 1,
        'PapayaSessionHandler_TestClass::read' => 1,
        'PapayaSessionHandler_TestClass::destroy' => 1,
        'PapayaSessionHandler_TestClass::close' => 1
      ),
      PapayaSessionHandler_TestClass::$calls
    );
  }

  /**
  * @covers PapayaSessionWrapper::regenerateId
  * @preserveGlobalState disabled
  * @runInSeparateProcess
  * @large
  */
  public function testRegenerateId() {
    PapayaSessionHandler_TestClass::$calls = array();
    $wrapper = new PapayaSessionWrapper();
    $wrapper->registerHandler('PapayaSessionHandler_TestClass');
    $wrapper->start();
    $id = session_id();
    $wrapper->regenerateId();
    $this->assertEquals(
      array(
        'PapayaSessionHandler_TestClass::open' => 1,
        'PapayaSessionHandler_TestClass::read' => 1,
        'PapayaSessionHandler_TestClass::destroy' => 1
      ),
      PapayaSessionHandler_TestClass::$calls
    );
    $this->assertNotEquals(
      $id, session_id()
    );
  }
}

class PapayaSessionHandler_TestClass implements PapayaSessionHandler {

  public static $calls = array();

  public static function countCall($method) {
    if (isset(self::$calls[$method])) {
      self::$calls[$method]++;
    } else {
      self::$calls[$method] = 1;
    }
  }

  public static function open($savePath, $sessionName) {
    self::countCall(__METHOD__);
    return TRUE;
  }

  public static function close() {
    self::countCall(__METHOD__);
    return TRUE;
  }

  public static function read($id) {
    self::countCall(__METHOD__);
    return '';
  }

  public static function write($id, $data) {
    self::countCall(__METHOD__);
    return TRUE;
  }

  public static function destroy($id) {
    self::countCall(__METHOD__);
    return TRUE;
  }

  public static function gc($maxlifetime) {
    // gc is not called regulary - do not track it
    return TRUE;
  }
}
