<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaAdministrationPagesDependencySynchronizationBoxesTest extends PapayaTestCase {

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationBoxes::synchronize
  * @covers PapayaAdministrationPagesDependencySynchronizationBoxes::setInheritanceStatus
  */
  public function testSynchronize() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with('topic_id', array(42))
      ->will($this->returnValue("topic_id = '42'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmtWrite')
      ->with("UPDATE %s SET box_useparent = '%d' WHERE topic_id = '42'", array('table_topic', 1))
      ->will($this->returnValue(TRUE));

    $page = $this->createMock(PapayaContentPageWork::class);
    $page
      ->expects($this->once())
      ->method('load')
      ->with(21)
      ->will($this->returnValue(TRUE));
    $page
      ->expects($this->once())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $page
      ->expects($this->once())
      ->method('__get')
      ->with('inheritBoxes')
      ->will($this->returnValue(1));

    $boxes = $this->createMock(PapayaContentPageBoxes::class);
    $boxes
      ->expects($this->once())
      ->method('load')
      ->with(21)
      ->will($this->returnValue(TRUE));
    $boxes
      ->expects($this->once())
      ->method('copyTo')
      ->with(array(42))
      ->will($this->returnValue(TRUE));

    $action = new PapayaAdministrationPagesDependencySynchronizationBoxes();
    $action->page($page);
    $action->boxes($boxes);
    $action->synchronize(array(42), 21);
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationBoxes::synchronize
  * @covers PapayaAdministrationPagesDependencySynchronizationBoxes::setInheritanceStatus
  */
  public function testSynchronizeLoadFailed() {
    $page = $this->createMock(PapayaContentPageWork::class);
    $page
      ->expects($this->once())
      ->method('load')
      ->with(21)
      ->will($this->returnValue(FALSE));
    $action = new PapayaAdministrationPagesDependencySynchronizationBoxes();
    $action->page($page);
    $action->synchronize(array(42), 21);
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationBoxes::boxes
  */
  public function testBoxesGetAfterSet() {
    $boxes = $this->createMock(PapayaContentPageBoxes::class);
    $action = new PapayaAdministrationPagesDependencySynchronizationBoxes();
    $this->assertSame(
      $boxes, $action->boxes($boxes)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationBoxes::boxes
  */
  public function testBoxesGetImplicitCreate() {
    $action = new PapayaAdministrationPagesDependencySynchronizationBoxes();
    $this->assertInstanceOf(
      PapayaContentPageBoxes::class, $action->boxes()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationBoxes::page
  */
  public function testPageGetAfterSet() {
    $page = $this->createMock(PapayaContentPageWork::class);
    $action = new PapayaAdministrationPagesDependencySynchronizationBoxes();
    $this->assertSame(
      $page, $action->page($page)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencySynchronizationBoxes::page
  */
  public function testPageGetImplicitCreate() {
    $action = new PapayaAdministrationPagesDependencySynchronizationBoxes();
    $this->assertInstanceOf(
      PapayaContentPageWork::class, $action->page()
    );
  }
}
