<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaProfilerStorageXhguiTest extends PapayaTestCase {

  /**
  * @covers PapayaProfilerStorageXhgui::__construct
  */
  public function testConstructor() {
    $storage = new PapayaProfilerStorageXhgui('database', 'table', 'foo');
    $this->assertAttributeEquals(
      'database', '_database', $storage
    );
    $this->assertAttributeEquals(
      'table', '_tableName', $storage
    );
    $this->assertAttributeEquals(
      'foo', '_serverId', $storage
    );
  }

  /**
  * @covers PapayaProfilerStorageXhgui::saveRun
  * @covers PapayaProfilerStorageXhgui::getId
  * @covers PapayaProfilerStorageXhgui::normalizeUrl
  * @covers PapayaProfilerStorageXhgui::removeSid
  */
  public function testSaveRun() {
    $databaseAccess = $this
      ->getMockBuilder(PapayaDatabaseAccess::class)
      ->disableOriginalConstructor()
      ->setMethods(array('queryFmtWrite'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmtWrite')
      ->with($this->isType('string'), $this->isType('array'))
      ->will($this->returnValue(TRUE));
    $storage = new PapayaProfilerStorageXhgui('database', 'table', 'foo');
    $storage->setDatabaseAccess($databaseAccess);
    $this->assertNotEmpty(
      $storage->saveRun(array(), 'type')
    );
  }

  /**
  * @covers PapayaProfilerStorageXhgui::setDatabaseAccess
  * @covers PapayaProfilerStorageXhgui::getDatabaseAccess
  */
  public function testGetDatabaseAccessAfterSet() {
    $databaseAccess = $this
      ->getMockBuilder(PapayaDatabaseAccess::class)
      ->disableOriginalConstructor()
      ->getMock();
    $storage = new PapayaProfilerStorageXhgui('database', 'table', 'foo');
    $storage->setDatabaseAccess($databaseAccess);
    $this->assertSame(
      $databaseAccess, $storage->getDatabaseAccess()
    );
  }

  /**
  * @covers PapayaProfilerStorageXhgui::getDatabaseAccess
  */
  public function testGetDatabaseAccessImplicitCreate() {
    $storage = new PapayaProfilerStorageXhgui('database', 'table', 'foo');
    $storage->papaya($this->mockPapaya()->application());
    $this->assertInstanceOf(
      PapayaDatabaseAccess::class, $storage->getDatabaseAccess()
    );
    $this->assertSame(
      $storage->papaya(), $storage->getDatabaseAccess()->papaya()
    );
  }
}
