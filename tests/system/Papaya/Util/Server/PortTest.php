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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUtilServerPortTest extends PapayaTestCase {

  private $_server;

  public function setUp() {
    $this->_server = $_SERVER;
  }

  public function tearDown() {
    $_SERVER = $this->_server;
  }

  /**
  * @covers \PapayaUtilServerPort::get
  */
  public function testGetOnEmptyRequestEnvironmentExpecting80() {
    $_SERVER = array();
    $this->assertEquals(
      80, \PapayaUtilServerPort::get()
    );
  }

  /**
  * @covers \PapayaUtilServerPort::get
  */
  public function testGet() {
    $_SERVER = array(
      'SERVER_PORT' => 80
    );
    $this->assertEquals(
      80, \PapayaUtilServerPort::get()
    );
  }

  /**
  * @covers \PapayaUtilServerPort::get
  */
  public function testGetWithNonDefaultPort() {
    $_SERVER = array(
      'SERVER_PORT' => 8080
    );
    $this->assertEquals(
      8080, \PapayaUtilServerPort::get()
    );
  }

  /**
  * @covers \PapayaUtilServerPort::get
  */
  public function testGetWithHttps() {
    $_SERVER = array(
      'HTTPS' => 'on',
      'SERVER_PORT' => 443
    );
    $this->assertEquals(
      443, \PapayaUtilServerPort::get()
    );
  }

  /**
  * @covers \PapayaUtilServerPort::get
  */
  public function testGetWithHttpsNonDefaultPort() {
    $_SERVER = array(
      'HTTPS' => 'on',
      'SERVER_PORT' => 886
    );
    $this->assertEquals(
      886, \PapayaUtilServerPort::get()
    );
  }

  /**
  * @covers \PapayaUtilServerPort::get
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
      443, \PapayaUtilServerPort::get()
    );
  }

  /**
  * @covers \PapayaUtilServerPort::get
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
      443, \PapayaUtilServerPort::get()
    );
  }
}
