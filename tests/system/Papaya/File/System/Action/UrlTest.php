<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaFileSystemActionUrlTest extends PapayaTestCase {

  /**
   * @covers PapayaFileSystemActionUrl::__construct
   */
  public function testConstructor() {
    $action = new PapayaFileSystemActionUrl('http://www.sample.tld/success');
    $this->assertAttributeEquals(
      'http://www.sample.tld/success', '_url', $action
    );
  }

  /**
   * @covers PapayaFileSystemActionUrl::execute
   */
  public function testExecute() {
    $action = new PapayaFileSystemActionUrl_TestProxy('http://test.tld/remote.php');
    $action->execute(array('foo' => 'bar'));
    $this->assertEquals(
      array(
        'http://test.tld/remote.php?foo=bar'
      ),
      $action->fetchCall
    );
  }
}

class PapayaFileSystemActionUrl_TestProxy extends PapayaFileSystemActionUrl {

  public $fetchCall = array();

  protected function fetch($url) {
    $this->fetchCall = func_get_args();
  }
}
