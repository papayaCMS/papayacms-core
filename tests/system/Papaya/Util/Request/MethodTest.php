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

class PapayaUtilRequestMethodTest extends PapayaTestCase {

  private $_server;

  public function setUp() {
    $this->_server = $_SERVER;
  }

  public function tearDown() {
    $_SERVER = $this->_server;
  }

  /**
  * @covers \PapayaUtilRequestMethod::get
  */
  public function testGetOnEmptyRequestEnvironmentExpectingGet() {
    $_SERVER = array();
    $this->assertEquals(
      'get', \PapayaUtilRequestMethod::get()
    );
  }

  /**
  * @covers \PapayaUtilRequestMethod::get
  */
  public function testGetForPostRequestInLowercase() {
    $_SERVER = array(
      'REQUEST_METHOD' => 'POST'
    );
    $this->assertEquals(
      'post', \PapayaUtilRequestMethod::get()
    );
  }

  /**
  * @covers \PapayaUtilRequestMethod::get
  */
  public function testGetForPostRequestInUppercase() {
    $_SERVER = array(
      'REQUEST_METHOD' => 'POST'
    );
    $this->assertEquals(
      'POST', \PapayaUtilRequestMethod::get(PapayaUtilRequestMethod::FORMAT_UPPERCASE)
    );
  }

  /**
  * @covers \PapayaUtilRequestMethod::isGet
  */
  public function testIsGetExpectingTrue() {
    $_SERVER = array(
      'REQUEST_METHOD' => 'GET'
    );
    $this->assertTrue(PapayaUtilRequestMethod::isGet());
  }

  /**
  * @covers \PapayaUtilRequestMethod::isGet
  */
  public function testIsGetExpectingFalse() {
    $_SERVER = array(
      'REQUEST_METHOD' => 'POST'
    );
    $this->assertFalse(PapayaUtilRequestMethod::isGet());
  }

  /**
  * @covers \PapayaUtilRequestMethod::isPost
  */
  public function testIsPostExpectingTrue() {
    $_SERVER = array(
      'REQUEST_METHOD' => 'POST'
    );
    $this->assertTrue(PapayaUtilRequestMethod::isPost());
  }

  /**
  * @covers \PapayaUtilRequestMethod::isPost
  */
  public function testIsPostExpectingFalse() {
    $_SERVER = array(
      'REQUEST_METHOD' => 'GET'
    );
    $this->assertFalse(PapayaUtilRequestMethod::isPost());
  }

  /**
  * @covers \PapayaUtilRequestMethod::isPut
  */
  public function testIsPutExpectingTrue() {
    $_SERVER = array(
      'REQUEST_METHOD' => 'PUT'
    );
    $this->assertTrue(PapayaUtilRequestMethod::isPut());
  }

  /**
  * @covers \PapayaUtilRequestMethod::isPut
  */
  public function testIsPutExpectingFalse() {
    $_SERVER = array(
      'REQUEST_METHOD' => 'GET'
    );
    $this->assertFalse(PapayaUtilRequestMethod::isPut());
  }
}
