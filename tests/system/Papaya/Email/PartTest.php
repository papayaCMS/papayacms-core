<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaEmailPartTest extends PapayaTestCase {

  /**
  * @covers PapayaEmailPart::__construct
  * @covers PapayaEmailPart::content
  */
  public function testConstructor() {
    $content = $this->getMock('PapayaEmailContent');
    $part = new PapayaEmailPart($content);
    $this->assertAttributeSame(
      $content, '_content', $part
    );
  }

  /**
  * @covers PapayaEmailPart::content
  */
  public function testContentGetter() {
    $content = $this->getMock('PapayaEmailContent');
    $part = new PapayaEmailPart($content);
    $this->assertSame(
      $content, $part->content()
    );
  }

  /**
  * @covers PapayaEmailPart::headers
  */
  public function testHeadersGetAfterSet() {
    $part = new PapayaEmailPart($this->getMock('PapayaEmailContent'));
    $headers = $this->getMock('PapayaEmailHeaders');
    $this->assertSame(
      $headers, $part->headers($headers)
    );
  }

  /**
  * @covers PapayaEmailPart::headers
  */
  public function testHeadersGetImplicitCreate() {
    $part = new PapayaEmailPart($this->getMock('PapayaEmailContent'));
    $this->assertInstanceOf(
      'PapayaEmailHeaders', $part->headers()
    );
  }

  /**
  * @covers PapayaEmailPart::__get
  */
  public function testMagicMethodGetForPropertyContent() {
    $content = $this->getMock('PapayaEmailContent');
    $part = new PapayaEmailPart($content);
    $this->assertSame(
      $content, $part->content
    );
  }

  /**
  * @covers PapayaEmailPart::__get
  */
  public function testMagicMethodGetForPropertyHeaders() {
    $part = new PapayaEmailPart($this->getMock('PapayaEmailContent'));
    $this->assertInstanceOf(
      'PapayaEmailHeaders', $part->headers
    );
  }

  /**
  * @covers PapayaEmailPart::__get
  */
  public function testMagicMethodGetWithInvalidPropertyExpectingException() {
    $part = new PapayaEmailPart($this->getMock('PapayaEmailContent'));
    $this->setExpectedException(
      'LogicException',
      'LogicException: Unknown property "PapayaEmailPart::$invalidProperty".'
    );
    $dummy = $part->invalidProperty;
  }

  /**
  * @covers PapayaEmailPart::__set
  */
  public function testMagicMethodSetForPropertyContent() {
    $part = new PapayaEmailPart($this->getMock('PapayaEmailContent'));
    $content = $this->getMock('PapayaEmailContent');
    $part->content = $content;
    $this->assertAttributeSame(
      $content, '_content', $part
    );
  }

  /**
  * @covers PapayaEmailPart::__set
  */
  public function testMagicMethodSetForPropertyHeaders() {
    $part = new PapayaEmailPart($this->getMock('PapayaEmailContent'));
    $headers = $this->getMock('PapayaEmailHeaders');
    $part->headers = $headers;
    $this->assertAttributeSame(
      $headers, '_headers', $part
    );
  }

  /**
  * @covers PapayaEmailPart::__set
  */
  public function testMagicMethodSetWithInvalidPropertyExpectingException() {
    $part = new PapayaEmailPart($this->getMock('PapayaEmailContent'));
    $this->setExpectedException(
      'LogicException',
      'LogicException: Unknown property "PapayaEmailPart::$invalidProperty".'
    );
    $part->invalidProperty = 'test';
  }
}
