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

namespace Papaya\Utility\Request;
require_once __DIR__.'/../../../../bootstrap.php';

class URLTest extends \Papaya\TestFramework\TestCase {

  private $_server;

  public function setUp(): void {
    $this->_server = $_SERVER;
  }

  public function tearDown(): void {
    $_SERVER = $this->_server;
  }

  /**
   * @covers \Papaya\Utility\Request\URL::get
   */
  public function testGetOnEmptyRequestEnvironmentExpectingEmptyString() {
    $_SERVER = array();
    $this->assertEquals(
      '', URL::get()
    );
  }

  /**
   * @covers \Papaya\Utility\Request\URL::get
   */
  public function testGet() {
    $_SERVER = array(
      'HTTP_HOST' => 'www.sample.tld',
      'SERVER_PORT' => 80,
      'REQUEST_URI' => '/'
    );
    $this->assertEquals(
      'http://www.sample.tld/', URL::get()
    );
  }

  /**
   * @covers \Papaya\Utility\Request\URL::get
   */
  public function testGetWithNonDefaultPort() {
    $_SERVER = array(
      'HTTP_HOST' => 'www.sample.tld',
      'SERVER_PORT' => 8080,
      'REQUEST_URI' => '/'
    );
    $this->assertEquals(
      'http://www.sample.tld:8080/', URL::get()
    );
  }

  /**
   * @covers \Papaya\Utility\Request\URL::get
   */
  public function testGetWithHttps() {
    $_SERVER = array(
      'HTTPS' => 'on',
      'HTTP_HOST' => 'www.sample.tld',
      'SERVER_PORT' => 443,
      'REQUEST_URI' => '/secure.html'
    );
    $this->assertEquals(
      'https://www.sample.tld/secure.html', URL::get()
    );
  }

  /**
   * @covers \Papaya\Utility\Request\URL::get
   * @preserveGlobalState disabled
   * @runInSeparateProcess
   */
  public function testGetWithHttpsAtProxy() {
    define('PAPAYA_HEADER_HTTPS_TOKEN', '123456789012345678901234567890ab');
    $_SERVER = array(
      'HTTP_X_PAPAYA_HTTPS' => '123456789012345678901234567890ab',
      'HTTPS' => 'on',
      'HTTP_HOST' => 'www.sample.tld',
      'SERVER_PORT' => 8000,
      'REQUEST_URI' => '/secure.html'
    );
    $this->assertEquals(
      'https://www.sample.tld/secure.html', URL::get()
    );
  }
}
