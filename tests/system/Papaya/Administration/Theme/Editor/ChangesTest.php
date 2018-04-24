<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaAdministrationThemeEditorChangesTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationThemeEditorChanges::appendTo
   */
  public function testAppendTo() {
    $commands = $this
      ->getMockBuilder(PapayaUiControlCommandController::class)
      ->disableOriginalConstructor()
      ->getMock();
    $commands
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));
    $changes = new PapayaAdministrationThemeEditorChanges();
    $changes->commands($commands);
    $this->assertEmpty($changes->getXml());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChanges::commands
   */
  public function testCommandsGetAfterSet() {
    $commands = $this
      ->getMockBuilder(PapayaUiControlCommandController::class)
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
    $this->assertInstanceOf(PapayaUiControlCommandController::class, $changes->commands());
  }


  /**
   * @covers PapayaAdministrationThemeEditorChanges::themeSet
   */
  public function testThemeSetGetAfterSet() {
    $command = new PapayaAdministrationThemeEditorChanges();
    $command->themeSet($themeSet =  $this->createMock(PapayaContentThemeSet::class));
    $this->assertSame($themeSet, $command->themeSet());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChanges::themeSet
   */
  public function testThemeSetGetImplicitCreate() {
    $command = new PapayaAdministrationThemeEditorChanges();
    $this->assertInstanceOf(PapayaContentThemeSet::class, $command->themeSet());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChanges::themeHandler
   */
  public function testThemeHandlerGetAfterSet() {
    $command = new PapayaAdministrationThemeEditorChanges();
    $command->themeHandler($themeHandler = $this->createMock(PapayaThemeHandler::class));
    $this->assertSame($themeHandler, $command->themeHandler());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChanges::themeHandler
   */
  public function testThemeHandlerGetImplicitCreate() {
    $command = new PapayaAdministrationThemeEditorChanges();
    $this->assertInstanceOf(PapayaThemeHandler::class, $command->themeHandler());
  }
}
