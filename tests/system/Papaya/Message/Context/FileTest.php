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

namespace Papaya\Message\Context;
require_once __DIR__.'/../../../../bootstrap.php';

/**
 * @covers \Papaya\Message\Context\File
 */
class FileTest extends \Papaya\TestFramework\TestCase {

  public function testGetLabel() {
    $context = new File(__FILE__);
    $this->assertEquals(
      __FILE__,
      $context->getLabel()
    );
  }

  public function testGetLabelWithLine() {
    $context = new File(__FILE__, 42);
    $this->assertEquals(
      __FILE__.':42',
      $context->getLabel()
    );
  }

  public function testGetLabelWithLineAndColumn() {
    $context = new File(__FILE__, 42, 21);
    $this->assertEquals(
      __FILE__.':42:21',
      $context->getLabel()
    );
  }

  public function testReadable() {
    $context = new File(__FILE__);
    $this->assertTrue(
      $context->readable(__FILE__)
    );
  }

  public function testReadableWithDirectory() {
    $context = new File(__FILE__);
    $this->assertFalse(
      $context->readable(__DIR__)
    );
  }

  public function testReadableWithEmpty() {
    $context = new File(__FILE__);
    $this->assertFalse(
      $context->readable('')
    );
  }

  public function testReadableWithNotExistingFile() {
    $context = new File(__FILE__);
    $this->assertFalse(
      $context->readable(__FILE__.'does-not-exist.txt')
    );
  }

  public function testAsString() {
    $context = new File(__DIR__.'/TestData/sample.txt');
    $this->assertEquals(
      "Line1\nLine2\nLine3",
      $context->asString()
    );
  }

  public function testAsStringWithNotExistingFile() {
    $context = new File(__FILE__.'does-not-exist.txt');
    $this->assertEquals(
      '',
      $context->asString()
    );
  }

  public function testAsArray() {
    $context = new File(__DIR__.'/TestData/sample.txt');
    $this->assertEquals(
      array('Line1', 'Line2', 'Line3'),
      $context->asArray()
    );
  }

  public function testAsArrayWithNotExistingFile() {
    $context = new File(__FILE__.'does-not-exist.txt');
    $this->assertEquals(
      array(),
      $context->asArray()
    );
  }

  public function testAsXhtml() {
    $context = new File(__DIR__.'/TestData/sample.txt', 2, 3);
    $this->assertEquals(
      '<ol class="file" style="white-space: pre; font-family: monospace;">'.
      '<li style="list-style-position: outside;">Line1</li>'.
      '<li style="list-style-position: outside;"><strong>Li<em>ne2</em></strong></li>'.
      '<li style="list-style-position: outside;">Line3</li>'.
      '</ol>',
      $context->asXhtml()
    );
  }

  public function testAsXhtmlWithNotExistingFile() {
    $context = new File(__FILE__.'does-not-exist.txt', 2, 3);
    $this->assertEquals(
      '',
      $context->asXhtml()
    );
  }
}
