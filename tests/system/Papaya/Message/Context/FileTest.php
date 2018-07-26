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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaMessageContextFileTest extends PapayaTestCase {

  /**
  * @covers \PapayaMessageContextFile::__construct
  */
  public function testConstructor() {
    $context = new \PapayaMessageContextFile(__FILE__);
    $this->assertAttributeEquals(
      __FILE__, '_fileName', $context
    );
    $this->assertAttributeEquals(
      0, '_line', $context
    );
    $this->assertAttributeEquals(
      0, '_column', $context
    );
  }

  /**
  * @covers \PapayaMessageContextFile::__construct
  */
  public function testConstructorWithPosition() {
    $context = new \PapayaMessageContextFile(__FILE__, 42, 21);
    $this->assertAttributeEquals(
      __FILE__, '_fileName', $context
    );
    $this->assertAttributeEquals(
      42, '_line', $context
    );
    $this->assertAttributeEquals(
      21, '_column', $context
    );
  }

  /**
  * @covers \PapayaMessageContextFile::getLabel
  */
  public function testGetLabel() {
    $context = new \PapayaMessageContextFile(__FILE__);
    $this->assertEquals(
      __FILE__,
      $context->getLabel()
    );
  }

  /**
  * @covers \PapayaMessageContextFile::getLabel
  */
  public function testGetLabelWithLine() {
    $context = new \PapayaMessageContextFile(__FILE__, 42);
    $this->assertEquals(
      __FILE__.':42',
      $context->getLabel()
    );
  }

  /**
  * @covers \PapayaMessageContextFile::getLabel
  */
  public function testGetLabelWithLineAndColumn() {
    $context = new \PapayaMessageContextFile(__FILE__, 42, 21);
    $this->assertEquals(
      __FILE__.':42:21',
      $context->getLabel()
    );
  }

  /**
  * @covers \PapayaMessageContextFile::readable
  */
  public function testReadable() {
    $context = new \PapayaMessageContextFile(__FILE__);
    $this->assertTrue(
      $context->readable(__FILE__)
    );
  }

  /**
  * @covers \PapayaMessageContextFile::readable
  */
  public function testReadableWithDirectory() {
    $context = new \PapayaMessageContextFile(__FILE__);
    $this->assertFalse(
      $context->readable(__DIR__)
    );
  }

  /**
  * @covers \PapayaMessageContextFile::readable
  */
  public function testReadableWithEmpty() {
    $context = new \PapayaMessageContextFile(__FILE__);
    $this->assertFalse(
      $context->readable('')
    );
  }

  /**
  * @covers \PapayaMessageContextFile::readable
  */
  public function testReadableWithNotExistingFile() {
    $context = new \PapayaMessageContextFile(__FILE__);
    $this->assertFalse(
      $context->readable(__FILE__.'does-not-exist.txt')
    );
  }

  /**
  * @covers \PapayaMessageContextFile::asString
  */
  public function testAsString() {
    $context = new \PapayaMessageContextFile(__DIR__.'/TestData/sample.txt');
    $this->assertEquals(
      "Line1\nLine2\nLine3",
      $context->asString()
    );
  }

  /**
  * @covers \PapayaMessageContextFile::asString
  */
  public function testAsStringWithNotExistingFile() {
    $context = new \PapayaMessageContextFile(__FILE__.'does-not-exist.txt');
    $this->assertEquals(
      '',
      $context->asString()
    );
  }

  /**
  * @covers \PapayaMessageContextFile::asArray
  */
  public function testAsArray() {
    $context = new \PapayaMessageContextFile(__DIR__.'/TestData/sample.txt');
    $this->assertEquals(
      array('Line1', 'Line2', 'Line3'),
      $context->asArray()
    );
  }

  /**
  * @covers \PapayaMessageContextFile::asArray
  */
  public function testAsArrayWithNotExistingFile() {
    $context = new \PapayaMessageContextFile(__FILE__.'does-not-exist.txt');
    $this->assertEquals(
      array(),
      $context->asArray()
    );
  }

  /**
  * @covers \PapayaMessageContextFile::asXhtml
  */
  public function testAsXhtml() {
    $context = new \PapayaMessageContextFile(__DIR__.'/TestData/sample.txt', 2, 3);
    $this->assertEquals(
      '<ol class="file" style="white-space: pre; font-family: monospace;">'.
      '<li style="list-style-position: outside;">Line1</li>'.
      '<li style="list-style-position: outside;"><strong>Li<em>ne2</em></strong></li>'.
      '<li style="list-style-position: outside;">Line3</li>'.
      '</ol>',
      $context->asXhtml()
    );
  }

  /**
  * @covers \PapayaMessageContextFile::asXhtml
  */
  public function testAsXhtmlWithNotExistingFile() {
    $context = new \PapayaMessageContextFile(__FILE__.'does-not-exist.txt', 2, 3);
    $this->assertEquals(
      '',
      $context->asXhtml()
    );
  }
}
