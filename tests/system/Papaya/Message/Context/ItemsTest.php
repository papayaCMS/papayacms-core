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
 * @covers \Papaya\Message\Context\Items
 */
class ItemsTest extends \Papaya\TestFramework\TestCase {

  public function testGetLabel() {
    $context = new Items('List Sample', array('Hello', 'World'));
    $this->assertEquals(
      'List Sample', $context->getLabel()
    );
  }

  public function testAsArray() {
    $context = new Items('', array('Hello', 'World'));
    $this->assertEquals(
      array('Hello', 'World'),
      $context->asArray()
    );
  }

  public function testAsXhtml() {
    $context = new Items('', array('Hello', 'World'));
    $this->assertEquals(
      '<ol><li>Hello</li><li>World</li></ol>',
      $context->asXhtml()
    );
  }

  public function testAsXhtmlWithEmptyList() {
    $context = new Items('', array());
    $this->assertEquals(
      '',
      $context->asXhtml()
    );
  }

  public function testAsString() {
    $context = new Items('', array('Hello', 'World'));
    $this->assertEquals(
      'Hello'."\n".'World',
      $context->asString()
    );
  }
}
