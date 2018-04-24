<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaAdministrationThemeEditorTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationThemeEditor::createContent
   */
  public function testCreateContent() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $template */
    $template = $this->createMock(PapayaTemplate::class);
    $page = new PapayaAdministrationThemeEditor_TestProxy($template);
    $this->assertInstanceOf(
      'PapayaAdministrationPagePart', $page->createContent()
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditor::createNavigation
   */
  public function testCreateNavigation() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $template */
    $template = $this->createMock(PapayaTemplate::class);
    $page = new PapayaAdministrationThemeEditor_TestProxy($template);
    $this->assertInstanceOf(
      'PapayaAdministrationPagePart', $page->createNavigation()
    );
  }
}

class PapayaAdministrationThemeEditor_TestProxy extends PapayaAdministrationThemeEditor {

  public function createContent() {
    return parent::createContent();
  }

  public function createNavigation() {
    return parent::createNavigation();
  }
}
