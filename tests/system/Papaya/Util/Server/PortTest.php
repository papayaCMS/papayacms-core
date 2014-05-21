<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaUtilServerPortTest extends PapayaTestCase {

  public function setUp() {
    $this->_server = $_SERVER;
  }

  public function tearDown() {
    $_SERVER = $this->_server;
  }

  /**
  * @covers PapayaUtilServerPort::get
  */
  public function testGetOnEmptyRequestEnvironmentExpecting80() {
    $_SERVER = array();
    $this->assertEquals(
      80, PapayaUtilServerPort::get()
    );
  }

  /**
  * @covers PapayaUtilServerPort::get
  */
  public function testGet() {
    $_SERVER = array(
      'SERVER_PORT' => 80
    );
    $this->assertEquals(
      80, PapayaUtilServerPort::get()
    );
  }

  /**
  * @covers PapayaUtilServerPort::get
  */
  public function testGetWithNonDefaultPort() {
    $_SERVER = array(
      'SERVER_PORT' => 8080
    );
    $this->assertEquals(
      8080, PapayaUtilServerPort::get()
    );
  }

  /**
  * @covers PapayaUtilServerPort::get
  */
  public function testGetWithHttps() {
    $_SERVER = array(
      'HTTPS' => 'on',
      'SERVER_PORT' => 443
    );
    $this->assertEquals(
      443, PapayaUtilServerPort::get()
    );
  }

  /**
  * @covers PapayaUtilServerPort::get
  */
  public function testGetWithHttpsNonDefaultPort() {
    $_SERVER = array(
      'HTTPS' => 'on',
      'SERVER_PORT' => 886
    );
    $this->assertEquals(
      886, PapayaUtilServerPort::get()
    );
  }

  /**
  * @covers PapayaUtilServerPort::get
  * @preserveGlobalState disabled
  * @runInSeparateProcess
  */
  public function testGetWithHttpsAtProxy() {
    define('PAPAYA_HEADER_HTTPS_TOKEN', '123456789012345678901234567890ab');
    $_SERVER = array(
      'X_PAPAYA_HTTPS' => '123456789012345678901234567890ab',
      'SERVER_PORT' => 8000
    );
    $this->assertEquals(
      443, PapayaUtilServerPort::get()
    );
  }

  /**
  * @covers PapayaUtilServerPort::get
  * @preserveGlobalState disabled
  * @runInSeparateProcess
  */
  public function testGetWithHttpsAtProxyPrefixedEnvironmentVariable() {
    define('PAPAYA_HEADER_HTTPS_TOKEN', '123456789012345678901234567890ab');
    $_SERVER = array(
      'HTTP_X_PAPAYA_HTTPS' => '123456789012345678901234567890ab',
      'SERVER_PORT' => 8000
    );
    $this->assertEquals(
      443, PapayaUtilServerPort::get()
    );
  }
}