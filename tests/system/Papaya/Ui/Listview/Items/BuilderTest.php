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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiListviewItemsBuilderTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Listview\Items\Builder::__construct
  * @covers \Papaya\Ui\Listview\Items\Builder::getDataSource
  */
  public function testConstructor() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Iterator $iterator */
    $iterator = $this->createMock(Iterator::class);
    $builder = new \Papaya\Ui\Listview\Items\Builder($iterator);
    $this->assertSame($iterator, $builder->getDataSource());
  }

  /**
  * @covers \Papaya\Ui\Listview\Items\Builder::fill
  */
  public function testFillWithDefaultCallbacks() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Ui\Listview\Items $items */
    $items = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Items::class)
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->once())
      ->method('clear');
    $items
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf(\Papaya\Ui\Listview\Item::class));
    $builder = new \Papaya\Ui\Listview\Items\Builder(
      array('Sample One')
    );
    $builder->fill($items);
  }

  /**
  * @covers \Papaya\Ui\Listview\Items\Builder::fill
  */
  public function testFillWithDefinedCallbacks() {
    $callbacks = $this->createMock(\Papaya\Ui\Listview\Items\Builder\Callbacks::class);
    $callbacks
      ->expects($this->once())
      ->method('__isset')
      ->will($this->returnValue(TRUE));
    $callbacks
      ->expects($this->exactly(3))
      ->method('__call')
      ->will($this->returnValue(TRUE));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Ui\Listview\Items $items */
    $items = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Items::class)
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->never())
      ->method('clear');
    $items
      ->expects($this->never())
      ->method('offsetSet');
    $builder = new \Papaya\Ui\Listview\Items\Builder(
      array('Sample One')
    );
    $builder->callbacks($callbacks);
    $builder->fill($items);
  }

  /**
  * @covers \Papaya\Ui\Listview\Items\Builder::callbacks
  */
  public function testCallbacksGetAfterSet() {
    $callbacks = $this->createMock(\Papaya\Ui\Listview\Items\Builder\Callbacks::class);
    $builder = new \Papaya\Ui\Listview\Items\Builder(array());
    $builder->callbacks($callbacks);
    $this->assertSame($callbacks, $builder->callbacks());
  }

  /**
  * @covers \Papaya\Ui\Listview\Items\Builder::callbacks
  */
  public function testCallbacksGetImplicitCreate() {
    $builder = new \Papaya\Ui\Listview\Items\Builder(array());
    $this->assertInstanceOf(\Papaya\Ui\Listview\Items\Builder\Callbacks::class, $builder->callbacks());
  }

  public function testCreateItem() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Ui\Listview\Items $items */
    $items = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Items::class)
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf(\Papaya\Ui\Listview\Item::class));
    $builder = new \Papaya\Ui\Listview\Items\Builder(array());
    $builder->createItem(NULL, $items, 'Sample');
  }
}
