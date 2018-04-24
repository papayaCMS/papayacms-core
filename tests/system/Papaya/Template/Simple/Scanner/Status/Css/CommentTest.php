<?php
require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaTemplateSimpleScannerStatusCssCommentTest extends PapayaTestCase {

  /**
   * @covers PapayaTemplateSimpleScannerStatusCssComment::getToken
   * @dataProvider provideValidTokenData
   */
  public function testGetToken($expected, $buffer, $offset) {
    $status = new PapayaTemplateSimpleScannerStatusCssComment();
    $token = $status->getToken($buffer, $offset);
    $this->assertEquals($expected, (string)$token);
  }

  /**
   * @covers PapayaTemplateSimpleScannerStatusCssComment::isEndToken
   */
  public function testIsEndTokenExpectingTrue() {
    $token = new PapayaTemplateSimpleScannerToken(
      PapayaTemplateSimpleScannerToken::COMMENT_END, 0, ''
    );
    $status = new PapayaTemplateSimpleScannerStatusCssComment();
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
