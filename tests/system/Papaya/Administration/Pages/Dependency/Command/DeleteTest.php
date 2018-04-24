<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaAdministrationPagesDependencyCommandDeleteTest extends PapayaTestCase {
  /**
  * @covers PapayaAdministrationPagesDependencyCommandDelete::createDialog
  */
  public function testCreateDialog() {
    $owner = $this->getMock('PapayaAdministrationPagesDependencyChanger');
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
    $this->assertEquals(1, count($dialog->fields));
    $this->assertTrue(isset($command->callbacks()->onExecuteSuccessful));
  }

  /**
  * @covers PapayaAdministrationPagesDependencyCommandDelete::dispatchDeleteMessage
  */
  public function testDispatchDeleteMessage() {
    $messages = $this->getMock('PapayaMessageManager');
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageDisplayTranslated'));
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

  public function getRecordFixture($data = array()) {
    $this->_dependencyRecordData = $data;
    $record = $this->getMock('PapayaContentPageDependency');
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
