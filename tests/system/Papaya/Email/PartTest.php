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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaEmailPartTest extends PapayaTestCase {

  /**
  * @covers \PapayaEmailPart::__construct
  * @covers \PapayaEmailPart::content
  */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaEmailContent $content */
    $content = $this->createMock(PapayaEmailContent::class);
    $part = new \PapayaEmailPart($content);
    $this->assertAttributeSame(
      $content, '_content', $part
    );
  }

  /**
  * @covers \PapayaEmailPart::content
  */
  public function testContentGetter() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaEmailContent $content */
    $content = $this->createMock(PapayaEmailContent::class);
    $part = new \PapayaEmailPart($content);
    $this->assertSame(
      $content, $part->content()
    );
  }

  /**
  * @covers \PapayaEmailPart::headers
  */
  public function testHeadersGetAfterSet() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaEmailContent $content */
    $content = $this->createMock(PapayaEmailContent::class);
    $part = new \PapayaEmailPart($content);
    $headers = $this->createMock(PapayaEmailHeaders::class);
    $this->assertSame(
      $headers, $part->headers($headers)
    );
  }

  /**
  * @covers \PapayaEmailPart::headers
  */
  public function testHeadersGetImplicitCreate() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaEmailContent $content */
    $content = $this->createMock(PapayaEmailContent::class);
    $part = new \PapayaEmailPart($content);
    $this->assertInstanceOf(
      PapayaEmailHeaders::class, $part->headers()
    );
  }

  /**
  * @covers \PapayaEmailPart::__get
  */
  public function testMagicMethodGetForPropertyContent() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaEmailContent $content */
    $content = $this->createMock(PapayaEmailContent::class);
    $part = new \PapayaEmailPart($content);
    $this->assertSame(
      $content, $part->content
    );
  }

  /**
  * @covers \PapayaEmailPart::__get
  */
  public function testMagicMethodGetForPropertyHeaders() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaEmailContent $content */
    $content = $this->createMock(PapayaEmailContent::class);
    $part = new \PapayaEmailPart($content);
    $this->assertInstanceOf(
      PapayaEmailHeaders::class, $part->headers
    );
  }

  /**
  * @covers \PapayaEmailPart::__get
  */
  public function testMagicMethodGetWithInvalidPropertyExpectingException() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaEmailContent $content */
    $content = $this->createMock(PapayaEmailContent::class);
    $part = new \PapayaEmailPart($content);
    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('LogicException: Unknown property "PapayaEmailPart::$invalidProperty".');
    /** @noinspection PhpUndefinedFieldInspection */
    $part->invalidProperty;
  }

  /**
  * @covers \PapayaEmailPart::__set
  */
  public function testMagicMethodSetForPropertyContent() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaEmailContent $content */
    $content = $this->createMock(PapayaEmailContent::class);
    $part = new \PapayaEmailPart($content);
    $content = $this->createMock(PapayaEmailContent::class);
    $part->content = $content;
    $this->assertAttributeSame(
      $content, '_content', $part
    );
  }

  /**
  * @covers \PapayaEmailPart::__set
  */
  public function testMagicMethodSetForPropertyHeaders() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaEmailContent $content */
    $content = $this->createMock(PapayaEmailContent::class);
    $part = new \PapayaEmailPart($content);
    $headers = $this->createMock(PapayaEmailHeaders::class);
    $part->headers = $headers;
    $this->assertAttributeSame(
      $headers, '_headers', $part
    );
  }

  /**
  * @covers \PapayaEmailPart::__set
  */
  public function testMagicMethodSetWithInvalidPropertyExpectingException() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaEmailContent $content */
    $content = $this->createMock(PapayaEmailContent::class);
    $part = new \PapayaEmailPart($content);
    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('LogicException: Unknown property "PapayaEmailPart::$invalidProperty".');
    /** @noinspection PhpUndefinedFieldInspection */
    $part->invalidProperty = 'test';
  }
}
