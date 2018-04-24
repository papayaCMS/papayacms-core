<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaAdministrationPagesDependencySynchronizationsTest extends PapayaTestCase {

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizations::getIcons
  */
  public function testGetIcons() {
    $synchronizations = new PapayaAdministrationPagesDependencySynchronizations();
    $icons = $synchronizations->getIcons();
    $this->assertInstanceOf(PapayaUiIconList::class, $icons);
    $this->assertSame($icons, $synchronizations->getIcons());
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizations::getList
  */
  public function testGetList() {
    $synchronizations = new PapayaAdministrationPagesDependencySynchronizations();
    $list = $synchronizations->getList();
    $this->assertInternalType('array', $list);
    $this->assertEquals($list, $synchronizations->getList());
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizations::dependencies
  */
  public function testDependenciesGetAfterSet() {
    $synchronizations = new PapayaAdministrationPagesDependencySynchronizations();
    $dependencies = $this->createMock(PapayaContentPageDependencies::class);
    $this->assertSame($dependencies, $synchronizations->dependencies($dependencies));
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizations::dependencies
  */
  public function testDependenciesGetImplicitCreate() {
    $synchronizations = new PapayaAdministrationPagesDependencySynchronizations();
    $this->assertInstanceOf(PapayaContentPageDependencies::class, $synchronizations->dependencies());
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizations::getAction
  */
  public function testGetAction() {
    $synchronizations = new PapayaAdministrationPagesDependencySynchronizations();
    $action = $synchronizations->getAction(PapayaContentPageDependency::SYNC_PROPERTIES);
    $this->assertInstanceOf(
      PapayaAdministrationPagesDependencySynchronization::class,
      $action
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizations::getAction
  */
  public function testGetActionExpectingNull() {
    $synchronizations = new PapayaAdministrationPagesDependencySynchronizations();
    $this->assertNull(
      $synchronizations->getAction(-1)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizations::getTargets
  */
  public function testGetTargets() {
    $dependencies = $this->createMock(PapayaContentPageDependencies::class);
    $dependencies
      ->expects($this->once())
      ->method('load')
      ->with(42)
      ->will($this->returnValue(TRUE));
    $dependencies
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new ArrayIterator(
            array(
              23 => array(
                'id' => 23,
                'synchronization' => PapayaContentPageDependency::SYNC_PROPERTIES
              ),
              46 => array(
                'id' => 46,
                'synchronization' => 0
              )
            )
          )
        )
      );
    $synchronizations = new PapayaAdministrationPagesDependencySynchronizations();
    $synchronizations->dependencies($dependencies);
    $this->assertEquals(
      array(23),
      $synchronizations->getTargets(42, PapayaContentPageDependency::SYNC_PROPERTIES)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizations::getTargets
  */
  public function testGetTargetsExpectingNull() {
    $dependencies = $this->createMock(PapayaContentPageDependencies::class);
    $dependencies
      ->expects($this->once())
      ->method('load')
      ->with(42)
      ->will($this->returnValue(FALSE));
    $dependencies
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new ArrayIterator(
            array()
          )
        )
      );
    $synchronizations = new PapayaAdministrationPagesDependencySynchronizations();
    $synchronizations->dependencies($dependencies);
    $this->assertNull(
      $synchronizations->getTargets(42, PapayaContentPageDependency::SYNC_PROPERTIES)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizations::synchronizeDependency
  */
  public function testSynchronizeDependency() {
    $dependency = $this->getRecordFixture(
      array(
        'id' => 21,
        'originId' => 42,
        'synchronization' => PapayaContentPageDependency::SYNC_PROPERTIES
      )
    );
    $action = $this->createMock(PapayaAdministrationPagesDependencySynchronization::class);
    $action
      ->expects($this->once())
      ->method('synchronize')
      ->with(array(21), 42, NULL);
    $synchronizations =
      new PapayaAdministrationPagesDependencySynchronizations_TestProxy();
    $synchronizations->actionMock = $action;
    $synchronizations->synchronizeDependency($dependency);
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizations::synchronizeAction
  */
  public function testSynchronizeAction() {
    $action = $this->createMock(PapayaAdministrationPagesDependencySynchronization::class);
    $action
      ->expects($this->once())
      ->method('synchronize')
      ->with(array(21), 42, array(3, 4));
    $synchronizations =
      new PapayaAdministrationPagesDependencySynchronizations_TestProxy();
    $synchronizations->actionMock = $action;
    $synchronizations->targetsList = array(21);
    $synchronizations->synchronizeAction(
      PapayaContentPageDependency::SYNC_PROPERTIES, 42, array(3, 4)
    );
  }

  /**************************
  * Fixtures
  **************************/

  /**
   * @param array $data
   * @return PHPUnit_Framework_MockObject_MockObject|PapayaContentPageDependency
   */
  public function getRecordFixture(array $data = array()) {
    $record = $this->createMock(PapayaContentPageDependency::class);
    $record
      ->expects($this->any())
      ->method('getIterator')
      ->will(
        $this->returnValue(new ArrayIterator($data))
      );
    $record
      ->expects($this->any())
      ->method('__get')
      ->withAnyParameters()
      ->willReturnCallback(
        function($name) use ($data) {
          return $data[$name];
        }
      );
    return $record;
  }
}

class PapayaAdministrationPagesDependencySynchronizations_TestProxy
  extends PapayaAdministrationPagesDependencySynchronizations {

  public $actionMock;
  public $targetsList;

  public function getAction($synchronization) {
    return $this->actionMock;
  }

  public function getTargets($originId, $synchronization) {
    return $this->targetsList;
  }
}
