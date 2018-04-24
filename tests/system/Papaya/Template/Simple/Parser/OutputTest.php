<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaTemplateSimpleParserOutputTest extends PapayaTestCase {

  /**
   * @covers PapayaTemplateSimpleParserOutput::parse
   */
  public function testWithText() {
    $tokens = array(
      new PapayaTemplateSimpleScannerToken(PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo')
    );
    $parser = new PapayaTemplateSimpleParserOutput($tokens);
    $this->assertEquals(
      new PapayaTemplateSimpleAstNodes(
        array(
          new PapayaTemplateSimpleAstNodeOutput('foo')
        )
      ),
      $parser->parse()
    );
  }

  /**
   * @covers PapayaTemplateSimpleParserOutput::parse
   */
  public function testWithWhitespace() {
    $tokens = array(
      new PapayaTemplateSimpleScannerToken(PapayaTemplateSimpleScannerToken::WHITESPACE, 0, "\n")
    );
    $parser = new PapayaTemplateSimpleParserOutput($tokens);
    $this->assertEquals(
      new PapayaTemplateSimpleAstNodes(
        array(
          new PapayaTemplateSimpleAstNodeOutput("\n")
        )
      ),
      $parser->parse()
    );
  }

  /**
   * @covers PapayaTemplateSimpleParserOutput::parse
   */
  public function testWithSeveralOutputTokensOptimizesAst() {
    $tokens = array(
      new PapayaTemplateSimpleScannerToken(PapayaTemplateSimpleScannerToken::TEXT, 0, "foo"),
      new PapayaTemplateSimpleScannerToken(PapayaTemplateSimpleScannerToken::WHITESPACE, 3, "\n"),
      new PapayaTemplateSimpleScannerToken(PapayaTemplateSimpleScannerToken::TEXT, 4, "bar"),
    );
    $parser = new PapayaTemplateSimpleParserOutput($tokens);
    $this->assertEquals(
      new PapayaTemplateSimpleAstNodes(
        array(
          new PapayaTemplateSimpleAstNodeOutput("foo\nbar")
        )
      ),
      $parser->parse()
    );
  }

  /**
   * @covers PapayaTemplateSimpleParserOutput::parse
   */
  public function testWithValue() {
    $tokens = array(
      new PapayaTemplateSimpleScannerToken(
        PapayaTemplateSimpleScannerToken::VALUE_NAME, 0, '/*$foo*/'
      ),
      new PapayaTemplateSimpleScannerToken(
        PapayaTemplateSimpleScannerToken::WHITESPACE, 6, " "
      ),
      new PapayaTemplateSimpleScannerToken(
        PapayaTemplateSimpleScannerToken::VALUE_DEFAULT, 7, 'bar'
      )
    );
    $parser = new PapayaTemplateSimpleParserOutput($tokens);
    $this->assertEquals(
      new PapayaTemplateSimpleAstNodes(
        array(
          new PapayaTemplateSimpleAstNodeValue('foo', 'bar')
        )
      ),
      $parser->parse()
    );
  }
}
