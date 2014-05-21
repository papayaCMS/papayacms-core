<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaTemplateSimpleExceptionUnexpectedEofTest extends PapayaTestCase {

  /**
  * @covers PapayaTemplateSimpleExceptionUnexpectedEof::__construct
  */
  public function testConstructor() {
    $e = new PapayaTemplateSimpleExceptionUnexpectedEof(
      array(PapayaTemplateSimpleScannerToken::TEXT)
    );
    $this->assertAttributeEquals(
      array(PapayaTemplateSimpleScannerToken::TEXT), 'expectedTokens', $e
    );
    $this->assertEquals(
      'Parse error: Unexpected end of file was found while one of TEXT was expected.',
      $e->getMessage()
    );
  }
}