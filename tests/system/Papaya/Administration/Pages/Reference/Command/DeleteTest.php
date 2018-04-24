<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaAdministrationPagesReferenceCommandDeleteTest extends PapayaTestCase {
  /**
  * @covers PapayaAdministrationPagesReferenceCommandDelete::createDialog
  */
  public function testCreateDialog() {
    $owner = $this->createMock(PapayaAdministrationPagesDependencyChanger::class);
    $owner
      ->expects($this->atLeastOnce())
      ->method('getPageId')
      ->will($this->returnValue(42));
    $owner
      ->expects($this->once())
      ->method('reference')
      ->will($this->returnValue($this->getRecordFixture(array('sourceId' => 21,'targetId' => 42))));

    $command = new PapayaAdministrationPagesReferenceCommandDelete();
    $command->owner($owner);
    $dialog = $command->createDialog();
    $this->assertCount(1, $dialog->fields);
    $this->assertTrue(isset($command->callbacks()->onExecuteSuccessful));
  }

  /**
  * @covers PapayaAdministrationPagesReferenceCommandDelete::dispatchDeleteMessage
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
    $command = new PapayaAdministrationPagesReferenceCommandDelete();
    $command->papaya($application);
    $command->dispatchDeleteMessage();
  }

  /**************************
   * Fixtures
   *************************/

  /**
   * @param array $data
   * @return PHPUnit_Framework_MockObject_MockObject
   */
  public function getRecordFixture(array $data = array()) {
    $record = $this->createMock(PapayaContentPageReference::class);
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
