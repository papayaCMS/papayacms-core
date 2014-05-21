<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaRequestParserTest extends PapayaTestCase {

  /**
  * @covers PapayaRequestParser::isLast
  */
  public function testIsLast() {
    $parser = new PapayaRequestParser_TestProxy();
    $this->assertTrue($parser->isLast());
  }
}

class PapayaRequestParser_TestProxy extends PapayaRequestParser {
  public function parse($url) {

  }
}