<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaApplicationProfileLanguagesTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileLanguages::createObject
  */
  public function testCreateObject() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->will($this->returnValue(FALSE));
    $databaseManager = $this->createMock(PapayaDatabaseManager::class);
    $databaseManager
      ->expects($this->once())
      ->method('createDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $application = $this
      ->mockPapaya()
      ->application(
        array(
          'database' => $databaseManager
        )
      );
    $profile = new PapayaApplicationProfileLanguages();
    $request = $profile->createObject($application);
    $this->assertInstanceOf(
      PapayaContentLanguages::class,
      $request
    );
  }
}
