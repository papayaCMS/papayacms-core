<?php
require_once(dirname(__FILE__).'/../../../../../../../bootstrap.php');

class PapayaTemplateSimpleScannerStatusCssValueTest extends PapayaTestCase {

  /**
   * @covers PapayaTemplateSimpleScannerStatusCssValue::getToken
   * @dataProvider provideValidTokenData
   */
  public function testGetToken($expected, $buffer, $offset) {
    $status = new PapayaTemplateSimpleScannerStatusCssValue();
    $token = $status->getToken($buffer, $offset);
    $this->assertEquals($expected, (string)$token);
  }

  /**
   * @covers PapayaTemplateSimpleScannerStatusCssValue::isEndToken
   */
  public function testIsEndTokenExpectingTrue() {
    $token = new PapayaTemplateSimpleScannerToken(
      PapayaTemplateSimpleScannerToken::VALUE_DEFAULT, 0, ''
    );
    $status = new PapayaTemplateSimpleScannerStatusCssValue();
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