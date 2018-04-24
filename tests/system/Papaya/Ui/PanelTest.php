<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaUiPanelTest extends PapayaTestCase {

  /**
  * @covers PapayaUiPanel::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('sample');
    $panel = new PapayaUiPanel_TestProxy();
    $this->assertEquals(
      '<panel/>',
      $panel->getXml()
    );
  }

  /**
  * @covers PapayaUiPanel::appendTo
  * @covers PapayaUiPanel::setCaption
  */
  public function testAppendToWithCaption() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('sample');
    $panel = new PapayaUiPanel_TestProxy();
    $panel->setCaption('sample caption');
    $this->assertEquals(
      '<panel title="sample caption"/>',
      $panel->getXml()
    );
  }

  /**
  * @covers PapayaUiPanel::toolbars
  */
  public function testToolbarsGetAfterSet() {
    $panel = new PapayaUiPanel_TestProxy();
    $toolbars = $this->getMock('PapayaUiToolbars');
    $this->assertSame($toolbars, $panel->toolbars($toolbars));
  }

  /**
  * @covers PapayaUiPanel::toolbars
  */
  public function testToolbarsGetImplicitCreate() {
    $panel = new PapayaUiPanel_TestProxy();
    $toolbars = $panel->toolbars();
    $this->assertInstanceOf('PapayaUiToolbars', $toolbars);
  }
}

class PapayaUiPanel_TestProxy extends PapayaUiPanel {

}
