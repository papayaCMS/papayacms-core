<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaEmailPartTest extends PapayaTestCase {

  /**
  * @covers PapayaEmailPart::__construct
  * @covers PapayaEmailPart::content
  */
  public function testConstructor() {
    $content = $this->createMock(PapayaEmailContent::class);
    $part = new PapayaEmailPart($content);
    $this->assertAttributeSame(
      $content, '_content', $part
    );
  }

  /**
  * @covers PapayaEmailPart::content
  */
  public function testContentGetter() {
    $content = $this->createMock(PapayaEmailContent::class);
    $part = new PapayaEmailPart($content);
    $this->assertSame(
      $content, $part->content()
    );
  }

  /**
  * @covers PapayaEmailPart::headers
  */
  public function testHeadersGetAfterSet() {
    $part = new PapayaEmailPart($this->createMock(PapayaEmailContent::class));
    $headers = $this->createMock(PapayaEmailHeaders::class);
    $this->assertSame(
      $headers, $part->headers($headers)
    );
  }

  /**
  * @covers PapayaEmailPart::headers
  */
  public function testHeadersGetImplicitCreate() {
    $part = new PapayaEmailPart($this->createMock(PapayaEmailContent::class));
    $this->assertInstanceOf(
      PapayaEmailHeaders::class, $part->headers()
    );
  }

  /**
  * @covers PapayaEmailPart::__get
  */
  public function testMagicMethodGetForPropertyContent() {
    $content = $this->createMock(PapayaEmailContent::class);
    $part = new PapayaEmailPart($content);
    $this->assertSame(
      $content, $part->content
    );
  }

  /**
  * @covers PapayaEmailPart::__get
  */
  public function testMagicMethodGetForPropertyHeaders() {
    $part = new PapayaEmailPart($this->createMock(PapayaEmailContent::class));
    $this->assertInstanceOf(
      PapayaEmailHeaders::class, $part->headers
    );
  }

  /**
  * @covers PapayaEmailPart::__get
  */
  public function testMagicMethodGetWithInvalidPropertyExpectingException() {
    $part = new PapayaEmailPart($this->createMock(PapayaEmailContent::class));
    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('LogicException: Unknown property "PapayaEmailPart::$invalidProperty".');
    $dummy = $part->invalidProperty;
  }

  /**
  * @covers PapayaEmailPart::__set
  */
  public function testMagicMethodSetForPropertyContent() {
    $part = new PapayaEmailPart($this->createMock(PapayaEmailContent::class));
    $content = $this->createMock(PapayaEmailContent::class);
    $part->content = $content;
    $this->assertAttributeSame(
      $content, '_content', $part
    );
  }

  /**
  * @covers PapayaEmailPart::__set
  */
  public function testMagicMethodSetForPropertyHeaders() {
    $part = new PapayaEmailPart($this->createMock(PapayaEmailContent::class));
    $headers = $this->createMock(PapayaEmailHeaders::class);
    $part->headers = $headers;
    $this->assertAttributeSame(
      $headers, '_headers', $part
    );
  }

  /**
  * @covers PapayaEmailPart::__set
  */
  public function testMagicMethodSetWithInvalidPropertyExpectingException() {
    $part = new PapayaEmailPart($this->createMock(PapayaEmailContent::class));
    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('LogicException: Unknown property "PapayaEmailPart::$invalidProperty".');
    $part->invalidProperty = 'test';
  }
}
