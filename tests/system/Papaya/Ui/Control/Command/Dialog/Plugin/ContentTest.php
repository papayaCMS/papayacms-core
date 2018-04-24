<?php
require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaUiControlCommandDialogPluginContentTest extends PapayaTestCase {

  /**
  * @covers PapayaUiControlCommandDialogPluginContent
  */
  public function testConstructor() {
    $content = $this->createMock(PapayaPluginEditableContent::class);
    $command = new PapayaUiControlCommandDialogPluginContent($content);
    $this->assertSame($content, $command->getContent());
  }

  /**
  * @covers PapayaUiControlCommandDialogPluginContent
  */
  public function testCreateDialog() {
    $content = new PapayaPluginEditableContent(array('foo' => 'bar'));
    $command = new PapayaUiControlCommandDialogPluginContent($content);
    $this->assertEquals(
      (array)$content,
      (array)$command->dialog()->data
    );
  }

  /**
  * @covers PapayaUiControlCommandDialogPluginContent
  */
  public function testAppendTo() {
    $dialog = $this->createMock(PapayaUiDialog::class);
    $dialog
      ->expects($this->once())
      ->method('execute')
      ->will($this->returnValue(FALSE));
    $dialog
      ->expects($this->once())
      ->method('isSubmitted')
      ->will($this->returnValue(FALSE));
    $dialog
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $content = $this->createMock(PapayaPluginEditableContent::class);
    $command = new PapayaUiControlCommandDialogPluginContent($content);
    $command->dialog($dialog);
    $command->getXml();
  }

  /**
  * @covers PapayaUiControlCommandDialogPluginContent
  */
  public function testAppendToWithSubmittedDialog() {
    $dialog = $this->createMock(PapayaUiDialog::class);
    $dialog
      ->expects($this->once())
      ->method('execute')
      ->will($this->returnValue(FALSE));
    $dialog
      ->expects($this->once())
      ->method('isSubmitted')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $content = $this->createMock(PapayaPluginEditableContent::class);
    $command = new PapayaUiControlCommandDialogPluginContent($content);
    $command->dialog($dialog);
    $command->getXml();
  }

  /**
  * @covers PapayaUiControlCommandDialogPluginContent
  */
  public function testAppendToWithExecutedDialog() {
    $dialog = $this->createMock(PapayaUiDialog::class);
    $dialog
      ->expects($this->once())
      ->method('execute')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->never())
      ->method('isSubmitted');
    $dialog
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $content = $this->createMock(PapayaPluginEditableContent::class);
    $content
      ->expects($this->once())
      ->method('assign');
    $command = new PapayaUiControlCommandDialogPluginContent($content);
    $command->dialog($dialog);
    $command->getXml();
  }

  /**
  * @covers PapayaUiControlCommandDialogPluginContent
  */
  public function testAppendToWithHideExecutedDialog() {
    $dialog = $this->createMock(PapayaUiDialog::class);
    $dialog
      ->expects($this->once())
      ->method('execute')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->never())
      ->method('isSubmitted');
    $dialog
      ->expects($this->never())
      ->method('appendTo');
    $content = $this->createMock(PapayaPluginEditableContent::class);
    $content
      ->expects($this->once())
      ->method('assign');
    $command = new PapayaUiControlCommandDialogPluginContent($content);
    $command->hideAfterSuccess(TRUE);
    $command->dialog($dialog);
    $command->getXml();
  }
}
