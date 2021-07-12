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

namespace Papaya\UI\Control\Collection {

  require_once __DIR__.'/../../../../../bootstrap.php';

  class ItemTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers \Papaya\UI\Control\Collection\Item::hasCollection
     */
    public function testHasCollection() {
      $collection = $this->createMock(\Papaya\UI\Control\Collection::class);
      $item = new Item_TestProxy();
      $item->collection($collection);
      $this->assertTrue(
        $item->hasCollection()
      );
    }

    /**
     * @covers \Papaya\UI\Control\Collection\Item::collection
     */
    public function testCollectionGetAfterSet() {
      $papaya = $this->mockPapaya()->application();
      $collection = $this->createMock(\Papaya\UI\Control\Collection::class);
      $collection
        ->expects($this->once())
        ->method('papaya')
        ->will($this->returnValue($papaya));
      $item = new Item_TestProxy();
      $this->assertSame(
        $collection, $item->collection($collection)
      );
      $this->assertEquals(
        $papaya, $item->papaya()
      );
    }

    /**
     * @covers \Papaya\UI\Control\Collection\Item::collection
     */
    public function testCollectionWithoutSetExpectingException() {
      $item = new Item_TestProxy();
      $this->expectException(\BadMethodCallException::class);
      $this->expectExceptionMessage('BadMethodCallException: Item ist not part of a collection.');
      $item->collection();
    }

    /**
     * @covers \Papaya\UI\Control\Collection\Item::index
     */
    public function testIndexGetWithoutSet() {
      $item = new Item_TestProxy();
      $this->assertSame(
        0, $item->index()
      );
    }

    /**
     * @covers \Papaya\UI\Control\Collection\Item::index
     */
    public function testIndexSetWithInvalidValue() {
      $collection = $this->createMock(\Papaya\UI\Control\Collection::class);
      $collection
        ->expects($this->once())
        ->method('get')
        ->with(42)
        ->will($this->returnValue(new Item_TestProxy()));
      $item = new Item_TestProxy();
      $item->collection($collection);
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('UnexpectedValueException: Index "42" does not match the collection item.');
      $item->index(42);
    }

    /**
     * @covers \Papaya\UI\Control\Collection\Item::index
     */
    public function testIndex() {
      $item = new Item_TestProxy();
      $collection = $this->createMock(\Papaya\UI\Control\Collection::class);
      $collection
        ->expects($this->once())
        ->method('get')
        ->with(23)
        ->willReturnCallback(
          function () use ($item) {
            return $item;
          }
        );
      $item->collection($collection);
      $this->assertEquals(23, $item->index(23));
    }
  }

  class Item_TestProxy extends Item {
    public function appendTo(\Papaya\XML\Element $parent) {
    }
  }
}
