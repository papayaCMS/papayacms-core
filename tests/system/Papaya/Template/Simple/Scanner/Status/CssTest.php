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

class PapayaTemplateSimpleScannerStatusCssTest extends PapayaTestCase {

  /**
   * @covers PapayaTemplateSimpleScannerStatusCss::getToken
   * @dataProvider provideValidTokenData
   * @param string $expected
   * @param string $buffer
   * @param int $offset
   */
  public function testGetToken($expected, $buffer, $offset) {
    $status = new PapayaTemplateSimpleScannerStatusCss();
    $token = $status->getToken($buffer, $offset);
    $this->assertEquals($expected, (string)$token);
  }

  /**
   * @covers PapayaTemplateSimpleScannerStatusCss::getToken
   */
  public function testGetTokenExpectingNull() {
    $status = new PapayaTemplateSimpleScannerStatusCss();
    $this->assertNull($status->getToken('', 0));
  }

  /**
   * @covers PapayaTemplateSimpleScannerStatusCss::getNewStatus
   */
  public function testGetNewStatusExpectingValueStatus() {
    $token = new PapayaTemplateSimpleScannerToken(
      PapayaTemplateSimpleScannerToken::VALUE_NAME, 0, ''
    );
    $status = new PapayaTemplateSimpleScannerStatusCss();
    $this->assertInstanceOf(
      PapayaTemplateSimpleScannerStatusCssValue::class,
      $status->getNewStatus($token)
    );
  }

  /**
   * @covers PapayaTemplateSimpleScannerStatusCss::getNewStatus
   */
  public function testGetNewStatusExpectingCommentStatus() {
    $token = new PapayaTemplateSimpleScannerToken(
      PapayaTemplateSimpleScannerToken::COMMENT_START, 0, ''
    );
    $status = new PapayaTemplateSimpleScannerStatusCss();
    $this->assertInstanceOf(
      PapayaTemplateSimpleScannerStatusCssComment::class,
      $status->getNewStatus($token)
    );
  }

  /**
   * @covers PapayaTemplateSimpleScannerStatusCss::getNewStatus
   */
  public function testGetNewStatusExpectingNull() {
    $token = new PapayaTemplateSimpleScannerToken(
      PapayaTemplateSimpleScannerToken::TEXT, 0, ''
    );
    $status = new PapayaTemplateSimpleScannerStatusCss();
    $this->assertNull(
      $status->getNewStatus($token)
    );
  }

  /**************************
   * Data Provider
   *************************/

  public static function provideValidTokenData() {
    return array(
      array('VALUE_NAME@0: "/*$foo*/"', '/*$foo*/', 0),
      array('WHITESPACE@7: " "', 'margin: /*$foo*/ 2px', 7),
      array('VALUE_NAME@8: "/*$foo*/"', 'margin: /*$foo*/ 2px', 8),
      array('TEXT@0: "margin:"', 'margin: /*$foo*/ 2px', 0),
      array('TEXT@3: "margin:"', '   margin: /*$foo*/ 2px', 3),
      array('TEXT@0: "0"', '0 0 1em 2em;', 0),
      array('COMMENT_START@0: "/*"', '/* comment */', 0),
      array('COMMENT_START@0: "/*"', '/*', 0),
      array('TEXT@0: "/"', '/', 0)
    );
  }
}
