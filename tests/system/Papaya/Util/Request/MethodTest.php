<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaUtilRequestMethodTest extends PapayaTestCase {

  public function setUp() {
    $this->_server = $_SERVER;
  }

  public function tearDown() {
    $_SERVER = $this->_server;
  }

  /**
  * @covers PapayaUtilRequestMethod::get
  */
  public function testGetOnEmptyRequestEnvironmentExpectingGet() {
    $_SERVER = array();
    $this->assertEquals(
      'get', PapayaUtilRequestMethod::get()
    );
  }

  /**
  * @covers PapayaUtilRequestMethod::get
  */
  public function testGetForPostRequestInLowercase() {
    $_SERVER = array(
      'REQUEST_METHOD' => 'POST'
    );
    $this->assertEquals(
      'post', PapayaUtilRequestMethod::get()
    );
  }

  /**
  * @covers PapayaUtilRequestMethod::get
  */
  public function testGetForPostRequestInUppercase() {
    $_SERVER = array(
      'REQUEST_METHOD' => 'POST'
    );
    $this->assertEquals(
      'POST', PapayaUtilRequestMethod::get(PapayaUtilRequestMethod::FORMAT_UPPERCASE)
    );
  }

  /**
  * @covers PapayaUtilRequestMethod::isGet
  */
  public function testIsGetExpectingTrue() {
    $_SERVER = array(
      'REQUEST_METHOD' => 'GET'
    );
    $this->assertTrue(PapayaUtilRequestMethod::isGet());
  }

  /**
  * @covers PapayaUtilRequestMethod::isGet
  */
  public function testIsGetExpectingFalse() {
    $_SERVER = array(
      'REQUEST_METHOD' => 'POST'
    );
    $this->assertFalse(PapayaUtilRequestMethod::isGet());
  }

  /**
  * @covers PapayaUtilRequestMethod::isPost
  */
  public function testIsPostExpectingTrue() {
    $_SERVER = array(
      'REQUEST_METHOD' => 'POST'
    );
    $this->assertTrue(PapayaUtilRequestMethod::isPost());
  }

  /**
  * @covers PapayaUtilRequestMethod::isPost
  */
  public function testIsPostExpectingFalse() {
    $_SERVER = array(
      'REQUEST_METHOD' => 'GET'
    );
    $this->assertFalse(PapayaUtilRequestMethod::isPost());
  }

  /**
  * @covers PapayaUtilRequestMethod::isPut
  */
  public function testIsPutExpectingTrue() {
    $_SERVER = array(
      'REQUEST_METHOD' => 'PUT'
    );
    $this->assertTrue(PapayaUtilRequestMethod::isPut());
  }

  /**
  * @covers PapayaUtilRequestMethod::isPut
  */
  public function testIsPutExpectingFalse() {
    $_SERVER = array(
      'REQUEST_METHOD' => 'GET'
    );
    $this->assertFalse(PapayaUtilRequestMethod::isPut());
  }
}