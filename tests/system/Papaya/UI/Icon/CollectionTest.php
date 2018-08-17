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

namespace Papaya\UI\Icon;
require_once __DIR__.'/../../../../bootstrap.php';

class CollectionTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Icon\Collection::offsetExists
   */
  public function testOffsetExists() {
    $list = new Collection();
    $list['sample'] = new \Papaya\UI\Icon('sample.png');
    $this->assertTrue(isset($list['sample']));
  }

  /**
   * @covers \Papaya\UI\Icon\Collection::offsetGet
   */
  public function testOffsetGet() {
    $list = new Collection();
    $list['sample'] = $icon = new \Papaya\UI\Icon('sample.png');
    $this->assertSame($icon, $list['sample']);
  }

  /**
   * @covers \Papaya\UI\Icon\Collection::offsetSet
   */
  public function testOffsetSet() {
    $list = new Collection();
    $list['sample'] = $icon = new \Papaya\UI\Icon('sample.png');
    $this->assertAttributeSame(array('sample' => $icon), '_icons', $list);
  }

  /**
   * @covers \Papaya\UI\Icon\Collection::offsetSet
   */
  public function testOffsetSetWithoutIndexExpectingException() {
    $list = new Collection();
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('InvalidArgumentException: Please provide a valid offset for the icon.');
    $list[] = new \Papaya\UI\Icon('sample.png');
  }

  /**
   * @covers \Papaya\UI\Icon\Collection::offsetSet
   */
  public function testOffsetSetWithInvalidIconExpectingException() {
    $list = new Collection();
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('InvalidArgumentException: Please provide an instance of Papaya\UI\Icon.');
    $list['sample'] = 'X';
  }

  /**
   * @covers \Papaya\UI\Icon\Collection::offsetUnset
   */
  public function testOffsetUnset() {
    $list = new Collection();
    $list['sample'] = new \Papaya\UI\Icon('sample.png');
    unset($list['sample']);
    $this->assertFalse(isset($list['sample']));
  }

  /**
   * @covers \Papaya\UI\Icon\Collection::count
   */
  public function testCountExpectingZero() {
    $list = new Collection();
    $this->assertCount(0, $list);
  }

  /**
   * @covers \Papaya\UI\Icon\Collection::count
   */
  public function testCountExpectingTwo() {
    $list = new Collection();
    $list['one'] = $icon = new \Papaya\UI\Icon('one.png');
    $list['two'] = $icon = new \Papaya\UI\Icon('two.png');
    $this->assertCount(2, $list);
  }

  /**
   * @covers \Papaya\UI\Icon\Collection::getIterator
   */
  public function testGetIterator() {
    $list = new Collection();
    $list['sample'] = $icon = new \Papaya\UI\Icon('sample.png');
    $this->assertSame(array('sample' => $icon), $list->getIterator()->getArrayCopy());
  }
}
