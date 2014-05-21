<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaUiListviewItemsBuilderTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewItemsBuilder::__construct
  * @covers PapayaUiListviewItemsBuilder::getDataSource
  */
  public function testConstructor() {
    $iterator = $this->getMock('Iterator');
    $builder = new PapayaUiListviewItemsBuilder($iterator);
    $this->assertSame($iterator, $builder->getDataSource());
  }

  /**
  * @covers PapayaUiListviewItemsBuilder::fill
  */
  public function testFillWithDefaultCallbacks() {
    $items = $this
      ->getMockBuilder('PapayaUiListviewItems')
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->once())
      ->method('clear');
    $items
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf('PapayaUiListviewItem'));
    $builder = new PapayaUiListviewItemsBuilder(
      array('Sample One')
    );
    $builder->fill($items);
  }

  /**
  * @covers PapayaUiListviewItemsBuilder::fill
  */
  public function testFillWithDefinedCallbacks() {
    $item = $this
      ->getMockBuilder('PapayaUiListviewItem')
      ->disableOriginalConstructor()
      ->getMock();
    $callbacks = $this->getMock('PapayaUiListviewItemsBuilderCallbacks');
    $callbacks
      ->expects($this->once())
      ->method('__isset')
      ->will($this->returnValue(TRUE));
    $callbacks
      ->expects($this->exactly(3))
      ->method('__call')
      ->will($this->returnValue(TRUE));
    $items = $this
      ->getMockBuilder('PapayaUiListviewItems')
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->never())
      ->method('clear');
    $items
      ->expects($this->never())
      ->method('offsetSet');
    $builder = new PapayaUiListviewItemsBuilder(
      array('Sample One')
    );
    $builder->callbacks($callbacks);
    $builder->fill($items);
  }

  /**
  * @covers PapayaUiListviewItemsBuilder::callbacks
  */
  public function testCallbacksGetAfterSet() {
    $callbacks = $this->getMock('PapayaUiListviewItemsBuilderCallbacks');
    $builder = new PapayaUiListviewItemsBuilder(array());
    $builder->callbacks($callbacks);
    $this->assertSame($callbacks, $builder->callbacks());
  }

  /**
  * @covers PapayaUiListviewItemsBuilder::callbacks
  */
  public function testCallbacksGetImplicitCreate() {
    $builder = new PapayaUiListviewItemsBuilder(array());
    $this->assertInstanceOf('PapayaUiListviewItemsBuilderCallbacks', $builder->callbacks());
  }

  public function testCreateItem() {
    $items = $this
      ->getMockBuilder('PapayaUiListviewItems')
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf('PapayaUiListviewItem'));
    $builder = new PapayaUiListviewItemsBuilder(array());
    $builder->createItem(NULL, $items, 'Sample', 0);
  }
}