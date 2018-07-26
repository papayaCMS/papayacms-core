<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaTemplateSimpleParserOutputTest extends PapayaTestCase {

  /**
   * @covers \PapayaTemplateSimpleParserOutput::parse
   */
  public function testWithText() {
    $tokens = array(
      new \PapayaTemplateSimpleScannerToken(PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo')
    );
    $parser = new \PapayaTemplateSimpleParserOutput($tokens);
    $this->assertEquals(
      new \PapayaTemplateSimpleAstNodes(
        array(
          new \PapayaTemplateSimpleAstNodeOutput('foo')
        )
      ),
      $parser->parse()
    );
  }

  /**
   * @covers \PapayaTemplateSimpleParserOutput::parse
   */
  public function testWithWhitespace() {
    $tokens = array(
      new \PapayaTemplateSimpleScannerToken(PapayaTemplateSimpleScannerToken::WHITESPACE, 0, "\n")
    );
    $parser = new \PapayaTemplateSimpleParserOutput($tokens);
    $this->assertEquals(
      new \PapayaTemplateSimpleAstNodes(
        array(
          new \PapayaTemplateSimpleAstNodeOutput("\n")
        )
      ),
      $parser->parse()
    );
  }

  /**
   * @covers \PapayaTemplateSimpleParserOutput::parse
   */
  public function testWithSeveralOutputTokensOptimizesAst() {
    $tokens = array(
      new \PapayaTemplateSimpleScannerToken(PapayaTemplateSimpleScannerToken::TEXT, 0, 'foo'),
      new \PapayaTemplateSimpleScannerToken(PapayaTemplateSimpleScannerToken::WHITESPACE, 3, "\n"),
      new \PapayaTemplateSimpleScannerToken(PapayaTemplateSimpleScannerToken::TEXT, 4, 'bar'),
    );
    $parser = new \PapayaTemplateSimpleParserOutput($tokens);
    $this->assertEquals(
      new \PapayaTemplateSimpleAstNodes(
        array(
          new \PapayaTemplateSimpleAstNodeOutput("foo\nbar")
        )
      ),
      $parser->parse()
    );
  }

  /**
   * @covers \PapayaTemplateSimpleParserOutput::parse
   */
  public function testWithValue() {
    $tokens = array(
      new \PapayaTemplateSimpleScannerToken(
        \PapayaTemplateSimpleScannerToken::VALUE_NAME, 0, '/*$foo*/'
      ),
      new \PapayaTemplateSimpleScannerToken(
        \PapayaTemplateSimpleScannerToken::WHITESPACE, 6, ' '
      ),
      new \PapayaTemplateSimpleScannerToken(
        \PapayaTemplateSimpleScannerToken::VALUE_DEFAULT, 7, 'bar'
      )
    );
    $parser = new \PapayaTemplateSimpleParserOutput($tokens);
    $this->assertEquals(
      new \PapayaTemplateSimpleAstNodes(
        array(
          new \PapayaTemplateSimpleAstNodeValue('foo', 'bar')
        )
      ),
      $parser->parse()
    );
  }
}
