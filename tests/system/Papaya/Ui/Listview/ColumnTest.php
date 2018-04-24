<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiListviewColumnTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewColumn::__construct
  */
  public function testConstructor() {
    $column = new PapayaUiListviewColumn('test title');
    $this->assertAttributeEquals(
      'test title', '_caption', $column
    );
  }

  /**
  * @covers PapayaUiListviewColumn::__construct
  * @covers PapayaUiListviewColumn::setAlign
  */
  public function testConstructorWithAllParameters() {
    $column = new PapayaUiListviewColumn(
      'test title', PapayaUiOptionAlign::CENTER
    );
    $this->assertAttributeEquals(
      PapayaUiOptionAlign::CENTER, '_align', $column
    );
  }

  /**
  * @covers PapayaUiListviewColumn::getAlign
  * @covers PapayaUiListviewColumn::setAlign
  */
  public function testGetAlignAfterSetAlign() {
    $column = new PapayaUiListviewColumn('test title');
    $column->setAlign(PapayaUiOptionAlign::RIGHT);
    $this->assertEquals(
      PapayaUiOptionAlign::RIGHT, $column->getAlign()
    );
  }

  /**
  * @covers PapayaUiListviewColumn::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendChild($dom->createElement('sample'));
    $column = new PapayaUiListviewColumn('test title');
    $column->appendTo($dom->documentElement);
    $this->assertEquals(
      '<sample><col align="left">test title</col></sample>',
      $dom->saveXml($dom->documentElement)
    );
  }

}
