<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaAdministrationPagePartTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationPagePart::appendTo
   */
  public function testAppendTo() {
    $commands = $this->createMock(PapayaUiControlCommand::class);
    $commands
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));
    $part = new PapayaAdministrationPagePart_TestProxy();
    $part->commands($commands);

    $this->assertEquals(
      '',
      $part->getXml()
    );
  }

  /**
   * @covers PapayaAdministrationPagePart::commands
   */
  public function testCommandsGetAfterSet() {
    $part = new PapayaAdministrationPagePart_TestProxy();
    $part->commands($commands = $this->createMock(PapayaUiControlCommand::class));
    $this->assertSame($commands, $part->commands());
  }

  /**
   * @covers PapayaAdministrationPagePart::commands
   * @covers PapayaAdministrationPagePart::_createCommands
   */
  public function testCommandsGetImplicitCreate() {
    $part = new PapayaAdministrationPagePart_TestProxy();
    $this->assertInstanceOf(PapayaUiControlCommandController::class, $part->commands());
  }

  /**
   * @covers PapayaAdministrationPagePart::toolbar
   */
  public function testToolbarGetAfterSet() {
    $part = new PapayaAdministrationPagePart_TestProxy();
    $part->toolbar($toolbar = $this->createMock(PapayaUiToolbarSet::class));
    $this->assertSame($toolbar, $part->toolbar());
  }

  /**
   * @covers PapayaAdministrationPagePart::toolbar
   */
  public function testToolbarGetImplicitCreate() {
    $part = new PapayaAdministrationPagePart_TestProxy();
    $this->assertInstanceOf(PapayaUiToolbarSet::class, $part->toolbar());
  }
}

class PapayaAdministrationPagePart_TestProxy extends PapayaAdministrationPagePart {

}
