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

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaTemplateSimpleExceptionUnexpectedTokenTest extends PapayaTestCase {

  /**
  * @covers \PapayaTemplateSimpleExceptionUnexpectedToken::__construct
  */
  public function testConstructor() {
    $expectedToken = new \PapayaTemplateSimpleScannerToken(
      PapayaTemplateSimpleScannerToken::TEXT, 42, 'sample'
    );
    $e = new \PapayaTemplateSimpleExceptionUnexpectedToken(
      $expectedToken, array(PapayaTemplateSimpleScannerToken::VALUE_NAME)
    );
    $this->assertAttributeEquals(
      $expectedToken, 'encounteredToken', $e
    );
    $this->assertAttributeEquals(
      array(PapayaTemplateSimpleScannerToken::VALUE_NAME), 'expectedTokens', $e
    );
    $this->assertEquals(
      'Parse error: Found TEXT@42: "sample" while one of VALUE_NAME was expected.',
      $e->getMessage()
    );
  }
}
