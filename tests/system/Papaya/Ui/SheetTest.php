<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaUiSheetTest extends PapayaTestCase {

  /**
  * @covers PapayaUiPanel::appendTo
  */
  public function testAppendTo() {
    $sheet = new PapayaUiSheet();
    $this->assertXmlStringEqualsXmlString(
      '<sheet><text/></sheet>',
      $sheet->getXml()
    );
  }

  /**
  * @covers PapayaUiPanel::appendTo
  */
  public function testAppendToWithTitle() {
    $sheet = new PapayaUiSheet();
    $sheet->title('Sample Title');
    $this->assertXmlStringEqualsXmlString(
      '<?xml version="1.0"?>
       <sheet>
         <header>
           <title>Sample Title</title>
         </header>
         <text/>
       </sheet>',
      $sheet->getXml()
    );
  }

  /**
  * @covers PapayaUiPanel::appendTo
  */
  public function testAppendToWithSubtitle() {
    $sheet = new PapayaUiSheet();
    $sheet->subtitles()->add(new PapayaUiSheetSubtitle('Sample Title'));
    $this->assertXmlStringEqualsXmlString(
      '<?xml version="1.0"?>
       <sheet>
         <header>
           <subtitle>Sample Title</subtitle>
         </header>
         <text/>
       </sheet>',
      $sheet->getXml()
    );
  }

  /**
  * @covers PapayaUiPanel::appendTo
  */
  public function testAppendToWithContent() {
    $sheet = new PapayaUiSheet();
    $sheet
      ->content()
      ->appendElement('div', array('class' => 'simple'), 'Content');
    $this->assertXmlStringEqualsXmlString(
      '<?xml version="1.0"?>
       <sheet>
         <text>
           <div class="simple">Content</div>
         </text>
       </sheet>',
      $sheet->getXml()
    );
  }
}