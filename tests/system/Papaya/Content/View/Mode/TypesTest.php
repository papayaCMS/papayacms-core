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

namespace Papaya\Content\View\Mode;

require_once __DIR__.'/../../../../../bootstrap.php';

class TypesTest extends \PapayaTestCase {

  /**
   * @covers Types::exists
   */
  public function testExistsExpectingTrue() {
    $this->assertTrue(Types::exists(Types::PAGE));
  }

  /**
   * @covers Types::exists
   */
  public function testExistsExpectingFalse() {
    $this->assertFalse(Types::exists(-23));
  }

  /**
   * @covers Types::offsetExists
   */
  public function testArrayAccessExistsExpectingTrue() {
    $types = new Types();
    $this->assertTrue(isset($types[Types::PAGE]));
  }

  /**
   * @covers Types::offsetExists
   */
  public function testArrayAccessExistsExpectingFalse() {
    $types = new Types();
    $this->assertFalse(isset($types[-23]));
  }

  /**
   * @covers Types::offsetGet
   */
  public function testArrayAccessGet() {
    $types = new Types();
    $this->assertEquals('Feed', $types[Types::FEED]);
  }

  /**
   * @covers Types::offsetGet
   */
  public function testArrayAccessGetwithInvalidType() {
    $types = new Types();
    $this->assertEquals('Page', $types[-23]);
  }

  /**
   * @covers Types::offsetSet
   */
  public function testArrayAccessBlockedSet() {
    $types = new Types();
    $this->expectException(\LogicException::class);
    $types[Types::FEED] = 'invalid';
  }

  /**
   * @covers Types::offsetUnset
   */
  public function testArrayAccessBlockedUnset() {
    $types = new Types();
    $this->expectException(\LogicException::class);
    unset($types[Types::FEED]);
  }

  /**
   * @covers Types::getIterator
   */
  public function testIterator() {
    $types = new Types();
    $this->assertEquals(
      array(
        Types::PAGE => 'Page',
        Types::FEED => 'Feed',
        Types::HIDDEN => 'Hidden'
      ),
      iterator_to_array($types)
    );
  }
}
