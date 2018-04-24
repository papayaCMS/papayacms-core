<?php
require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaAdministrationThemeEditorChangesSetExportTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationThemeEditorChangesSetExport
   */
  public function testAppendTo() {
    $response = $this->getMock('PapayaResponse');
    $response
      ->expects($this->once())
      ->method('setStatus')
      ->with(200);
    $response
      ->expects($this->once())
      ->method('sendHeader')
      ->with('Content-Disposition: attachment; filename="theme set.xml"');
    $response
      ->expects($this->once())
      ->method('setContentType')
      ->with('application/octet-stream');
    $response
      ->expects($this->once())
      ->method('setContentType')
      ->with('application/octet-stream');
    $response
      ->expects($this->once())
      ->method('content')
      ->with($this->isInstanceOf('PapayaResponseContentString'));

    $themeHandler = $this->getMock('PapayaThemeHandler');
    $themeHandler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('theme')
      ->will($this->returnValue($this->getMock('PapayaContentStructure')));

    $document = $this->getMock('PapayaXmlDocument');
    $document
      ->expects($this->once())
      ->method('saveXml')
      ->will($this->returnValue('<theme/>'));

    $themeSet = $this->getMock('PapayaContentThemeSet');
    $themeSet
      ->expects($this->once())
      ->method('load')
      ->with(0);
    $themeSet
      ->expects($this->any())
      ->method('offsetGet')
      ->will(
        $this->returnValueMap(
          array(
            array('theme', 'theme'),
            array('title', 'set')
          )
        )
      );
    $themeSet
      ->expects($this->once())
      ->method('getValuesXml')
      ->with($this->isInstanceOf('PapayaContentStructure'))
      ->will($this->returnValue($document));

    $export = new PapayaAdministrationThemeEditorChangesSetExport($themeSet, $themeHandler);
    $export->papaya(
      $this->mockPapaya()->application(
        array(
          'response' => $response
        )
      )
    );
    $export->getXml();
  }
}
