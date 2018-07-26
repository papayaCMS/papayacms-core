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

use Papaya\Database\Interfaces\Order;

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaDatabaseRecordOrderListTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseRecordOrderList::__construct
  */
  public function testConstructorWithoutArguments() {
    $orderBy = new PapayaDatabaseRecordOrderList();
    $this->assertEquals(0, $orderBy->count());
  }

  /**
  * @covers PapayaDatabaseRecordOrderList::__construct
  */
  public function testConstructorWithArguments() {
    $child = $this->createMock(Order::class);
    $orderBy = new PapayaDatabaseRecordOrderList($child);
    $this->assertEquals(1, $orderBy->count());
  }

  /**
  * @covers PapayaDatabaseRecordOrderList::__toString
  */
  public function testToStringWithTwoItems() {
    $one = $this->createMock(Order::class);
    $one
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('field_one ASC'));
    $two = $this->createMock(Order::class);
    $two
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('field_two DESC'));
    $orderBy = new PapayaDatabaseRecordOrderList($one, $two);
    $this->assertEquals('field_one ASC, field_two DESC', (string)$orderBy);
  }

  /**
  * @covers PapayaDatabaseRecordOrderList::__toString
  */
  public function testToStringWithoutItems() {
    $orderBy = new PapayaDatabaseRecordOrderList();
    $this->assertEquals('', (string)$orderBy);
  }
}
