<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

/**
 * @property array _dependencyRecordData
 */
class PapayaAdministrationPagesDependencyCommandDeleteTest extends PapayaTestCase {
  /**
  * @covers PapayaAdministrationPagesDependencyCommandDelete::createDialog
  */
  public function testCreateDialog() {
    $owner = $this->createMock(PapayaAdministrationPagesDependencyChanger::class);
    $owner
      ->expects($this->once())
      ->method('getPageId')
      ->will($this->returnValue(42));
    $owner
      ->expects($this->once())
      ->method('dependency')
      ->will($this->returnValue($this->getRecordFixture(array('id' => 21,'originId' => 42))));

    $command = new PapayaAdministrationPagesDependencyCommandDelete();
    $command->owner($owner);
    $dialog = $command->createDialog();
    $this->assertCount(1, $dialog->fields);
    $this->assertTrue(isset($command->callbacks()->onExecuteSuccessful));
  }

  /**
  * @covers PapayaAdministrationPagesDependencyCommandDelete::dispatchDeleteMessage
  */
  public function testDispatchDeleteMessage() {
    $messages = $this->createMock(PapayaMessageManager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(PapayaMessageDisplayTranslated::class));
    $application = $this->mockPapaya()->application(
      array(
        'Messages' => $messages
      )
    );
    $command = new PapayaAdministrationPagesDependencyCommandDelete();
    $command->papaya($application);
    $command->dispatchDeleteMessage();
  }

  /**************************
  * Fixtures
  **************************/

  /**
   * @param array $data
   * @return PHPUnit_Framework_MockObject_MockObject|PapayaContentPageDependency
   */
  public function getRecordFixture(array $data = array()) {
    $this->_dependencyRecordData = $data;
    $record = $this->createMock(PapayaContentPageDependency::class);
    $record
      ->expects($this->any())
      ->method('toArray')
      ->will(
        $this->returnValue($data)
      );
    $record
      ->expects($this->any())
      ->method('delete')
      ->will(
        $this->returnValue(TRUE)
      );
    return $record;
  }
}
