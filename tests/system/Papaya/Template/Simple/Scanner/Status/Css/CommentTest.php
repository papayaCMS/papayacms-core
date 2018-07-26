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

require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaTemplateSimpleScannerStatusCssCommentTest extends \PapayaTestCase {

  /**
   * @covers \PapayaTemplateSimpleScannerStatusCssComment::getToken
   * @dataProvider provideValidTokenData
   * @param string $expected
   * @param string $buffer
   * @param int $offset
   */
  public function testGetToken($expected, $buffer, $offset) {
    $status = new \PapayaTemplateSimpleScannerStatusCssComment();
    $token = $status->getToken($buffer, $offset);
    $this->assertEquals($expected, (string)$token);
  }

  /**
   * @covers \PapayaTemplateSimpleScannerStatusCssComment::isEndToken
   */
  public function testIsEndTokenExpectingTrue() {
    $token = new \PapayaTemplateSimpleScannerToken(
      \PapayaTemplateSimpleScannerToken::COMMENT_END, 0, ''
    );
    $status = new \PapayaTemplateSimpleScannerStatusCssComment();
    $this->assertTrue($status->isEndToken($token));
  }

  /**************************
   * Data Provider
   *************************/

  public static function provideValidTokenData() {
    return array(
      array('TEXT@2: " foo "', '/* foo */', 2),
      array('COMMENT_END@7: "*/"', '/* foo */', 7),
      array('TEXT@0: " foo "', ' foo */', 0),
      array('COMMENT_END@0: "*/"', '*/', 0),
      array('TEXT@0: "/* foo "', '/* foo */', 0)
    );
  }
}
