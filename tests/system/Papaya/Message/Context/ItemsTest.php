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

class ItemsTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Message\Context\Items::__construct
   */
  public function testConstructor() {
    $context = new Items('List Sample', array('Hello', 'World'));
    $this->assertAttributeEquals(
      'List Sample', '_label', $context
    );
    $this->assertAttributeEquals(
      array('Hello', 'World'), '_items', $context
    );
  }

  /**
   * @covers \Papaya\Message\Context\Items::getLabel
   */
  public function testGetLabel() {
    $context = new Items('List Sample', array('Hello', 'World'));
    $this->assertEquals(
      'List Sample', $context->getLabel()
    );
  }

  /**
   * @covers \Papaya\Message\Context\Items::asArray
   */
  public function testAsArray() {
    $context = new Items('', array('Hello', 'World'));
    $this->assertEquals(
      array('Hello', 'World'),
      $context->asArray()
    );
  }

  /**
   * @covers \Papaya\Message\Context\Items::asXhtml
   */
  public function testAsXhtml() {
    $context = new Items('', array('Hello', 'World'));
    $this->assertEquals(
      '<ol><li>Hello</li><li>World</li></ol>',
      $context->asXhtml()
    );
  }

  /**
   * @covers \Papaya\Message\Context\Items::asXhtml
   */
  public function testAsXhtmlWithEmptyList() {
    $context = new Items('', array());
    $this->assertEquals(
      '',
      $context->asXhtml()
    );
  }

  /**
   * @covers \Papaya\Message\Context\Items::asString
   */
  public function testAsString() {
    $context = new Items('', array('Hello', 'World'));
    $this->assertEquals(
      'Hello'."\n".'World',
      $context->asString()
    );
  }
}
