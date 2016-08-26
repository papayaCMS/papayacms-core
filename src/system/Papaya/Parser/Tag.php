<?php
abstract class PapayaParserTag implements PapayaXmlAppendable {
  /**
   * Compile output xml for the user interface element.
   * @return string
   */
  public function getXml() {
    $dom = new PapayaXmlDocument();
    $control = $dom->appendElement('tag');
    $this->appendTo($control);
    $xml = '';
    foreach ($dom->documentElement->childNodes as $node) {
      $xml .= $node->ownerDocument->saveXml($node);
    }
    return $xml;
  }
}
