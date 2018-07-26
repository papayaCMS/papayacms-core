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
  * @covers \PapayaUiListviewItemsBuilder::__construct
  * @covers \PapayaUiListviewItemsBuilder::getDataSource
  */
  public function testConstructor() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Iterator $iterator */
    $iterator = $this->createMock(Iterator::class);
    $builder = new \PapayaUiListviewItemsBuilder($iterator);
    $this->assertSame($iterator, $builder->getDataSource());
  }

  /**
  * @covers \PapayaUiListviewItemsBuilder::fill
  */
  public function testFillWithDefaultCallbacks() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaUiListviewItems $items */
    $items = $this
      ->getMockBuilder(\PapayaUiListviewItems::class)
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->once())
      ->method('clear');
    $items
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf(\PapayaUiListviewItem::class));
    $builder = new \PapayaUiListviewItemsBuilder(
      array('Sample One')
    );
    $builder->fill($items);
  }

  /**
  * @covers \PapayaUiListviewItemsBuilder::fill
  */
  public function testFillWithDefinedCallbacks() {
    $callbacks = $this->createMock(\PapayaUiListviewItemsBuilderCallbacks::class);
    $callbacks
      ->expects($this->once())
      ->method('__isset')
      ->will($this->returnValue(TRUE));
    $callbacks
      ->expects($this->exactly(3))
      ->method('__call')
      ->will($this->returnValue(TRUE));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaUiListviewItems $items */
    $items = $this
      ->getMockBuilder(\PapayaUiListviewItems::class)
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->never())
      ->method('clear');
    $items
      ->expects($this->never())
      ->method('offsetSet');
    $builder = new \PapayaUiListviewItemsBuilder(
      array('Sample One')
    );
    $builder->callbacks($callbacks);
    $builder->fill($items);
  }

  /**
  * @covers \PapayaUiListviewItemsBuilder::callbacks
  */
  public function testCallbacksGetAfterSet() {
    $callbacks = $this->createMock(\PapayaUiListviewItemsBuilderCallbacks::class);
    $builder = new \PapayaUiListviewItemsBuilder(array());
    $builder->callbacks($callbacks);
    $this->assertSame($callbacks, $builder->callbacks());
  }

  /**
  * @covers \PapayaUiListviewItemsBuilder::callbacks
  */
  public function testCallbacksGetImplicitCreate() {
    $builder = new \PapayaUiListviewItemsBuilder(array());
    $this->assertInstanceOf(\PapayaUiListviewItemsBuilderCallbacks::class, $builder->callbacks());
  }

  public function testCreateItem() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaUiListviewItems $items */
    $items = $this
      ->getMockBuilder(\PapayaUiListviewItems::class)
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf(\PapayaUiListviewItem::class));
    $builder = new \PapayaUiListviewItemsBuilder(array());
    $builder->createItem(NULL, $items, 'Sample');
  }
}
