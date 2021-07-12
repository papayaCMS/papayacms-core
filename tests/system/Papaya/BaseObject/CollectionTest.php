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

namespace Papaya\BaseObject;
require_once __DIR__.'/../../../bootstrap.php';

class CollectionTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\BaseObject\Collection::__construct
   */
  public function testConstructorWithClass() {
    $list = new Collection(\Papaya\TestFramework\TestCase::class);
    $this->assertEquals(
      \Papaya\TestFramework\TestCase::class,
      $list->getItemClass()
    );
  }

  /**
   * @covers \Papaya\BaseObject\Collection::__construct
   */
  public function testConstructorWithoutClass() {
    $list = new Collection();
    $this->assertEquals(
      \stdClass::class,
      $list->getItemClass()
    );
  }

  /**
   * @covers \Papaya\BaseObject\Collection::setItemClass
   */
  public function testSetItemClassWithClass() {
    $list = new Collection();
    $list->setItemClass(\Papaya\TestFramework\TestCase::class);
    $this->assertEquals(
      \Papaya\TestFramework\TestCase::class,
      $list->getItemClass()
    );
  }

  /**
   * @covers \Papaya\BaseObject\Collection::setItemClass
   */
  public function testSetItemClassWithInterface() {
    $list = new Collection();
    $list->setItemClass('Iterator');
    $this->assertEquals(
      'Iterator',
      $list->getItemClass()
    );
  }

  /**
   * @covers \Papaya\BaseObject\Collection::__construct
   * @covers \Papaya\BaseObject\Collection::setItemClass
   */
  public function testSetItemClassWithInvalidArgument() {
    $list = new Collection();
    $this->expectException(\InvalidArgumentException::class);
    $list->setItemClass('NONEXISTING_CLASSNAME');
  }

  /**
   * @covers \Papaya\BaseObject\Collection::setItemClass
   */
  public function testSetItemClassRemovesItems() {
    $list = new Collection();
    $list->add(new \stdClass());
    $list->setItemClass(\Papaya\TestFramework\TestCase::class);
    $this->assertEquals(
      array(),
      iterator_to_array($list)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Collection::getItemClass
   */
  public function testGetItemClass() {
    $list = new Collection(\Papaya\TestFramework\TestCase::class);
    $this->assertEquals(
      \Papaya\TestFramework\TestCase::class,
      $list->getItemClass()
    );
  }

  /**
   * @covers \Papaya\BaseObject\Collection::add
   */
  public function testAdd() {
    $item = new \stdClass();
    $list = new Collection(\stdClass::class);
    $this->assertSame(
      $list,
      $list->add($item)
    );
    $this->assertSame(
      array($item),
      iterator_to_array($list)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Collection::clear
   */
  public function testClear() {
    $list = new Collection(\stdClass::class);
    $list->add(new \stdClass());
    $list->clear();
    $this->assertSame(
      array(),
      iterator_to_array($list)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Collection::remove
   */
  public function testRemove() {
    $list = new Collection(\stdClass::class);
    $list->add(new \stdClass());
    $list->remove(0);
    $this->assertSame(
      array(),
      iterator_to_array($list)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Collection::isEmpty
   */
  public function testIsEmptyExpectingTrue() {
    $list = new Collection(\stdClass::class);
    $this->assertTrue($list->isEmpty());
  }

  /**
   * @covers \Papaya\BaseObject\Collection::isEmpty
   */
  public function testIsEmptyExpectingFalse() {
    $list = new Collection(\stdClass::class);
    $list->add(new \stdClass());
    $this->assertFalse($list->isEmpty());
  }

  /**
   * @covers \Papaya\BaseObject\Collection::count
   */
  public function testIsEmptyExpectingZero() {
    $list = new Collection(\stdClass::class);
    $this->assertSame(
      0,
      $list->count()
    );
  }

  /**
   * @covers \Papaya\BaseObject\Collection::count
   */
  public function testIsEmptyExpectingOne() {
    $list = new Collection(\stdClass::class);
    $list->add(new \stdClass());
    $this->assertSame(
      1,
      $list->count()
    );
  }

  /**
   * @covers \Papaya\BaseObject\Collection::current
   */
  public function testCurrentExpectingItem() {
    $item = new \stdClass();
    $list = new Collection(\stdClass::class);
    $list->add($item);
    $this->assertSame(
      $item,
      $list->current()
    );
  }

  /**
   * @covers \Papaya\BaseObject\Collection::current
   */
  public function testCurrentExpectingNull() {
    $list = new Collection(\stdClass::class);
    $this->assertFalse(
      $list->current()
    );
  }

  /**
   * @covers \Papaya\BaseObject\Collection::key
   */
  public function testKeyExpectingZero() {
    $item = new \stdClass();
    $list = new Collection(\stdClass::class);
    $list->add($item);
    $this->assertSame(
      0,
      $list->key()
    );
  }

  /**
   * @covers \Papaya\BaseObject\Collection::key
   */
  public function testKeyExpectingNull() {
    $list = new Collection(\stdClass::class);
    $this->assertNull(
      $list->key()
    );
  }

  /**
   * @covers \Papaya\BaseObject\Collection::next
   */
  public function testNext() {
    $list = new Collection(\stdClass::class);
    $list->add(new \stdClass());
    $list->add(new \stdClass());
    $list->next();
    $this->assertSame(
      1,
      $list->key()
    );
  }

  /**
   * @covers \Papaya\BaseObject\Collection::rewind
   */
  public function testRewind() {
    $list = new Collection(\stdClass::class);
    $list->add(new \stdClass());
    $list->add(new \stdClass());
    $list->next();
    $list->rewind();
    $this->assertSame(
      0,
      $list->key()
    );
  }


  /**
   * @covers \Papaya\BaseObject\Collection::valid
   */
  public function testValidExpectingTrue() {
    $list = new Collection(\stdClass::class);
    $list->add(new \stdClass());
    $this->assertTrue(
      $list->valid()
    );
  }

  /**
   * @covers \Papaya\BaseObject\Collection::valid
   */
  public function testValidExpectingFalse() {
    $list = new Collection(\stdClass::class);
    $this->assertFalse(
      $list->valid()
    );
  }

  /**
   * @covers \Papaya\BaseObject\Collection::offsetExists
   */
  public function testOffsetExistsExpectingTrue() {
    $list = new Collection(\stdClass::class);
    $list->add(new \stdClass());
    $this->assertTrue(
      $list->offsetExists(0)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Collection::offsetExists
   */
  public function testOffsetExistsExpectingFalse() {
    $list = new Collection(\stdClass::class);
    $this->assertFalse(
      $list->offsetExists(99)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Collection::offsetGet
   */
  public function testOffsetGet() {
    $item = new \stdClass();
    $list = new Collection(\stdClass::class);
    $list->add($item);
    $this->assertSame(
      $item,
      $list->offsetGet(0)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Collection::offsetSet
   * @covers \Papaya\BaseObject\Collection::prepareItem
   */
  public function testOffsetSet() {
    $item = new \stdClass();
    $list = new Collection(\stdClass::class);
    $list->offsetSet(NULL, $item);
    $this->assertSame(
      array($item),
      iterator_to_array($list)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Collection::offsetSet
   */
  public function testOffsetSetWithExistingIndex() {
    $item = new \stdClass();
    $list = new Collection(\stdClass::class);
    $list->offsetSet(NULL, new \stdClass());
    $list->offsetSet(0, $item);
    $this->assertSame(
      array($item),
      iterator_to_array($list)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Collection::offsetSet
   */
  public function testOffsetSetWithInvalidIndexExpectingException() {
    $list = new Collection(\stdClass::class);
    $this->expectException(\InvalidArgumentException::class);
    $list->offsetSet(99, new \stdClass);
  }

  /**
   * @covers \Papaya\BaseObject\Collection::offsetSet
   */
  public function testOffsetSetWithInvalidValueExpectingException() {
    $list = new Collection(\stdClass::class);
    $this->expectException(\InvalidArgumentException::class);
    $list->offsetSet(99, 'A String');
  }

  /**
   * @covers \Papaya\BaseObject\Collection::offsetUnset
   */
  public function testOffsetUnset() {
    $item = new \stdClass();
    $list = new Collection(\stdClass::class);
    $list->offsetSet(NULL, new \stdClass());
    $list->offsetSet(NULL, $item);
    $list->offsetUnset(0);
    $this->assertSame(
      array($item),
      iterator_to_array($list)
    );
  }

  public function testCloneDuplicatesItems() {
    $item = new \stdClass();
    $list = new Collection(\stdClass::class);
    $list[] = $item;
    $clonedList = clone $list;
    $this->assertEquals($list, $clonedList);
    $this->assertSame($item, $list[0]);
    $this->assertNotSame($item, $clonedList[0]);
  }
  public function testKeys() {
    $list = new Collection(\stdClass::class, Collection::MODE_ASSOCIATIVE);
    $list['one'] = new \stdClass();
    $list['two'] = new \stdClass();
    $list['three'] = new \stdClass();
    $this->assertSame(['one', 'two', 'three'], $list->keys());
  }

  public function testFirst() {
    $list = new Collection(\stdClass::class);
    $list[] = $item = new \stdClass();
    $list[] = new \stdClass();
    $list[] = new \stdClass();
    $this->assertSame($item, $list->first());
  }

  public function testLast() {
    $list = new Collection(\stdClass::class);
    $list[] = new \stdClass();
    $list[] = new \stdClass();
    $list[] = $item = new \stdClass();
    $this->assertSame($item, $list->last());
  }
}
