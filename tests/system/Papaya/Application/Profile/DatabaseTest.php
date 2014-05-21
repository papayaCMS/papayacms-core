<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaApplicationProfileDatabaseTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileDatabase::createObject
  */
  public function testCreateObject() {
    $application = $this->mockPapaya()->application();
    $profile = new PapayaApplicationProfileDatabase();
    $request = $profile->createObject($application);
    $this->assertInstanceOf(
      'PapayaDatabaseManager',
      $request
    );

  }
}