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

namespace Papaya\Database\Record\Order;

require_once __DIR__.'/../../../../../bootstrap.php';

class CollectionTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Database\Record\Order\Collection::__construct
   */
  public function testConstructorWithoutArguments() {
    $orderBy = new Collection();
    $this->assertEquals(0, $orderBy->count());
  }

  /**
   * @covers \Papaya\Database\Record\Order\Collection::__construct
   */
  public function testConstructorWithArguments() {
    $child = $this->createMock(\Papaya\Database\Interfaces\Order::class);
    $orderBy = new Collection($child);
    $this->assertEquals(1, $orderBy->count());
  }

  /**
   * @covers \Papaya\Database\Record\Order\Collection::__toString
   */
  public function testToStringWithTwoItems() {
    $one = $this->createMock(\Papaya\Database\Interfaces\Order::class);
    $one
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('field_one ASC'));
    $two = $this->createMock(\Papaya\Database\Interfaces\Order::class);
    $two
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('field_two DESC'));
    $orderBy = new Collection($one, $two);
    $this->assertEquals('field_one ASC, field_two DESC', (string)$orderBy);
  }

  /**
   * @covers \Papaya\Database\Record\Order\Collection::__toString
   */
  public function testToStringWithoutItems() {
    $orderBy = new Collection();
    $this->assertEquals('', (string)$orderBy);
  }
}
