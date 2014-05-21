<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaUiControlCollectionTest extends PapayaTestCase {

  /**
  * @covers PapayaUiControlCollection::appendTo
  */
  public function testAppendToCallsItems() {
    $dom = new PapayaXmlDocument();
    $parentNode = $dom->appendElement('sample');
    $itemOne = $this->getMockItemFixture();
    $itemOne
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->equalTo($parentNode));
    $itemTwo = $this->getMockItemFixture();
    $itemTwo
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->equalTo($parentNode));
    $collection = new PapayaUiControlCollection();
    $collection
      ->add($itemOne)
      ->add($itemTwo);
    $this->assertSame($parentNode, $collection->appendTo($parentNode));
    $this->assertEquals('<sample/>', $parentNode->saveXml());
  }

  /**
  * @covers PapayaUiControlCollection::appendTo
  */
  public function testAppendToWithTagName() {
    $dom = new PapayaXmlDocument();
    $parentNode = $dom->appendElement('sample');
    $item = $this->getMockItemFixture();
    $item
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $collection = new PapayaUiControlCollection_TestProxy();
    $collection->_tagName = 'items';
    $collection->add($item);
    $this->assertNotSame($parentNode, $resultNode = $collection->appendTo($parentNode));
    $this->assertEquals('<items/>', $resultNode->saveXml());
  }

  /**
  * @covers PapayaUiControlCollection::appendTo
  */
  public function testAppendToWithoutItems() {
    $dom = new PapayaXmlDocument();
    $parentNode = $dom->appendElement('sample');
    $collection = new PapayaUiControlCollection();
    $this->assertNull($collection->appendTo($parentNode));
  }

  /**
  * @covers PapayaUiControlCollection::owner
  */
  public function testOwnerGetAfterSet() {
    $collection = new PapayaUiControlCollection();
    $collection->owner($owner = $this->getMock('stdClass'));
    $this->assertSame($owner, $collection->owner());
  }

  /**
  * @covers PapayaUiControlCollection::owner
  */
  public function testOwnerSetPreparesItems() {
    $item = $this->getMockItemFixture();
    $item
      ->expects($this->exactly(2))
      ->method('collection')
      ->with($this->isInstanceOf('PapayaUiControlCollection'));
    $collection = new PapayaUiControlCollection();
    $collection->add($item);
    $collection->owner($this->getMock('stdClass'));
  }

  /**
  * @covers PapayaUiControlCollection::owner
  */
  public function testOwnerGetExpectingException() {
    $collection = new PapayaUiControlCollection();
    $this->setExpectedException(
      'LogicException',
      'LogicException: Collection "PapayaUiControlCollection" has no owner object.'
    );
    $owner = $collection->owner();
  }

  /**
  * @covers PapayaUiControlCollection::owner
  */
  public function testOwnerSetNoObjectExpectingException() {
    $collection = new PapayaUiControlCollection();
    $this->setExpectedException('UnexpectedValueException');
    $collection->owner('WRONG');
  }

  /**
  * @covers PapayaUiControlCollection::owner
  */
  public function testOwnerSetInvalidClassExpectingException() {
    $collection = new PapayaUiControlCollection_TestProxy();
    $this->setExpectedException('UnexpectedValueException');
    $collection->owner(new stdClass());
  }

  /**
  * @covers PapayaUiControlCollection::owner
  */
  public function testOwnerSetValidSuperclass() {
    $collection = new PapayaUiControlCollection_TestProxy();
    $collection->owner($owner = $this->getMock('PapayaObject'));
    $this->assertSame($owner, $collection->owner());
  }

  /**
  * @covers PapayaUiControlCollection::hasOwner
  */
  public function testHasOwnerExpectingTrue() {
    $owner = $this->getMock('stdClass');
    $collection = new PapayaUiControlCollection();
    $collection->owner($owner);
    $this->assertTrue($collection->hasOwner());
  }

  /**
  * @covers PapayaUiControlCollection::hasOwner
  */
  public function testHasOwnerExpectingFalse() {
    $collection = new PapayaUiControlCollection();
    $this->assertFalse($collection->hasOwner());
  }

  /**
  * @covers PapayaUiControlCollection::get
  * @covers PapayaUiControlCollection::prepareOffset
  */
  public function testGetFirstItem() {
    $item = $this->getMockItemFixture();
    $collection = new PapayaUiControlCollection();
    $collection->add($item);
    $this->assertSame(
      $item, $collection->get(0)
    );
  }

  /**
  * @covers PapayaUiControlCollection::get
  * @covers PapayaUiControlCollection::prepareOffset
  */
  public function testGetSecondItem() {
    $itemOne = $this->getMockItemFixture();
    $itemTwo = $this->getMockItemFixture();
    $collection = new PapayaUiControlCollection();
    $collection->add($itemOne);
    $collection->add($itemTwo);
    $this->assertSame(
      $itemTwo, $collection->get(1)
    );
  }

  /**
  * @covers PapayaUiControlCollection::get
  * @covers PapayaUiControlCollection::prepareOffset
  */
  public function testGetLastItemUsingNegativeOffset() {
    $itemOne = $this->getMockItemFixture();
    $itemTwo = $this->getMockItemFixture();
    $collection = new PapayaUiControlCollection();
    $collection->add($itemOne);
    $collection->add($itemTwo);
    $this->assertSame(
      $itemTwo, $collection->get(-1)
    );
  }

  public function testGetWithoutItemExpectingException() {
    $collection = new PapayaUiControlCollection();
    $this->setExpectedException(
      'OutOfBoundsException',
      'OutOfBoundsException: Invalid offset "0".'
    );
    $collection->get(0);
  }

  /**
  * @covers PapayaUiControlCollection::has
  */
  public function testHasExpectingTrue() {
    $itemOne = $this->getMockItemFixture();
    $collection = new PapayaUiControlCollection();
    $collection->add($itemOne);
    $this->assertTrue($collection->has(0));
  }

  /**
  * @covers PapayaUiControlCollection::has
  */
  public function testHasExpectingFalse() {
    $collection = new PapayaUiControlCollection();
    $this->assertFalse($collection->has(99));
  }

  /**
  * @covers PapayaUiControlCollection::add
  * @covers PapayaUiControlCollection::validateItemClass
  */
  public function testAdd() {
    $collection = new PapayaUiControlCollection();
    $itemOne = $this->getMockItemFixture();
    $itemOne
      ->expects($this->once())
      ->method('collection')
      ->with($this->isInstanceOf('PapayaUiControlCollection'));
    $itemOne
      ->expects($this->once())
      ->method('index')
      ->with(0);
    $itemTwo = $this->getMockItemFixture();
    $itemTwo
      ->expects($this->once())
      ->method('collection')
      ->with($this->isInstanceOf('PapayaUiControlCollection'));
    $itemTwo
      ->expects($this->once())
      ->method('index')
      ->with(1);
    $this->assertSame(
      $collection, $collection->add($itemOne)
    );
    $this->assertSame(
      $collection, $collection->add($itemTwo)
    );
    $this->assertAttributeSame(
      array($itemOne, $itemTwo), '_items', $collection
    );
  }

  /**
  * @covers PapayaUiControlCollection::prepareItem
  */
  public function testAddPreparesItem() {
    $collection = new PapayaUiControlCollection();
    $collection->papaya($application = $this->mockPapaya()->application());
    $item = $this->getMockItemFixture();
    $item
      ->expects($this->once())
      ->method('collection')
      ->with($this->isInstanceOf('PapayaUiControlCollection'));
    $item
      ->expects($this->once())
      ->method('papaya')
      ->with($this->equalTo($application));
    $collection->add($item);
  }

  /**
  * @covers PapayaUiControlCollection::add
  * @covers PapayaUiControlCollection::validateItemClass
  */
  public function testAddWithInvalidItemClassExpectingException() {
    $item = $this->getMockItemFixture();
    $collection = new PapayaUiControlCollection_TestProxy();
    $collection->_itemClass = 'PapayaUiControlInteractive';
    $this->setExpectedException(
      'InvalidArgumentException',
      'InvalidArgumentException: Invalid item class "Mock_PapayaUiControl'
    );
    $collection->add($item);
  }

  /**
  * @covers PapayaUiControlCollection::set
  */
  public function testSet() {
    $itemOne = $this->getMockItemFixture();
    $itemTwo = $this->getMockItemFixture();
    $itemTwo
      ->expects($this->once())
      ->method('index')
      ->with(0);
    $collection = new PapayaUiControlCollection();
    $collection->add($itemOne);
    $this->assertSame(
      $collection, $collection->set(0, $itemTwo)
    );
    $this->assertAttributeSame(
      array($itemTwo), '_items', $collection
    );
  }

  /**
  * @covers PapayaUiControlCollection::set
  */
  public function testSetReplaceFirstItem() {
    $itemReplace = $this->getMockItemFixture();
    $itemReplace
      ->expects($this->once())
      ->method('index')
      ->with(1);
    $collection = new PapayaUiControlCollection();
    $collection->add($itemOne = $this->getMockItemFixture());
    $collection->add($this->getMockItemFixture());
    $collection->add($itemTwo = $this->getMockItemFixture());
    $this->assertSame(
      $collection, $collection->set(1, $itemReplace)
    );
    $this->assertAttributeSame(
      array($itemOne, $itemReplace, $itemTwo), '_items', $collection
    );
  }

  /**
  * @covers PapayaUiControlCollection::prepareItem
  */
  public function testSetPreparesItem() {
    $application = $this->mockPapaya()->application();
    $item = $this->getMockItemFixture();
    $item
      ->expects($this->exactly(2))
      ->method('collection')
      ->with($this->isInstanceOf('PapayaUiControlCollection'));
    $item
      ->expects($this->exactly(2))
      ->method('papaya')
      ->with($this->equalTo($application));
    $collection = new PapayaUiControlCollection();
    $collection->papaya($application);
    $collection->add($item);
    $collection->set(0, $item);
  }

  /**
  * @covers PapayaUiControlCollection::set
  */
  public function testSetWithInvalidItemClassExpectingException() {
    $itemOne = $this->getMock(
      'PapayaUiControlCollection_TestItem', array('appendTo', 'index', 'setApplication')
    );
    $itemTwo = $this->getMockItemFixture();
    $collection = new PapayaUiControlCollection_TestProxy();
    $collection->_itemClass = 'PapayaUiControlCollection_TestItem';
    $collection->add($itemOne);
    $this->setExpectedException('InvalidArgumentException');
    $collection->set(0, $itemTwo);
  }

  /**
  * @covers PapayaUiControlCollection::set
  */
  public function testSetWithInvalidOffsetExpectingException() {
    $itemOne = $this->getMockItemFixture();
    $itemTwo = $this->getMockItemFixture();
    $collection = new PapayaUiControlCollection();
    $collection->add($itemOne);
    $this->setExpectedException(
      'OutOfBoundsException',
      'OutOfBoundsException: Invalid offset "99".'
    );
    $collection->set(99, $itemTwo);
  }

  /**
  * @covers PapayaUiControlCollection::insertBefore
  * @covers PapayaUiControlCollection::updateItemIndex
  */
  public function testInsertBefore() {
    $itemOne = $this->getMockItemFixture();
    $itemOne
      ->expects($this->exactly(2))
      ->method('index')
      ->with($this->logicalOr(0, 1));
    $itemTwo = $this->getMockItemFixture();
    $itemTwo
      ->expects($this->once())
      ->method('index')
      ->with(0);
    $collection = new PapayaUiControlCollection();
    $collection->add($itemOne);
    $this->assertSame(
      $collection, $collection->insertBefore(0, $itemTwo)
    );
    $this->assertAttributeSame(
      array($itemTwo, $itemOne), '_items', $collection
    );
  }

  /**
  * @covers PapayaUiControlCollection::prepareItem
  */
  public function testInsertBeforePreparesItem() {
    $collection = new PapayaUiControlCollection();
    $application = $this->mockPapaya()->application();
    $itemOne = $this->getMockItemFixture();
    $itemOne
      ->expects($this->once())
      ->method('collection')
      ->with($this->isInstanceOf('PapayaUiControlCollection'));
    $itemOne
      ->expects($this->once())
      ->method('papaya')
      ->with($this->equalTo($application));
    $itemTwo = $this->getMockItemFixture();
    $itemTwo
      ->expects($this->once())
      ->method('collection')
      ->with($this->isInstanceOf('PapayaUiControlCollection'));
    $itemTwo
      ->expects($this->once())
      ->method('papaya')
      ->with($this->equalTo($application));
    $collection->papaya($application);
    $collection->add($itemOne);
    $collection->insertBefore(0, $itemTwo);
  }

  /**
  * @covers PapayaUiControlCollection::insertBefore
  */
  public function testInsertBeforeWithInvalidOffsetExpectingException() {
    $itemOne = $this->getMockItemFixture();
    $itemTwo = $this->getMockItemFixture();
    $collection = new PapayaUiControlCollection();
    $collection->add($itemOne);
    $this->setExpectedException(
      'OutOfBoundsException',
      'OutOfBoundsException: Invalid offset "99".'
    );
    $collection->insertBefore(99, $itemTwo);
  }

  /**
  * @covers PapayaUiControlCollection::remove
  */
  public function testRemove() {
    $itemOne = $this->getMockItemFixture();
    $itemTwo = $this->getMockItemFixture();
    $collection = new PapayaUiControlCollection();
    $collection->add($itemOne);
    $collection->add($itemTwo);
    $collection->remove(0);
    $this->assertAttributeSame(
      array($itemTwo), '_items', $collection
    );
  }

  /**
  * @covers PapayaUiControlCollection::remove
  */
  public function testRemoveWithInvalidOffsetExpectingException() {
    $itemOne = $this->getMockItemFixture();
    $collection = new PapayaUiControlCollection();
    $collection->add($itemOne);
    $this->setExpectedException(
      'OutOfBoundsException',
      'OutOfBoundsException: Invalid offset "99".'
    );
    $collection->remove(99);
  }

  /**
  * @covers PapayaUiControlCollection::clear
  */
  public function testClear() {
    $itemOne = $this->getMockItemFixture();
    $collection = new PapayaUiControlCollection();
    $collection->add($itemOne);
    $collection->clear();
    $this->assertEquals(
      array(), $collection->toArray()
    );
  }

  /**
  * @covers PapayaUiControlCollection::toArray
  */
  public function testToArray() {
    $itemOne = $this->getMockItemFixture();
    $itemTwo = $this->getMockItemFixture();
    $collection = new PapayaUiControlCollection();
    $collection->add($itemOne);
    $collection->add($itemTwo);
    $this->assertSame(
      array($itemOne, $itemTwo), $collection->toArray()
    );
  }

  /**
  * @covers PapayaUiControlCollection::getIterator
  */
  public function testGetIterator() {
    $itemOne = $this->getMockItemFixture();
    $itemTwo = $this->getMockItemFixture();
    $collection = new PapayaUiControlCollection();
    $collection->add($itemOne);
    $collection->add($itemTwo);
    $iterator = $collection->getIterator();
    $this->assertInstanceOf('ArrayIterator', $iterator);
    $this->assertSame(
      array($itemOne, $itemTwo), $iterator->getArrayCopy()
    );
  }

  /**
  * @covers PapayaUiControlCollection::count
  */
  public function testCount() {
    $itemOne = $this->getMockItemFixture();
    $itemTwo = $this->getMockItemFixture();
    $collection = new PapayaUiControlCollection();
    $collection->add($itemOne);
    $collection->add($itemTwo);
    $this->assertEquals(
      2, count($collection)
    );
  }

  /**
  * @covers PapayaUiControlCollection::offsetExists
  */
  public function testOffsetExistsExpectingTrue() {
    $itemOne = $this->getMockItemFixture();
    $collection = new PapayaUiControlCollection();
    $collection->add($itemOne);
    $this->assertTrue(isset($collection[0]));
  }

  /**
  * @covers PapayaUiControlCollection::offsetExists
  */
  public function testOffsetExistsExpectingFalse() {
    $collection = new PapayaUiControlCollection();
    $this->assertFalse(isset($collection[99]));
  }

  /**
  * @covers PapayaUiControlCollection::offsetGet
  */
  public function testOffsetGet() {
    $itemOne = $this->getMockItemFixture();
    $collection = new PapayaUiControlCollection();
    $collection->add($itemOne);
    $this->assertSame($itemOne, $collection[0]);
  }

  /**
  * @covers PapayaUiControlCollection::offsetSet
  */
  public function testOffsetSetAppendingItem() {
    $itemOne = $this->getMockItemFixture();
    $collection = new PapayaUiControlCollection();
    $collection[] = $itemOne;
    $this->assertAttributeSame(
      array($itemOne), '_items', $collection
    );
  }

  /**
  * @covers PapayaUiControlCollection::offsetSet
  */
  public function testOffsetSetReplacingItem() {
    $itemOne = $this->getMockItemFixture();
    $itemTwo = $this->getMockItemFixture();
    $collection = new PapayaUiControlCollection();
    $collection->add($itemOne);
    $collection[0] = $itemTwo;
    $this->assertAttributeSame(
      array($itemTwo), '_items', $collection
    );
  }

  /**
  * @covers PapayaUiControlCollection::offsetUnset
  */
  public function testOffsetUnset() {
    $itemOne = $this->getMockItemFixture();
    $itemTwo = $this->getMockItemFixture();
    $collection = new PapayaUiControlCollection();
    $collection->add($itemOne);
    $collection->add($itemTwo);
    unset($collection[0]);
    $this->assertAttributeSame(
      array($itemTwo), '_items', $collection
    );
  }

  public function getMockItemFixture() {
    $item = $this->getMock(
      'PapayaUiControlCollectionItem',
      array('appendTo', 'collection', 'index', 'papaya')
    );
    return $item;
  }
}

class PapayaUiControlCollection_TestProxy extends PapayaUiControlCollection {

  public $_tagName = '';

  public $_itemClass = 'PapayaUiControl';

  public $_ownerClass = 'PapayaObject';
}

abstract class PapayaUiControlCollection_TestItem
  extends PapayaUiControlCollectionItem {

}
