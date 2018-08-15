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

require_once __DIR__.'/../../../bootstrap.php';

/**
 * @runTestsInSeparateProcesses
 */
class PapayaSessionWrapperTest extends \PapayaTestCase {

  public function setUp() {
    ini_set('session.use_cookies', FALSE);
    session_cache_limiter(FALSE);
  }

  /**
  * @covers \Papaya\Session\Wrapper::registerHandler
  */
  public function testRegisterHandler() {
    $wrapper = new \Papaya\Session\Wrapper();
    $this->assertTrue($wrapper->registerHandler(\PapayaSessionHandler_TestClass::class));
  }

  /**
  * @covers \Papaya\Session\Wrapper::registerHandler
  */
  public function testRegisterHandlerExpectingException() {
    $wrapper = new \Papaya\Session\Wrapper();
    $this->expectException(\InvalidArgumentException::class);
    $wrapper->registerHandler('INVALID_NON_EXISTING_CLASS');
  }

  /**
  * @covers \Papaya\Session\Wrapper::getId
  */
  public function testGetId() {
    $wrapper = new \Papaya\Session\Wrapper();
    $this->assertEquals('', $wrapper->getId());
  }

  /**
  * @covers \Papaya\Session\Wrapper::getId
  * @covers \Papaya\Session\Wrapper::setId
  */
  public function testSetId() {
    $wrapper = new \Papaya\Session\Wrapper();
    $this->assertEquals('', $wrapper->setId('12345678901234567890ab'));
    $this->assertEquals('12345678901234567890ab', $wrapper->getId());
  }

  /**
  * @covers \Papaya\Session\Wrapper::getName
  * @covers \Papaya\Session\Wrapper::setName
  */
  public function testSetAndGetName() {
    $wrapper = new \Papaya\Session\Wrapper();
    $wrapper->setName('sample');
    $this->assertEquals('sample', $wrapper->getName());
  }

  /**
  * @covers \Papaya\Session\Wrapper::getCookieParams
  * @covers \Papaya\Session\Wrapper::setCookieParams
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
    $wrapper = new \Papaya\Session\Wrapper();
    $wrapper->setCookieParams($params);
    $this->assertEquals($params, $wrapper->getCookieParams());
  }

  /**
  * @covers \Papaya\Session\Wrapper::getCacheLimiter
  * @covers \Papaya\Session\Wrapper::setCacheLimiter
  */
  public function testSetAndGetCacheLimiter() {
    ini_set('session.use_cookies', FALSE);
    $wrapper = new \Papaya\Session\Wrapper();
    $wrapper->setCacheLimiter('private');
    $this->assertEquals('private', $wrapper->getCacheLimiter());
  }

  /**
  * @covers \Papaya\Session\Wrapper::start
  */
  public function testStart() {
    \PapayaSessionHandler_TestClass::$calls = array();
    $wrapper = new \Papaya\Session\Wrapper();
    $wrapper->registerHandler(\PapayaSessionHandler_TestClass::class);
    $this->assertTrue($wrapper->start());
    $this->assertEquals(
      array(
        'PapayaSessionHandler_TestClass::open' => 1,
        'PapayaSessionHandler_TestClass::read' => 1
      ),
      \PapayaSessionHandler_TestClass::$calls
    );
    $wrapper->writeClose();
  }

  /**
  * @covers \Papaya\Session\Wrapper::writeClose
  */
  public function testWriteClose() {
    \PapayaSessionHandler_TestClass::$calls = array();
    $wrapper = new \Papaya\Session\Wrapper();
    $wrapper->registerHandler(\PapayaSessionHandler_TestClass::class);
    $wrapper->start();
    $wrapper->writeClose();
    $this->assertEquals(
      array(
        'PapayaSessionHandler_TestClass::open' => 1,
        'PapayaSessionHandler_TestClass::read' => 1,
        'PapayaSessionHandler_TestClass::write' => 1,
        'PapayaSessionHandler_TestClass::close' => 1
      ),
      \PapayaSessionHandler_TestClass::$calls
    );
  }

  /**
  * @covers \Papaya\Session\Wrapper::destroy
  */
  public function testDestroy() {
    \PapayaSessionHandler_TestClass::$calls = array();
    $wrapper = new \Papaya\Session\Wrapper();
    $wrapper->registerHandler(\PapayaSessionHandler_TestClass::class);
    $wrapper->start();
    $wrapper->destroy();
    $this->assertEquals(
      array(
        'PapayaSessionHandler_TestClass::open' => 1,
        'PapayaSessionHandler_TestClass::read' => 1,
        'PapayaSessionHandler_TestClass::destroy' => 1,
        'PapayaSessionHandler_TestClass::close' => 1
      ),
      \PapayaSessionHandler_TestClass::$calls
    );
  }

  /**
  * @covers \Papaya\Session\Wrapper::regenerateId
  * @preserveGlobalState disabled
  * @runInSeparateProcess
  * @large
  */
  public function testRegenerateId() {
    \PapayaSessionHandler_TestClass::$calls = array();
    $wrapper = new \Papaya\Session\Wrapper();
    $wrapper->registerHandler(\PapayaSessionHandler_TestClass::class);
    $wrapper->start();
    $id = session_id();
    $wrapper->regenerateId();
    $this->assertThat(
      \PapayaSessionHandler_TestClass::$calls,
      $this->logicalOr(
        array(
          'PapayaSessionHandler_TestClass::open' => 1,
          'PapayaSessionHandler_TestClass::read' => 1,
          'PapayaSessionHandler_TestClass::destroy' => 1
        ),
        array(
          'PapayaSessionHandler_TestClass::open' => 2,
          'PapayaSessionHandler_TestClass::read' => 2,
          'PapayaSessionHandler_TestClass::destroy' => 1,
          'PapayaSessionHandler_TestClass::close' => 1
        )
      )
    );
    $this->assertNotEquals(
      $id, session_id()
    );
  }
}

class PapayaSessionHandler_TestClass implements \Papaya\Session\Handler {

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
