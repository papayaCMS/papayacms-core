<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaApplicationProfileSessionTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileSession::createObject
  */
  public function testCreateObject() {
    $profile = new PapayaApplicationProfileSession();
    $session = $profile->createObject($application = NULL);
    $this->assertInstanceOf(
      'PapayaSession', $session
    );
  }
}
