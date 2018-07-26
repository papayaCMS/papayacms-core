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

class PapayaUrlCurrentTest extends \PapayaTestCase {

  /**
  * @covers \PapayaUrlCurrent::__construct
  * @backupGlobals enabled
  */
  public function testConstructor() {
    $_SERVER['HTTP_HOST'] = 'www.sample.tld';
    $urlObject = new \PapayaUrlCurrent();
    $this->assertSame(
      'http://www.sample.tld',
      $urlObject->getUrl()
    );
  }

  /**
  * @covers \PapayaUrlCurrent::__construct
  * @backupGlobals enabled
  */
  public function testConstructorOnHttps() {
    $_SERVER['HTTP_HOST'] = 'www.sample.tld';
    $_SERVER['HTTPS'] = 'on';
    $urlObject = new \PapayaUrlCurrent();
    $this->assertSame(
      'https://www.sample.tld',
      $urlObject->getUrl()
    );
  }

  /**
  * @covers \PapayaUrlCurrent::__construct
  * @backupGlobals enabled
  */
  public function testConstructorWithUrl() {
    $_SERVER = array();
    $urlObject = new \PapayaUrlCurrent('http://www.sample.tld');
    $this->assertSame(
      'http://www.sample.tld',
      $urlObject->getUrl()
    );
  }

  /**
   * @covers \PapayaUrlCurrent::getUrlFromEnvironment
   * @covers \PapayaUrlCurrent::_getServerValue
   * @backupGlobals enabled
   * @dataProvider getUrlDataProvider
   * @param array $environment
   * @param string $expected
   */
  public function testGetUrlFromEnvironment(array $environment, $expected) {
    $urlObject = new \PapayaUrlCurrent();
    $_SERVER = $environment;
    $this->assertSame($expected, $urlObject->getUrlFromEnvironment());
  }

  /*************************************
  * Data Providers
  *************************************/

  public static function getUrlDataProvider() {
    return array(
      array(
        array(),
        NULL
      ),
      array(
        array(
          'HTTP_HOST' => 'www.sample.tld'
        ),
        'http://www.sample.tld'
      ),
      array(
        array(
          'SERVER_NAME' => 'www.sample.tld',
          'SERVER_PORT' => '8080'
        ),
        'http://www.sample.tld:8080'
      ),
      array(
        array(
          'HTTPS' => 'on',
          'HTTP_HOST' => 'www.sample.tld',
          'REQUEST_URI' => '/path'
        ),
        'https://www.sample.tld/path'
      ),
      array(
        array(
          'SERVER_NAME' => 'www.sample.tld',
          'REQUEST_URI' => '/path'
        ),
        'http://www.sample.tld/path'
      ),
      array(
        array(
          'HTTP_HOST' => 'www.sample.tld',
          'SERVER_PORT' => '80',
          'REQUEST_URI' => '/path'
        ),
        'http://www.sample.tld/path'
      ),
      array(
        array(
          'HTTP_HOST' => 'www.sample.tld',
          'SERVER_PORT' => '443',
          'REQUEST_URI' => '/path'
        ),
        'http://www.sample.tld:443/path'
      ),
      array(
        array(
          'HTTPS' => 'on',
          'HTTP_HOST' => 'www.sample.tld',
          'SERVER_PORT' => '443',
          'REQUEST_URI' => '/path'
        ),
        'https://www.sample.tld/path'
      )
    );
  }
}
