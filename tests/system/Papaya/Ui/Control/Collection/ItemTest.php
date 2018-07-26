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

class PapayaUiControlCollectionItemTest extends PapayaTestCase {

  /**
  * @covers \PapayaUiControlCollectionItem::hasCollection
  */
  public function testHasCollection() {
    $collection = $this->createMock(PapayaUiControlCollection::class);
    $item = new \PapayaUiControlCollectionItem_TestProxy();
    $item->collection($collection);
    $this->assertTrue(
      $item->hasCollection()
    );
  }

  /**
  * @covers \PapayaUiControlCollectionItem::collection
  */
  public function testCollectionGetAfterSet() {
    $papaya = $this->mockPapaya()->application();
    $collection = $this->createMock(PapayaUiControlCollection::class);
    $collection
      ->expects($this->once())
      ->method('papaya')
      ->will($this->returnValue($papaya));
    $item = new \PapayaUiControlCollectionItem_TestProxy();
    $this->assertSame(
      $collection, $item->collection($collection)
    );
    $this->assertEquals(
      $papaya, $item->papaya()
    );
  }

  /**
  * @covers \PapayaUiControlCollectionItem::collection
  */
  public function testCollectionWithoutSetExpectingExpcetion() {
    $item = new \PapayaUiControlCollectionItem_TestProxy();
    $this->expectException(BadMethodCallException::class);
    $this->expectExceptionMessage('BadMethodCallException: Item ist not part of a collection.');
    $item->collection();
  }

  /**
  * @covers \PapayaUiControlCollectionItem::index
  */
  public function testIndexGetWithoutSet() {
    $item = new \PapayaUiControlCollectionItem_TestProxy();
    $this->assertSame(
      0, $item->index()
    );
  }

  /**
  * @covers \PapayaUiControlCollectionItem::index
  */
  public function testIndexSetWithInvalidValue() {
    $collection = $this->createMock(PapayaUiControlCollection::class);
    $collection
      ->expects($this->once())
      ->method('get')
      ->with(42)
      ->will($this->returnValue(new \PapayaUiControlCollectionItem_TestProxy()));
    $item = new \PapayaUiControlCollectionItem_TestProxy();
    $item->collection($collection);
    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('UnexpectedValueException: Index "42" does not match the collection item.');
    $item->index(42);
  }

  /**
  * @covers \PapayaUiControlCollectionItem::index
  */
  public function testIndex() {
    $item = new \PapayaUiControlCollectionItem_TestProxy();
    $collection = $this->createMock(PapayaUiControlCollection::class);
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

class PapayaUiControlCollectionItem_TestProxy extends PapayaUiControlCollectionItem {
  public function appendTo(PapayaXMlElement $parent) {
  }
}
