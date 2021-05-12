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

namespace Papaya\UI\Control {

  require_once __DIR__.'/../../../../bootstrap.php';

  class CollectionTest extends \Papaya\TestCase {

    /**
     * @covers \Papaya\UI\Control\Collection::appendTo
     */
    public function testAppendToCallsItems() {
      $document = new \Papaya\XML\Document();
      $parentNode = $document->appendElement('sample');
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
      $collection = new Collection();
      $collection
        ->add($itemOne)
        ->add($itemTwo);
      $this->assertSame($parentNode, $collection->appendTo($parentNode));
      $this->assertEquals(/** @lang XML */
        '<sample/>', $parentNode->saveXML());
    }

    /**
     * @covers \Papaya\UI\Control\Collection::appendTo
     */
    public function testAppendToWithTagName() {
      $document = new \Papaya\XML\Document();
      $parentNode = $document->appendElement('sample');
      $item = $this->getMockItemFixture();
      $item
        ->expects($this->once())
        ->method('appendTo')
        ->with($this->isInstanceOf(\Papaya\XML\Element::class));
      $collection = new Collection_TestProxy();
      $collection->_tagName = 'items';
      $collection->add($item);
      $this->assertNotSame($parentNode, $resultNode = $collection->appendTo($parentNode));
      $this->assertEquals(/** @lang XML */
        '<items/>', $resultNode->saveXML());
    }

    /**
     * @covers \Papaya\UI\Control\Collection::appendTo
     */
    public function testAppendToWithoutItems() {
      $document = new \Papaya\XML\Document();
      $parentNode = $document->appendElement('sample');
      $collection = new Collection();
      $this->assertNull($collection->appendTo($parentNode));
    }

    /**
     * @covers \Papaya\UI\Control\Collection::owner
     */
    public function testOwnerGetAfterSet() {
      $collection = new Collection();
      $collection->owner($owner = $this->createMock(\stdClass::class));
      $this->assertSame($owner, $collection->owner());
    }

    /**
     * @covers \Papaya\UI\Control\Collection::owner
     */
    public function testOwnerSetPreparesItems() {
      $item = $this->getMockItemFixture();
      $item
        ->expects($this->exactly(2))
        ->method('collection')
        ->with($this->isInstanceOf(Collection::class));
      $collection = new Collection();
      $collection->add($item);
      $collection->owner($this->createMock(\stdClass::class));
    }

    /**
     * @covers \Papaya\UI\Control\Collection::owner
     */
    public function testOwnerGetExpectingException() {
      $collection = new Collection();
      $this->expectException(\LogicException::class);
      $this->expectExceptionMessage('LogicException: Collection "Papaya\UI\Control\Collection" has no owner object.');
      $collection->owner();
    }

    /**
     * @covers \Papaya\UI\Control\Collection::owner
     */
    public function testOwnerSetNoObjectExpectingException() {
      $collection = new Collection();
      $this->expectException(\UnexpectedValueException::class);
      $collection->owner('WRONG');
    }

    /**
     * @covers \Papaya\UI\Control\Collection::owner
     */
    public function testOwnerSetInvalidClassExpectingException() {
      $collection = new Collection_TestProxy();
      $this->expectException(\UnexpectedValueException::class);
      $collection->owner(new \stdClass());
    }

    /**
     * @covers \Papaya\UI\Control\Collection::owner
     */
    public function testOwnerSetValidSuperclass() {
      $collection = new Collection_TestProxy();
      $collection->owner($owner = $this->createMock(\Papaya\Application\BaseObject::class));
      $this->assertSame($owner, $collection->owner());
    }

    /**
     * @covers \Papaya\UI\Control\Collection::hasOwner
     */
    public function testHasOwnerExpectingTrue() {
      $owner = $this->createMock(\stdClass::class);
      $collection = new Collection();
      $collection->owner($owner);
      $this->assertTrue($collection->hasOwner());
    }

    /**
     * @covers \Papaya\UI\Control\Collection::hasOwner
     */
    public function testHasOwnerExpectingFalse() {
      $collection = new Collection();
      $this->assertFalse($collection->hasOwner());
    }

    /**
     * @covers \Papaya\UI\Control\Collection::get
     * @covers \Papaya\UI\Control\Collection::prepareOffset
     */
    public function testGetFirstItem() {
      $item = $this->getMockItemFixture();
      $collection = new Collection();
      $collection->add($item);
      $this->assertSame(
        $item, $collection->get(0)
      );
    }

    /**
     * @covers \Papaya\UI\Control\Collection::get
     * @covers \Papaya\UI\Control\Collection::prepareOffset
     */
    public function testGetSecondItem() {
      $itemOne = $this->getMockItemFixture();
      $itemTwo = $this->getMockItemFixture();
      $collection = new Collection();
      $collection->add($itemOne);
      $collection->add($itemTwo);
      $this->assertSame(
        $itemTwo, $collection->get(1)
      );
    }

    /**
     * @covers \Papaya\UI\Control\Collection::get
     * @covers \Papaya\UI\Control\Collection::prepareOffset
     */
    public function testGetLastItemUsingNegativeOffset() {
      $itemOne = $this->getMockItemFixture();
      $itemTwo = $this->getMockItemFixture();
      $collection = new Collection();
      $collection->add($itemOne);
      $collection->add($itemTwo);
      $this->assertSame(
        $itemTwo, $collection->get(-1)
      );
    }

    public function testGetWithoutItemExpectingException() {
      $collection = new Collection();
      $this->expectException(\OutOfBoundsException::class);
      $this->expectExceptionMessage('OutOfBoundsException: Invalid offset "0".');
      $collection->get(0);
    }

    /**
     * @covers \Papaya\UI\Control\Collection::has
     */
    public function testHasExpectingTrue() {
      $itemOne = $this->getMockItemFixture();
      $collection = new Collection();
      $collection->add($itemOne);
      $this->assertTrue($collection->has(0));
    }

    /**
     * @covers \Papaya\UI\Control\Collection::has
     */
    public function testHasExpectingFalse() {
      $collection = new Collection();
      $this->assertFalse($collection->has(99));
    }

    /**
     * @covers \Papaya\UI\Control\Collection::add
     * @covers \Papaya\UI\Control\Collection::validateItemClass
     */
    public function testAdd() {
      $collection = new Collection();
      $itemOne = $this->getMockItemFixture();
      $itemOne
        ->expects($this->once())
        ->method('collection')
        ->with($this->isInstanceOf(Collection::class));
      $itemOne
        ->expects($this->once())
        ->method('index')
        ->with(0);
      $itemTwo = $this->getMockItemFixture();
      $itemTwo
        ->expects($this->once())
        ->method('collection')
        ->with($this->isInstanceOf(Collection::class));
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
      $this->assertSame(
        array($itemOne, $itemTwo), iterator_to_array($collection)
      );
    }

    /**
     * @covers \Papaya\UI\Control\Collection::prepareItem
     */
    public function testAddPreparesItem() {
      $collection = new Collection();
      $collection->papaya($application = $this->mockPapaya()->application());
      $item = $this->getMockItemFixture();
      $item
        ->expects($this->once())
        ->method('collection')
        ->with($this->isInstanceOf(Collection::class));
      $item
        ->expects($this->once())
        ->method('papaya')
        ->with($this->equalTo($application));
      $collection->add($item);
    }

    /**
     * @covers \Papaya\UI\Control\Collection::add
     * @covers \Papaya\UI\Control\Collection::validateItemClass
     */
    public function testAddWithInvalidItemClassExpectingException() {
      $item = $this->getMockItemFixture();
      $collection = new Collection_TestProxy();
      $collection->_itemClass = Interactive::class;
      $this->expectException(\InvalidArgumentException::class);
      $this->expectExceptionMessage('InvalidArgumentException: Invalid item class');
      $collection->add($item);
    }

    /**
     * @covers \Papaya\UI\Control\Collection::set
     */
    public function testSet() {
      $itemOne = $this->getMockItemFixture();
      $itemTwo = $this->getMockItemFixture();
      $itemTwo
        ->expects($this->once())
        ->method('index')
        ->with(0);
      $collection = new Collection();
      $collection->add($itemOne);
      $this->assertSame(
        $collection, $collection->set(0, $itemTwo)
      );
      $this->assertSame(
        array($itemTwo), iterator_to_array($collection)
      );
    }

    /**
     * @covers \Papaya\UI\Control\Collection::set
     */
    public function testSetReplaceFirstItem() {
      $itemReplace = $this->getMockItemFixture();
      $itemReplace
        ->expects($this->once())
        ->method('index')
        ->with(1);
      $collection = new Collection();
      $collection->add($itemOne = $this->getMockItemFixture());
      $collection->add($this->getMockItemFixture());
      $collection->add($itemTwo = $this->getMockItemFixture());
      $this->assertSame(
        $collection, $collection->set(1, $itemReplace)
      );
      $this->assertSame(
        array($itemOne, $itemReplace, $itemTwo), iterator_to_array($collection)
      );
    }

    /**
     * @covers \Papaya\UI\Control\Collection::prepareItem
     */
    public function testSetPreparesItem() {
      $application = $this->mockPapaya()->application();
      $item = $this->getMockItemFixture();
      $item
        ->expects($this->exactly(2))
        ->method('collection')
        ->with($this->isInstanceOf(Collection::class));
      $item
        ->expects($this->exactly(2))
        ->method('papaya')
        ->with($this->equalTo($application));
      $collection = new Collection();
      $collection->papaya($application);
      $collection->add($item);
      $collection->set(0, $item);
    }

    /**
     * @covers \Papaya\UI\Control\Collection::set
     */
    public function testSetWithInvalidItemClassExpectingException() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Collection_TestItem $itemOne */
      $itemOne = $this->createMock(Collection_TestItem::class);
      $itemTwo = $this->getMockItemFixture();
      $collection = new Collection_TestProxy();
      $collection->_itemClass = Collection_TestItem::class;
      $collection->add($itemOne);
      $this->expectException(\InvalidArgumentException::class);
      $collection->set(0, $itemTwo);
    }

    /**
     * @covers \Papaya\UI\Control\Collection::set
     */
    public function testSetWithInvalidOffsetExpectingException() {
      $itemOne = $this->getMockItemFixture();
      $itemTwo = $this->getMockItemFixture();
      $collection = new Collection();
      $collection->add($itemOne);
      $this->expectException(\OutOfBoundsException::class);
      $this->expectExceptionMessage('OutOfBoundsException: Invalid offset "99".');
      $collection->set(99, $itemTwo);
    }

    /**
     * @covers \Papaya\UI\Control\Collection::insertBefore
     * @covers \Papaya\UI\Control\Collection::updateItemIndex
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
      $collection = new Collection();
      $collection->add($itemOne);
      $this->assertSame(
        $collection, $collection->insertBefore(0, $itemTwo)
      );
      $this->assertSame(
        array($itemTwo, $itemOne), iterator_to_array($collection)
      );
    }

    /**
     * @covers \Papaya\UI\Control\Collection::prepareItem
     */
    public function testInsertBeforePreparesItem() {
      $collection = new Collection();
      $application = $this->mockPapaya()->application();
      $itemOne = $this->getMockItemFixture();
      $itemOne
        ->expects($this->once())
        ->method('collection')
        ->with($this->isInstanceOf(Collection::class));
      $itemOne
        ->expects($this->once())
        ->method('papaya')
        ->with($this->equalTo($application));
      $itemTwo = $this->getMockItemFixture();
      $itemTwo
        ->expects($this->once())
        ->method('collection')
        ->with($this->isInstanceOf(Collection::class));
      $itemTwo
        ->expects($this->once())
        ->method('papaya')
        ->with($this->equalTo($application));
      $collection->papaya($application);
      $collection->add($itemOne);
      $collection->insertBefore(0, $itemTwo);
    }

    /**
     * @covers \Papaya\UI\Control\Collection::insertBefore
     */
    public function testInsertBeforeWithInvalidOffsetExpectingException() {
      $itemOne = $this->getMockItemFixture();
      $itemTwo = $this->getMockItemFixture();
      $collection = new Collection();
      $collection->add($itemOne);
      $this->expectException(\OutOfBoundsException::class);
      $this->expectExceptionMessage('OutOfBoundsException: Invalid offset "99".');
      $collection->insertBefore(99, $itemTwo);
    }

    /**
     * @covers \Papaya\UI\Control\Collection::remove
     */
    public function testRemove() {
      $itemOne = $this->getMockItemFixture();
      $itemTwo = $this->getMockItemFixture();
      $collection = new Collection();
      $collection->add($itemOne);
      $collection->add($itemTwo);
      $collection->remove(0);
      $this->assertSame(
        array($itemTwo), iterator_to_array($collection)
      );
    }

    /**
     * @covers \Papaya\UI\Control\Collection::remove
     */
    public function testRemoveWithInvalidOffsetExpectingException() {
      $itemOne = $this->getMockItemFixture();
      $collection = new Collection();
      $collection->add($itemOne);
      $this->expectException(\OutOfBoundsException::class);
      $this->expectExceptionMessage('OutOfBoundsException: Invalid offset "99".');
      $collection->remove(99);
    }

    /**
     * @covers \Papaya\UI\Control\Collection::clear
     */
    public function testClear() {
      $itemOne = $this->getMockItemFixture();
      $collection = new Collection();
      $collection->add($itemOne);
      $collection->clear();
      $this->assertEquals(
        array(), $collection->toArray()
      );
    }

    /**
     * @covers \Papaya\UI\Control\Collection::toArray
     */
    public function testToArray() {
      $itemOne = $this->getMockItemFixture();
      $itemTwo = $this->getMockItemFixture();
      $collection = new Collection();
      $collection->add($itemOne);
      $collection->add($itemTwo);
      $this->assertSame(
        array($itemOne, $itemTwo), $collection->toArray()
      );
    }

    /**
     * @covers \Papaya\UI\Control\Collection::getIterator
     */
    public function testGetIterator() {
      $itemOne = $this->getMockItemFixture();
      $itemTwo = $this->getMockItemFixture();
      $collection = new Collection();
      $collection->add($itemOne);
      $collection->add($itemTwo);
      $iterator = $collection->getIterator();
      $this->assertInstanceOf('ArrayIterator', $iterator);
      $this->assertSame(
        array($itemOne, $itemTwo), $iterator->getArrayCopy()
      );
    }

    /**
     * @covers \Papaya\UI\Control\Collection::count
     */
    public function testCount() {
      $itemOne = $this->getMockItemFixture();
      $itemTwo = $this->getMockItemFixture();
      $collection = new Collection();
      $collection->add($itemOne);
      $collection->add($itemTwo);
      $this->assertCount(2, $collection);
    }

    /**
     * @covers \Papaya\UI\Control\Collection::offsetExists
     */
    public function testOffsetExistsExpectingTrue() {
      $itemOne = $this->getMockItemFixture();
      $collection = new Collection();
      $collection->add($itemOne);
      $this->assertTrue(isset($collection[0]));
    }

    /**
     * @covers \Papaya\UI\Control\Collection::offsetExists
     */
    public function testOffsetExistsExpectingFalse() {
      $collection = new Collection();
      $this->assertFalse(isset($collection[99]));
    }

    /**
     * @covers \Papaya\UI\Control\Collection::offsetGet
     */
    public function testOffsetGet() {
      $itemOne = $this->getMockItemFixture();
      $collection = new Collection();
      $collection->add($itemOne);
      $this->assertSame($itemOne, $collection[0]);
    }

    /**
     * @covers \Papaya\UI\Control\Collection::offsetSet
     */
    public function testOffsetSetAppendingItem() {
      $itemOne = $this->getMockItemFixture();
      $collection = new Collection();
      $collection[] = $itemOne;
      $this->assertSame(
        array($itemOne), iterator_to_array($collection)
      );
    }

    /**
     * @covers \Papaya\UI\Control\Collection::offsetSet
     */
    public function testOffsetSetReplacingItem() {
      $itemOne = $this->getMockItemFixture();
      $itemTwo = $this->getMockItemFixture();
      $collection = new Collection();
      $collection->add($itemOne);
      $collection[0] = $itemTwo;
      $this->assertSame(
        array($itemTwo), iterator_to_array($collection)
      );
    }

    /**
     * @covers \Papaya\UI\Control\Collection::offsetUnset
     */
    public function testOffsetUnset() {
      $itemOne = $this->getMockItemFixture();
      $itemTwo = $this->getMockItemFixture();
      $collection = new Collection();
      $collection->add($itemOne);
      $collection->add($itemTwo);
      unset($collection[0]);
      $this->assertSame(
        array($itemTwo), iterator_to_array($collection)
      );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Collection\Item
     */
    public function getMockItemFixture() {
      return $this->createMock(Collection\Item::class);
    }
  }

  class Collection_TestProxy extends Collection {

    public /** @noinspection PropertyInitializationFlawsInspection */
      $_tagName = '';

    public $_itemClass = \Papaya\UI\Control::class;

    public $_ownerClass = \Papaya\Application\BaseObject::class;
  }

  abstract class Collection_TestItem
    extends Collection\Item {

  }
}
