<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaTemplateSimpleScannerStatusCssTest extends PapayaTestCase {

  /**
   * @covers PapayaTemplateSimpleScannerStatusCss::getToken
   * @dataProvider provideValidTokenData
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
