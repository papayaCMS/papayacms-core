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

class PapayaMessageContextListTest extends \PapayaTestCase {

  /**
  * @covers \PapayaMessageContextList::__construct
  */
  public function testConstructor() {
    $context = new \PapayaMessageContextList('List Sample', array('Hello', 'World'));
    $this->assertAttributeEquals(
      'List Sample', '_label', $context
    );
    $this->assertAttributeEquals(
      array('Hello', 'World'), '_items', $context
    );
  }

  /**
  * @covers \PapayaMessageContextList::getLabel
  */
  public function testGetLabel() {
    $context = new \PapayaMessageContextList('List Sample', array('Hello', 'World'));
    $this->assertEquals(
      'List Sample', $context->getLabel()
    );
  }

  /**
  * @covers \PapayaMessageContextList::asArray
  */
  public function testAsArray() {
    $context = new \PapayaMessageContextList('', array('Hello', 'World'));
    $this->assertEquals(
      array('Hello', 'World'),
      $context->asArray()
    );
  }

  /**
  * @covers \PapayaMessageContextList::asXhtml
  */
  public function testAsXhtml() {
    $context = new \PapayaMessageContextList('', array('Hello', 'World'));
    $this->assertEquals(
      '<ol><li>Hello</li><li>World</li></ol>',
      $context->asXhtml()
    );
  }

  /**
  * @covers \PapayaMessageContextList::asXhtml
  */
  public function testAsXhtmlWithEmptyList() {
    $context = new \PapayaMessageContextList('', array());
    $this->assertEquals(
      '',
      $context->asXhtml()
    );
  }

  /**
  * @covers \PapayaMessageContextList::asString
  */
  public function testAsString() {
    $context = new \PapayaMessageContextList('', array('Hello', 'World'));
    $this->assertEquals(
      'Hello'."\n".'World',
      $context->asString()
    );
  }
}
