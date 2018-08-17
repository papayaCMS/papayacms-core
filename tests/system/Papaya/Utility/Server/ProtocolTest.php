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

namespace Papaya\Utility\Server;
require_once __DIR__.'/../../../../bootstrap.php';

class ProtocolTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Utility\Server\Protocol::isSecure
   * @backupGlobals enabled
   */
  public function testIsSecureExpectingTrue() {
    $_SERVER['HTTPS'] = 'On';
    $this->assertTrue(
      Protocol::isSecure()
    );
  }

  /**
   * @covers \Papaya\Utility\Server\Protocol::isSecure
   * @backupGlobals enabled
   */
  public function testIsSecureExpectingFalse() {
    $_SERVER['HTTPS'] = NULL;
    $this->assertFalse(
      Protocol::isSecure()
    );
  }

  /**
   * @covers \Papaya\Utility\Server\Protocol::isSecure
   * @preserveGlobalState disabled
   * @runInSeparateProcess
   */
  public function testIsSecureWithPrefixedCustomHeaderExpectingTrue() {
    $_SERVER['HTTPS'] = NULL;
    $_SERVER['HTTP_X_PAPAYA_HTTPS'] = '123456789012345678901234567890ab';
    define('PAPAYA_HEADER_HTTPS_TOKEN', '123456789012345678901234567890ab');
    $this->assertTrue(
      Protocol::isSecure()
    );
  }

  /**
   * @covers \Papaya\Utility\Server\Protocol::isSecure
   * @preserveGlobalState disabled
   * @runInSeparateProcess
   */
  public function testIsSecureWithCustomHeaderExpectingTrue() {
    $_SERVER['HTTPS'] = NULL;
    $_SERVER['X_PAPAYA_HTTPS'] = '123456789012345678901234567890ab';
    define('PAPAYA_HEADER_HTTPS_TOKEN', '123456789012345678901234567890ab');
    $this->assertTrue(
      Protocol::isSecure()
    );
  }

  /**
   * @covers \Papaya\Utility\Server\Protocol::isSecure
   * @preserveGlobalState disabled
   * @runInSeparateProcess
   */
  public function testIsSecureWithEmptyCustomHeaderExpectingFalse() {
    $_SERVER['HTTPS'] = NULL;
    $_SERVER['HTTP_X_PAPAYA_HTTPS'] = '';
    define('PAPAYA_HEADER_HTTPS_TOKEN', '');
    $this->assertFalse(
      Protocol::isSecure()
    );
  }

  /**
   * @covers \Papaya\Utility\Server\Protocol::isSecure
   * @preserveGlobalState disabled
   * @runInSeparateProcess
   */
  public function testIsSecureWithInvalidCustomHeaderExpectingFalse() {
    $_SERVER['HTTPS'] = NULL;
    $_SERVER['HTTP_X_PAPAYA_HTTPS'] = '123456789012345678901234567890ab';
    define('PAPAYA_HEADER_HTTPS_TOKEN', 'ef123456789012345678901234567890');
    $this->assertFalse(
      Protocol::isSecure()
    );
  }

  /**
   * @covers \Papaya\Utility\Server\Protocol::isSecure
   * @backupGlobals enabled
   */
  public function testIsSecureWithCustomHeaderWithoutConstantExpectingFalse() {
    $_SERVER['HTTPS'] = NULL;
    $_SERVER['HTTP_X_PAPAYA_HTTPS'] = '123456789012345678901234567890ab';
    $this->assertFalse(
      Protocol::isSecure()
    );
  }

  /**
   * @covers \Papaya\Utility\Server\Protocol::get
   * @backupGlobals enabled
   */
  public function testGetExpectingHttps() {
    $_SERVER['HTTPS'] = 'On';
    $this->assertEquals(
      'https', Protocol::get()
    );
  }

  /**
   * @covers \Papaya\Utility\Server\Protocol::get
   * @backupGlobals enabled
   */
  public function testGetExpectingHttp() {
    $_SERVER['HTTPS'] = NULL;
    $this->assertEquals(
      'http', Protocol::get()
    );
  }

  /**
   * @covers \Papaya\Utility\Server\Protocol::get
   */
  public function testGetWithParameterExpectingHttp() {
    $this->assertEquals(
      'http', Protocol::get(Protocol::HTTP)
    );
  }

  /**
   * @covers \Papaya\Utility\Server\Protocol::get
   */
  public function testGetWithParameterExpectingHttps() {
    $this->assertEquals(
      'https', Protocol::get(Protocol::HTTPS)
    );
  }

  /**
   * @covers \Papaya\Utility\Server\Protocol::getDefaultPort
   * @backupGlobals enabled
   */
  public function testGetDefaultPortExpectingHttps() {
    $_SERVER['HTTPS'] = 'On';
    $this->assertEquals(
      443, Protocol::getDefaultPort()
    );
  }

  /**
   * @covers \Papaya\Utility\Server\Protocol::getDefaultPort
   * @backupGlobals enabled
   */
  public function testGetDefaultPortExpectingHttp() {
    $_SERVER['HTTPS'] = NULL;
    $this->assertEquals(
      80, Protocol::getDefaultPort()
    );
  }

  /**
   * @covers \Papaya\Utility\Server\Protocol::isSecure
   * @preserveGlobalState disabled
   * @runInSeparateProcess
   */
  public function testGetDefaultPortWithValidCutomHeaderExpectingHttp() {
    $_SERVER['HTTPS'] = NULL;
    $_SERVER['HTTP_X_PAPAYA_HTTPS'] = '123456789012345678901234567890ab';
    define('PAPAYA_HEADER_HTTPS_TOKEN', '123456789012345678901234567890ab');
    $this->assertEquals(
      80, Protocol::getDefaultPort()
    );
  }
}
