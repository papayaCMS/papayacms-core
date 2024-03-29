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

namespace Papaya\UI\ListView\Items;

require_once __DIR__.'/../../../../../bootstrap.php';

class BuilderTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\UI\ListView\Items\Builder::__construct
   * @covers \Papaya\UI\ListView\Items\Builder::getDataSource
   */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Iterator $iterator */
    $iterator = $this->createMock(\Iterator::class);
    $builder = new Builder($iterator);
    $this->assertSame($iterator, $builder->getDataSource());
  }

  /**
   * @covers \Papaya\UI\ListView\Items\Builder::fill
   */
  public function testFillWithDefaultCallbacks() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\ListView\Items $items */
    $items = $this
      ->getMockBuilder(\Papaya\UI\ListView\Items::class)
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->once())
      ->method('clear');
    $items
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf(\Papaya\UI\ListView\Item::class));
    $builder = new Builder(
      array('Sample One')
    );
    $builder->fill($items);
  }

  /**
   * @covers \Papaya\UI\ListView\Items\Builder::fill
   */
  public function testFillWithDefinedCallbacks() {
    $callbacks = $this->createMock(Builder\Callbacks::class);
    $callbacks
      ->expects($this->once())
      ->method('__isset')
      ->will($this->returnValue(TRUE));
    $callbacks
      ->expects($this->exactly(3))
      ->method('__call')
      ->will($this->returnValue(TRUE));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\ListView\Items $items */
    $items = $this
      ->getMockBuilder(\Papaya\UI\ListView\Items::class)
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->never())
      ->method('clear');
    $items
      ->expects($this->never())
      ->method('offsetSet');
    $builder = new Builder(
      array('Sample One')
    );
    $builder->callbacks($callbacks);
    $builder->fill($items);
  }

  /**
   * @covers \Papaya\UI\ListView\Items\Builder::callbacks
   */
  public function testCallbacksGetAfterSet() {
    $callbacks = $this->createMock(Builder\Callbacks::class);
    $builder = new Builder(array());
    $builder->callbacks($callbacks);
    $this->assertSame($callbacks, $builder->callbacks());
  }

  /**
   * @covers \Papaya\UI\ListView\Items\Builder::callbacks
   */
  public function testCallbacksGetImplicitCreate() {
    $builder = new Builder(array());
    $this->assertInstanceOf(Builder\Callbacks::class, $builder->callbacks());
  }
}
