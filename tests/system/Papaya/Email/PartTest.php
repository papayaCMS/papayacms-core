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

use Papaya\Email\Content;
use Papaya\Email\Headers;

require_once __DIR__.'/../../../bootstrap.php';

class PapayaEmailPartTest extends PapayaTestCase {

  /**
  * @covers \Papaya\Email\Part::__construct
  * @covers \Papaya\Email\Part::content
  */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Email\Content $content */
    $content = $this->createMock(Content::class);
    $part = new \Papaya\Email\Part($content);
    $this->assertAttributeSame(
      $content, '_content', $part
    );
  }

  /**
  * @covers \Papaya\Email\Part::content
  */
  public function testContentGetter() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Email\Content $content */
    $content = $this->createMock(Content::class);
    $part = new \Papaya\Email\Part($content);
    $this->assertSame(
      $content, $part->content()
    );
  }

  /**
  * @covers \Papaya\Email\Part::headers
  */
  public function testHeadersGetAfterSet() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Email\Content $content */
    $content = $this->createMock(Content::class);
    $part = new \Papaya\Email\Part($content);
    $headers = $this->createMock(Headers::class);
    $this->assertSame(
      $headers, $part->headers($headers)
    );
  }

  /**
  * @covers \Papaya\Email\Part::headers
  */
  public function testHeadersGetImplicitCreate() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Email\Content $content */
    $content = $this->createMock(Content::class);
    $part = new \Papaya\Email\Part($content);
    $this->assertInstanceOf(
      \Papaya\Email\Headers::class, $part->headers()
    );
  }

  /**
  * @covers \Papaya\Email\Part::__get
  */
  public function testMagicMethodGetForPropertyContent() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Email\Content $content */
    $content = $this->createMock(Content::class);
    $part = new \Papaya\Email\Part($content);
    $this->assertSame(
      $content, $part->content
    );
  }

  /**
  * @covers \Papaya\Email\Part::__get
  */
  public function testMagicMethodGetForPropertyHeaders() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Email\Content $content */
    $content = $this->createMock(Content::class);
    $part = new \Papaya\Email\Part($content);
    $this->assertInstanceOf(
      \Papaya\Email\Headers::class, $part->headers
    );
  }

  /**
  * @covers \Papaya\Email\Part::__get
  */
  public function testMagicMethodGetWithInvalidPropertyExpectingException() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Email\Content $content */
    $content = $this->createMock(Content::class);
    $part = new \Papaya\Email\Part($content);
    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('LogicException: Unknown property "Papaya\Email\Part::$invalidProperty".');
    /** @noinspection PhpUndefinedFieldInspection */
    $part->invalidProperty;
  }

  /**
  * @covers \Papaya\Email\Part::__set
  */
  public function testMagicMethodSetForPropertyContent() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Email\Content $content */
    $content = $this->createMock(Content::class);
    $part = new \Papaya\Email\Part($content);
    $content = $this->createMock(Content::class);
    $part->content = $content;
    $this->assertAttributeSame(
      $content, '_content', $part
    );
  }

  /**
  * @covers \Papaya\Email\Part::__set
  */
  public function testMagicMethodSetForPropertyHeaders() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Email\Content $content */
    $content = $this->createMock(Content::class);
    $part = new \Papaya\Email\Part($content);
    $headers = $this->createMock(Headers::class);
    $part->headers = $headers;
    $this->assertAttributeSame(
      $headers, '_headers', $part
    );
  }

  /**
  * @covers \Papaya\Email\Part::__set
  */
  public function testMagicMethodSetWithInvalidPropertyExpectingException() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Email\Content $content */
    $content = $this->createMock(Content::class);
    $part = new \Papaya\Email\Part($content);
    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('LogicException: Unknown property "Papaya\Email\Part::$invalidProperty".');
    /** @noinspection PhpUndefinedFieldInspection */
    $part->invalidProperty = 'test';
  }
}
