<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaUtilServerProtocolTest extends PapayaTestCase {

  /**
  * @covers PapayaUtilServerProtocol::isSecure
  * @backupGlobals enabled
  */
  public function testIsSecureExpectingTrue() {
    $_SERVER['HTTPS'] = 'On';
    $this->assertTrue(
      PapayaUtilServerProtocol::isSecure()
    );
  }

  /**
  * @covers PapayaUtilServerProtocol::isSecure
  * @backupGlobals enabled
  */
  public function testIsSecureExpectingFalse() {
    $_SERVER['HTTPS'] = NULL;
    $this->assertFalse(
      PapayaUtilServerProtocol::isSecure()
    );
  }

  /**
  * @covers PapayaUtilServerProtocol::isSecure
  * @preserveGlobalState disabled
  * @runInSeparateProcess
  */
  public function testIsSecureWithPrefixedCustomHeaderExpectingTrue() {
    $_SERVER['HTTPS'] = NULL;
    $_SERVER['HTTP_X_PAPAYA_HTTPS'] = '123456789012345678901234567890ab';
    define('PAPAYA_HEADER_HTTPS_TOKEN', '123456789012345678901234567890ab');
    $this->assertTrue(
      PapayaUtilServerProtocol::isSecure()
    );
  }

  /**
  * @covers PapayaUtilServerProtocol::isSecure
  * @preserveGlobalState disabled
  * @runInSeparateProcess
  */
  public function testIsSecureWithCustomHeaderExpectingTrue() {
    $_SERVER['HTTPS'] = NULL;
    $_SERVER['X_PAPAYA_HTTPS'] = '123456789012345678901234567890ab';
    define('PAPAYA_HEADER_HTTPS_TOKEN', '123456789012345678901234567890ab');
    $this->assertTrue(
      PapayaUtilServerProtocol::isSecure()
    );
  }

  /**
  * @covers PapayaUtilServerProtocol::isSecure
  * @preserveGlobalState disabled
  * @runInSeparateProcess
  */
  public function testIsSecureWithEmptyCustomHeaderExpectingFalse() {
    $_SERVER['HTTPS'] = NULL;
    $_SERVER['HTTP_X_PAPAYA_HTTPS'] = '';
    define('PAPAYA_HEADER_HTTPS_TOKEN', '');
    $this->assertFalse(
      PapayaUtilServerProtocol::isSecure()
    );
  }

  /**
  * @covers PapayaUtilServerProtocol::isSecure
  * @preserveGlobalState disabled
  * @runInSeparateProcess
  */
  public function testIsSecureWithInvalidCustomHeaderExpectingFalse() {
    $_SERVER['HTTPS'] = NULL;
    $_SERVER['HTTP_X_PAPAYA_HTTPS'] = '123456789012345678901234567890ab';
    define('PAPAYA_HEADER_HTTPS_TOKEN', 'ef123456789012345678901234567890');
    $this->assertFalse(
      PapayaUtilServerProtocol::isSecure()
    );
  }

  /**
  * @covers PapayaUtilServerProtocol::isSecure
  * @backupGlobals enabled
  */
  public function testIsSecureWithCustomHeaderWithoutConstantExpectingFalse() {
    $_SERVER['HTTPS'] = NULL;
    $_SERVER['HTTP_X_PAPAYA_HTTPS'] = '123456789012345678901234567890ab';
    $this->assertFalse(
      PapayaUtilServerProtocol::isSecure()
    );
  }

  /**
  * @covers PapayaUtilServerProtocol::get
  * @backupGlobals enabled
  */
  public function testGetExpectingHttps() {
    $_SERVER['HTTPS'] = 'On';
    $this->assertEquals(
      'https', PapayaUtilServerProtocol::get()
    );
  }

  /**
  * @covers PapayaUtilServerProtocol::get
  * @backupGlobals enabled
  */
  public function testGetExpectingHttp() {
    $_SERVER['HTTPS'] = NULL;
    $this->assertEquals(
      'http', PapayaUtilServerProtocol::get()
    );
  }

  /**
  * @covers PapayaUtilServerProtocol::get
  */
  public function testGetWithParameterExpectingHttp() {
    $this->assertEquals(
      'http', PapayaUtilServerProtocol::get(PapayaUtilServerProtocol::HTTP)
    );
  }

  /**
  * @covers PapayaUtilServerProtocol::get
  */
  public function testGetWithParameterExpectingHttps() {
    $this->assertEquals(
      'https', PapayaUtilServerProtocol::get(PapayaUtilServerProtocol::HTTPS)
    );
  }

  /**
  * @covers PapayaUtilServerProtocol::getDefaultPort
  * @backupGlobals enabled
  */
  public function testGetDefaultPortExpectingHttps() {
    $_SERVER['HTTPS'] = 'On';
    $this->assertEquals(
      443, PapayaUtilServerProtocol::getDefaultPort()
    );
  }

  /**
  * @covers PapayaUtilServerProtocol::getDefaultPort
  * @backupGlobals enabled
  */
  public function testGetDefaultPortExpectingHttp() {
    $_SERVER['HTTPS'] = NULL;
    $this->assertEquals(
      80, PapayaUtilServerProtocol::getDefaultPort()
    );
  }

  /**
  * @covers PapayaUtilServerProtocol::isSecure
  * @preserveGlobalState disabled
  * @runInSeparateProcess
  */
  public function testGetDefaultPortWithValidCutomHeaderExpectingHttp() {
    $_SERVER['HTTPS'] = NULL;
    $_SERVER['HTTP_X_PAPAYA_HTTPS'] = '123456789012345678901234567890ab';
    define('PAPAYA_HEADER_HTTPS_TOKEN', '123456789012345678901234567890ab');
    $this->assertEquals(
      80, PapayaUtilServerProtocol::getDefaultPort()
    );
  }
}