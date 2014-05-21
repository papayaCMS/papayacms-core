<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaAdministrationThemeEditorChangesTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationThemeEditorChanges::appendTo
   */
  public function testAppendTo() {
    $commands = $this
      ->getMockBuilder('PapayaUiControlCommandController')
      ->disableOriginalConstructor()
      ->getMock();
    $commands
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $changes = new PapayaAdministrationThemeEditorChanges();
    $changes->commands($commands);
    $this->assertEquals('', $changes->getXml());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChanges::commands
   */
  public function testCommandsGetAfterSet() {
    $commands = $this
      ->getMockBuilder('PapayaUiControlCommandController')
      ->disableOriginalConstructor()
      ->getMock();
    $changes = new PapayaAdministrationThemeEditorChanges();
    $changes->commands($commands);
    $this->assertSame($commands, $changes->commands());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChanges::commands
   */
  public function testCommandGetImplicitCreate() {
    $changes = new PapayaAdministrationThemeEditorChanges();
    $changes->papaya($this->mockPapaya()->application());
    $this->assertInstanceOf('PapayaUiControlCommandController', $changes->commands());
  }


  /**
   * @covers PapayaAdministrationThemeEditorChanges::themeSet
   */
  public function testThemeSetGetAfterSet() {
    $command = new PapayaAdministrationThemeEditorChanges();
    $command->themeSet($themeSet =  $this->getMock('PapayaContentThemeSet'));
    $this->assertSame($themeSet, $command->themeSet());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChanges::themeSet
   */
  public function testThemeSetGetImplicitCreate() {
    $command = new PapayaAdministrationThemeEditorChanges();
    $this->assertInstanceOf('PapayaContentThemeSet', $command->themeSet());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChanges::themeHandler
   */
  public function testThemeHandlerGetAfterSet() {
    $command = new PapayaAdministrationThemeEditorChanges();
    $command->themeHandler($themeHandler =  $this->getMock('PapayaThemeHandler'));
    $this->assertSame($themeHandler, $command->themeHandler());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChanges::themeHandler
   */
  public function testThemeHandlerGetImplicitCreate() {
    $command = new PapayaAdministrationThemeEditorChanges();
    $this->assertInstanceOf('PapayaThemeHandler', $command->themeHandler());
  }
}