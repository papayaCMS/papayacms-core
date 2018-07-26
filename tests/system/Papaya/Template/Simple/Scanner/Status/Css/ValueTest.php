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

class PapayaTemplateSimpleScannerStatusCssValueTest extends PapayaTestCase {

  /**
   * @covers \PapayaTemplateSimpleScannerStatusCssValue::getToken
   * @dataProvider provideValidTokenData
   * @param string $expected
   * @param string $buffer
   * @param int $offset
   */
  public function testGetToken($expected, $buffer, $offset) {
    $status = new \PapayaTemplateSimpleScannerStatusCssValue();
    $token = $status->getToken($buffer, $offset);
    $this->assertEquals($expected, (string)$token);
  }

  /**
   * @covers \PapayaTemplateSimpleScannerStatusCssValue::isEndToken
   */
  public function testIsEndTokenExpectingTrue() {
    $token = new \PapayaTemplateSimpleScannerToken(
      \PapayaTemplateSimpleScannerToken::VALUE_DEFAULT, 0, ''
    );
    $status = new \PapayaTemplateSimpleScannerStatusCssValue();
    $this->assertTrue($status->isEndToken($token));
  }

  /**************************
   * Data Provider
   *************************/

  public static function provideValidTokenData() {
    return array(
      array('VALUE_DEFAULT@0: "2px"', '2px; ', 0),
      array('VALUE_DEFAULT@1: "2px"', ' 2px; ', 1),
      array('VALUE_DEFAULT@0: "4em"', '4em', 0),
      array('VALUE_DEFAULT@0: "none"', 'none', 0),
      array('WHITESPACE@0: " "', ' 2px ', 0),
      array('WHITESPACE@4: " "', ' 2px ', 4)
    );
  }
}
