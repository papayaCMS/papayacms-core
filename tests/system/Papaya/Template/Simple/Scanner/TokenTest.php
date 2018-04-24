<?php
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
  public function testConstructorWithInvalidTypeExpectignException() {
    $this->setExpectedException(InvalidArgumentException::class);
    $token = new PapayaTemplateSimpleScannerToken(-23, 0, '');
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
    $this->setExpectedException(
      'LogicException', 'Unknown property: PapayaTemplateSimpleScannerToken::$UNKNOWN'
    );
    $token->UNKNOWN;
  }

  /**
   * @covers PapayaTemplateSimpleScannerToken::__set
   */
  public function testPropertyWriteThrowsException() {
    $token = new PapayaTemplateSimpleScannerToken(
      PapayaTemplateSimpleScannerToken::VALUE_NAME, 0, ''
    );
    $this->setExpectedException(LogicException::class);
    $token->offset = 23;
  }
}
