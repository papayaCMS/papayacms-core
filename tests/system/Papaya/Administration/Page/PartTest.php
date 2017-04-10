<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaAdministrationPagePartTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationPagePart::appendTo
   */
  public function testAppendTo() {
    $commands = $this->getMock('PapayaUiControlCommand');
    $commands
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
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
    $part->commands($commands = $this->getMock('PapayaUiControlCommand'));
    $this->assertSame($commands, $part->commands());
  }

  /**
   * @covers PapayaAdministrationPagePart::commands
   * @covers PapayaAdministrationPagePart::_createCommands
   */
  public function testCommandsGetImplicitCreate() {
    $part = new PapayaAdministrationPagePart_TestProxy();
    $this->assertInstanceOf('PapayaUiControlCommandController', $part->commands());
  }

  /**
   * @covers PapayaAdministrationPagePart::toolbar
   */
  public function testToolbarGetAfterSet() {
    $part = new PapayaAdministrationPagePart_TestProxy();
    $part->toolbar($toolbar = $this->getMock('PapayaUiToolbarSet'));
    $this->assertSame($toolbar, $part->toolbar());
  }

  /**
   * @covers PapayaAdministrationPagePart::toolbar
   */
  public function testToolbarGetImplicitCreate() {
    $part = new PapayaAdministrationPagePart_TestProxy();
    $this->assertInstanceOf('PapayaUiToolbarSet', $part->toolbar());
  }
}

class PapayaAdministrationPagePart_TestProxy extends PapayaAdministrationPagePart {

}