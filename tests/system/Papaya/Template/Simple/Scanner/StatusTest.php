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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplateSimpleScannerToken $token */
    $token = $this
      ->getMockBuilder(PapayaTemplateSimpleScannerToken::class)
      ->disableOriginalConstructor()
      ->getMock();
    $status = new PapayaTemplateSimpleScannerStatus_TestProxy();
    $this->assertFalse($status->isEndToken($token));
  }

  /**
   * @covers PapayaTemplateSimpleScannerStatus::getNewStatus
   */
  public function testGetNewStatusExpectingNull() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplateSimpleScannerToken $token */
    $token = $this
      ->getMockBuilder(PapayaTemplateSimpleScannerToken::class)
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
