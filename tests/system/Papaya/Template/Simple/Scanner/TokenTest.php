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

class PapayaTemplateSimpleScannerTokenTest extends PapayaTestCase {

  /**
   * @covers PapayaTemplateSimpleScannerToken::__construct
   * @covers PapayaTemplateSimpleScannerToken::__toString
   */
  public function testConstructorAndStringCasting() {
    $token = new PapayaTemplateSimpleScannerToken(
      PapayaTemplateSimpleScannerToken::TEXT,
      21,
      'foo'
    );
    $this->assertEquals(
      'TEXT@21: "foo"',
      (string)$token
    );
  }

  /**
   * @covers PapayaTemplateSimpleScannerToken::__construct
   */
  public function testConstructorWithInvalidTypeExpectingException() {
    $this->expectException(InvalidArgumentException::class);
    new PapayaTemplateSimpleScannerToken(-23, 0, '');
  }

  /**
   * @covers PapayaTemplateSimpleScannerToken::getTypeString
   * @covers PapayaTemplateSimpleScannerToken::getTokenTypes
   */
  public function testGetTypeStringExpectingText() {
    $this->assertEquals(
      'TEXT',
      PapayaTemplateSimpleScannerToken::getTypeString(PapayaTemplateSimpleScannerToken::TEXT)
    );
  }

  /**
   * @covers PapayaTemplateSimpleScannerToken::getTypeString
   */
  public function testGetTypeStringwithInvalidTokenTypeExpectingNull() {
    $this->assertNull(
      PapayaTemplateSimpleScannerToken::getTypeString(-23)
    );
  }

  /**
   * @covers PapayaTemplateSimpleScannerToken::__get
   */
  public function testPropertyReadType() {
    $token = new PapayaTemplateSimpleScannerToken(
      PapayaTemplateSimpleScannerToken::VALUE_NAME, 0, ''
    );
    $this->assertEquals(PapayaTemplateSimpleScannerToken::VALUE_NAME, $token->type);
  }

  /**
   * @covers PapayaTemplateSimpleScannerToken::__get
   */
  public function testPropertyReadOffset() {
    $token = new PapayaTemplateSimpleScannerToken(
      PapayaTemplateSimpleScannerToken::VALUE_NAME, 42, ''
    );
    $this->assertEquals(42, $token->offset);
  }

  /**
   * @covers PapayaTemplateSimpleScannerToken::__get
   */
  public function testPropertyReadContent() {
    $token = new PapayaTemplateSimpleScannerToken(
      PapayaTemplateSimpleScannerToken::VALUE_NAME, 0, 'foo'
    );
    $this->assertEquals('foo', $token->content);
  }

  /**
   * @covers PapayaTemplateSimpleScannerToken::__get
   */
  public function testPropertyReadLength() {
    $token = new PapayaTemplateSimpleScannerToken(
      PapayaTemplateSimpleScannerToken::VALUE_NAME, 0, 'foo'
    );
    $this->assertEquals(3, $token->length);
  }

  /**
   * @covers PapayaTemplateSimpleScannerToken::__get
   */
  public function testPropertyReadUnkownPropertyExpectingException() {
    $token = new PapayaTemplateSimpleScannerToken(
      PapayaTemplateSimpleScannerToken::VALUE_NAME, 0, 'foo'
    );
    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('Unknown property: PapayaTemplateSimpleScannerToken::$UNKNOWN');
    /** @noinspection PhpUndefinedFieldInspection */
    $token->UNKNOWN;
  }

  /**
   * @covers PapayaTemplateSimpleScannerToken::__set
   */
  public function testPropertyWriteThrowsException() {
    $token = new PapayaTemplateSimpleScannerToken(
      PapayaTemplateSimpleScannerToken::VALUE_NAME, 0, ''
    );
    $this->expectException(LogicException::class);
    /** @noinspection Annotator */
    $token->offset = 23;
  }
}
