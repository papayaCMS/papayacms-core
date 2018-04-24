<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaApplicationProfileLanguagesTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileLanguages::createObject
  */
  public function testCreateObject() {
    $databaseAccess = $this
      ->getMockBuilder(PapayaDatabaseAccess::class)
      ->disableOriginalConstructor()
      ->setMethods(array('queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->will($this->returnValue(FALSE));
    $databaseManager = $this->createMock(PapayaDatabaseManager::class);
    $databaseManager
      ->expects($this->once())
      ->method('createDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $application = $this->createMock(PapayaApplication::class);
    $application
      ->expects($this->any())
      ->method('__get')
      ->with('database')
      ->will($this->returnValue($databaseManager));
    $profile = new PapayaApplicationProfileLanguages();
    $request = $profile->createObject($application);
    $this->assertInstanceOf(
      PapayaContentLanguages::class,
      $request
    );
  }
}
