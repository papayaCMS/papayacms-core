<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUtilServerNameTest extends PapayaTestCase {

  public function setUp() {
    $this->_server = $_SERVER;
  }

  public function tearDown() {
    $_SERVER = $this->_server;
  }

  /**
  * @covers PapayaUtilServerName::get
  */
  public function testGetFromHttpHost() {
    $_SERVER['HTTP_HOST'] = 'www.test.tld';
    $this->assertEquals(
      'www.test.tld', PapayaUtilServerName::get()
    );
  }

  /**
  * @covers PapayaUtilServerName::get
  */
  public function testGetFromServerName() {
    $_SERVER['SERVER_NAME'] = 'www.test.tld';
    $this->assertEquals(
      'www.test.tld', PapayaUtilServerName::get()
    );
  }

  /**
  * @covers PapayaUtilServerName::get
  */
  public function testGetExpectingEmptyString() {
    $this->assertEquals(
      '', PapayaUtilServerName::get()
    );
  }
}
