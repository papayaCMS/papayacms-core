<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaDatabaseObjectTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseObject::setDatabaseAccess
  */
  public function testSetDatabaseAccess() {
    $databaseObject = new PapayaDatabaseObject();
    $databaseAccess = $this->getMock(PapayaDatabaseAccess::class, array(), array($databaseObject));
    $databaseObject->setDatabaseAccess($databaseAccess);
    $this->assertAttributeSame(
      $databaseAccess,
      '_databaseAccessObject',
      $databaseObject
    );
  }

  /**
  * @covers PapayaDatabaseObject::getDatabaseAccess
  */
  public function testGetDatabaseAccess() {
    $databaseObject = new PapayaDatabaseObject();
    $databaseAccess = $this->getMock(PapayaDatabaseAccess::class, array(), array($databaseObject));
    $databaseObject->setDatabaseAccess($databaseAccess);
    $this->assertSame(
      $databaseAccess,
      $databaseObject->getDatabaseAccess()
    );
  }

  /**
  * @covers PapayaDatabaseObject::getDatabaseAccess
  */
  public function testGetDatabaseAccessImplizitCreate() {
    $application = $this->mockPapaya()->application();
    $databaseObject = new PapayaDatabaseObject();
    $databaseObject->papaya($application);
    $databaseAccess = $databaseObject->getDatabaseAccess();
    $this->assertInstanceOf(
      PapayaDatabaseAccess::class, $databaseAccess
    );
    $this->assertSame(
      $application,
      $databaseAccess->papaya()
    );
  }

  /**
  * @covers PapayaDatabaseObject::__call
  */
  public function testDelegation() {
    $databaseObject = new PapayaDatabaseObject();
    $databaseAccess = $this->getMock(
      PapayaDatabaseAccess::class, array('queryFmt'), array($databaseObject)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->equalTo('SQL'), $this->equalTo('SAMPLE'))
      ->will($this->returnValue(TRUE));
    $databaseObject->setDatabaseAccess($databaseAccess);
    $this->assertTrue(
      $databaseObject->databaseQueryFmt('SQL', 'SAMPLE')
    );
  }

  /**
  * @covers PapayaDatabaseObject::__call
  */
  public function testDelegationWihtInvalidFunction() {
    $databaseObject = new PapayaDatabaseObject();
    $this->setExpectedException(BadMethodCallException::class);
    $databaseObject->invalidFunctionName();
  }
}

