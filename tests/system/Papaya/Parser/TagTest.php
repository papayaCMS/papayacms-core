<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaParserTagTest extends PapayaTestCase {
  /**
   * @covers PapayaParserTag::getXml
   */
  public function testGetXml() {
    $control = new PapayaParserTag_TestProxy();
    $dom = new PapayaXmlDocument;
    $control->nodeStub = array(
      $dom->appendElement('sample')
    );
    $this->assertEquals(
      '<sample/>', $control->getXml()
    );
  }

  /**
   * @covers PapayaParserTag::getXml
   */
  public function testGetXmlWithTextNode() {
    $control = new PapayaParserTag_TestProxy();
    $dom = new PapayaXmlDocument;
    $control->nodeStub = array(
      $dom->createTextNode('sample')
    );
    $this->assertEquals(
      'sample', $control->getXml()
    );
  }

  /**
   * @covers PapayaParserTag::getXml
   */
  public function testGetXmlWithSeveralNodes() {
    $control = new PapayaParserTag_TestProxy();
    $dom = new PapayaXmlDocument;
    $control->nodeStub = array(
      $dom->createTextNode('sample'),
      $dom->createElement('sample'),
      $dom->createComment('comment')
    );
    $this->assertEquals(
      'sample<sample/><!--comment-->', $control->getXml()
    );
  }
}

class PapayaParserTag_TestProxy extends PapayaParserTag {
  public $nodeStub = array();

  public function appendTo(PapayaXmlElement $parent) {
    foreach ($this->nodeStub as $node) {
      $parent->appendChild(
        $parent->ownerDocument->importNode($node)
      );
    }
  }
}