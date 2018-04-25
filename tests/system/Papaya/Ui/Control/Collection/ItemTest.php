<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiControlCollectionItemTest extends PapayaTestCase {

  private $_item = NULL;

  /**
  * @covers PapayaUiControlCollectionItem::hasCollection
  */
  public function testHasCollection() {
    $collection = $this->createMock(PapayaUiControlCollection::class);
    $item = new PapayaUiControlCollectionItem_TestProxy();
    $item->collection($collection);
    $this->assertTrue(
      $item->hasCollection()
    );
  }

  /**
  * @covers PapayaUiControlCollectionItem::collection
  */
  public function testCollectionGetAfterSet() {
    $papaya = $this->mockPapaya()->application();
    $collection = $this->createMock(PapayaUiControlCollection::class);
    $collection
      ->expects($this->once())
      ->method('papaya')
      ->will($this->returnValue($papaya));
    $item = new PapayaUiControlCollectionItem_TestProxy();
    $this->assertSame(
      $collection, $item->collection($collection)
    );
    $this->assertEquals(
      $papaya, $item->papaya()
    );
  }

  /**
  * @covers PapayaUiControlCollectionItem::collection
  */
  public function testCollectionWithoutSetExpectingExpcetion() {
    $item = new PapayaUiControlCollectionItem_TestProxy();
    $this->expectException(BadMethodCallException::class);
    $this->expectExceptionMessage('BadMethodCallException: Item ist not part of a collection.');
    $collection = $item->collection();
  }

  /**
  * @covers PapayaUiControlCollectionItem::index
  */
  public function testIndexGetWithoutSet() {
    $item = new PapayaUiControlCollectionItem_TestProxy();
    $this->assertSame(
      0, $item->index()
    );
  }

  /**
  * @covers PapayaUiControlCollectionItem::index
  */
  public function testIndexSetWithInvalidValue() {
    $collection = $this->createMock(PapayaUiControlCollection::class);
    $collection
      ->expects($this->once())
      ->method('get')
      ->with(42)
      ->will($this->returnValue(new PapayaUiControlCollectionItem_TestProxy()));
    $item = new PapayaUiControlCollectionItem_TestProxy();
    $item->collection($collection);
    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('UnexpectedValueException: Index "42" does not match the collection item.');
    $item->index(42);
  }

  /**
  * @covers PapayaUiControlCollectionItem::index
  */
  public function testIndex() {
    $collection = $this->createMock(PapayaUiControlCollection::class);
    $collection
      ->expects($this->once())
      ->method('get')
      ->with(23)
      ->will($this->returnCallback(array($this, 'callbackGetItemFromCollection')));
    $this->_item = $item = new PapayaUiControlCollectionItem_TestProxy();
    $item->collection($collection);
    $this->assertEquals(23, $item->index(23));
  }

  public function callbackGetItemFromCollection() {
    return $this->_item;
  }
}

class PapayaUiControlCollectionItem_TestProxy extends PapayaUiControlCollectionItem {
  public function appendTo(PapayaXMlElement $parent) {
  }
}
