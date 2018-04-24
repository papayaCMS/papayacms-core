<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaTemplateSimpleScannerStatusTest extends PapayaTestCase {

  /**
   * @covers PapayaTemplateSimpleScannerStatus::matchPattern
   */
  public function testMatchPatternExpectingMatchedToken() {
    $status = new PapayaTemplateSimpleScannerStatus_TestProxy();
    $result = $status->matchPattern('foobar', 3, '(bar)');
    $this->assertSame('bar', $result);
  }

  /**
   * @covers PapayaTemplateSimpleScannerStatus::matchPattern
   */
  public function testMatchPatternExpectingNull() {
    $status = new PapayaTemplateSimpleScannerStatus_TestProxy();
    $result = $status->matchPattern('foobar', 0, '(bar)');
    $this->assertNull($result);
  }

  /**
   * @covers PapayaTemplateSimpleScannerStatus::matchPatterns
   */
  public function testMatchPatternsExpectingSecondPatternMatches() {
    $status = new PapayaTemplateSimpleScannerStatus_TestProxy();
    $result = $status->matchPatterns(
      'foobar',
      0,
      array(
        '(bar)' => PapayaTemplateSimpleScannerToken::TEXT,
        '(foo)' => PapayaTemplateSimpleScannerToken::VALUE_NAME
      )
    );
    $this->assertEquals(
      new PapayaTemplateSimpleScannerToken(
        PapayaTemplateSimpleScannerToken::VALUE_NAME,
        0,
        'foo'
      ),
      $result
    );
  }

  /**
   * @covers PapayaTemplateSimpleScannerStatus::matchPatterns
   */
  public function testMatchPatternsExpectingNull() {
    $status = new PapayaTemplateSimpleScannerStatus_TestProxy();
    $result = $status->matchPatterns(
      'foobar', 0, array()
    );
    $this->assertNull($result);
  }

  /**
   * @covers PapayaTemplateSimpleScannerStatus::isEndToken
   */
  public function testIsEndTokenExpectingFalse() {
    $token = $this
      ->getMockBuilder('PapayaTemplateSimpleScannerToken')
      ->disableOriginalConstructor()
      ->getMock();
    $status = new PapayaTemplateSimpleScannerStatus_TestProxy();
    $this->assertFalse($status->isEndToken($token));
  }

  /**
   * @covers PapayaTemplateSimpleScannerStatus::getNewStatus
   */
  public function testGetNewStatusExpectungNull() {
    $token = $this
      ->getMockBuilder('PapayaTemplateSimpleScannerToken')
      ->disableOriginalConstructor()
      ->getMock();
    $status = new PapayaTemplateSimpleScannerStatus_TestProxy();
    $this->assertNull($status->getNewStatus($token));
  }
}

class PapayaTemplateSimpleScannerStatus_TestProxy extends PapayaTemplateSimpleScannerStatus {

  public function getToken($buffer, $offset) {
  }

  public function matchPattern($buffer, $offset, $pattern) {
    return parent::matchPattern($buffer, $offset, $pattern);
  }

  public function matchPatterns($buffer, $offset, $patterns) {
    return parent::matchPatterns($buffer, $offset, $patterns);
  }
}
