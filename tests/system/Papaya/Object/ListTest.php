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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaObjectListTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\BaseObject\Collection::__construct
  */
  public function testConstructorWithClass() {
    $list = new \Papaya\BaseObject\Collection(\PapayaTestCase::class);
    $this->assertAttributeEquals(
      \PapayaTestCase::class,
      '_itemClass',
      $list
    );
  }
  /**
  * @covers \Papaya\BaseObject\Collection::__construct
  */
  public function testConstructorWithoutClass() {
    $list = new \Papaya\BaseObject\Collection();
    $this->assertAttributeEquals(
      \stdClass::class,
      '_itemClass',
      $list
    );
  }

  /**
  * @covers \Papaya\BaseObject\Collection::setItemClass
  */
  public function testSetItemClassWithClass() {
    $list = new \Papaya\BaseObject\Collection();
    $list->setItemClass(\PapayaTestCase::class);
    $this->assertAttributeEquals(
      \PapayaTestCase::class,
      '_itemClass',
      $list
    );
  }

  /**
  * @covers \Papaya\BaseObject\Collection::setItemClass
  */
  public function testSetItemClassWithInterface() {
    $list = new \Papaya\BaseObject\Collection();
    $list->setItemClass('Iterator');
    $this->assertAttributeEquals(
      'Iterator',
      '_itemClass',
      $list
    );
  }

  /**
  * @covers \Papaya\BaseObject\Collection::__construct
  * @covers \Papaya\BaseObject\Collection::setItemClass
  */
  public function testSetItemClassWithInvalidArgument() {
    $list = new \Papaya\BaseObject\Collection();
    $this->expectException(\InvalidArgumentException::class);
    $list->setItemClass('NONEXISTING_CLASSNAME');
  }

  /**
  * @covers \Papaya\BaseObject\Collection::setItemClass
  */
  public function testSetItemClassRemovesItems() {
    $list = new \Papaya\BaseObject\Collection();
    $list->add(new \stdClass());
    $list->setItemClass(\PapayaTestCase::class);
    $this->assertAttributeEquals(
      array(),
      '_items',
      $list
    );
  }

  /**
  * @covers \Papaya\BaseObject\Collection::getItemClass
  */
  public function testGetItemClass() {
    $list = new \Papaya\BaseObject\Collection(\PapayaTestCase::class);
    $this->assertEquals(
      \PapayaTestCase::class,
      $list->getItemClass()
    );
  }

  /**
  * @covers \Papaya\BaseObject\Collection::add
  */
  public function testAdd() {
    $item = new \stdClass();
    $list = new \Papaya\BaseObject\Collection(\stdClass::class);
    $this->assertSame(
      $list,
      $list->add($item)
    );
    $this->assertAttributeSame(
      array($item),
      '_items',
      $list
    );
  }

  /**
  * @covers \Papaya\BaseObject\Collection::clear
  */
  public function testClear() {
    $list = new \Papaya\BaseObject\Collection(\stdClass::class);
    $list->add(new \stdClass());
    $list->clear();
    $this->assertAttributeSame(
      array(),
      '_items',
      $list
    );
  }

  /**
  * @covers \Papaya\BaseObject\Collection::remove
  */
  public function testRemove() {
    $list = new \Papaya\BaseObject\Collection(\stdClass::class);
    $list->add(new \stdClass());
    $list->remove(0);
    $this->assertAttributeSame(
      array(),
      '_items',
      $list
    );
  }

  /**
  * @covers \Papaya\BaseObject\Collection::isEmpty
  */
  public function testIsEmptyExpectingTrue() {
    $list = new \Papaya\BaseObject\Collection(\stdClass::class);
    $this->assertTrue($list->isEmpty());
  }

  /**
  * @covers \Papaya\BaseObject\Collection::isEmpty
  */
  public function testIsEmptyExpectingFalse() {
    $list = new \Papaya\BaseObject\Collection(\stdClass::class);
    $list->add(new \stdClass());
    $this->assertFalse($list->isEmpty());
  }

  /**
  * @covers \Papaya\BaseObject\Collection::count
  */
  public function testIsEmptyExpectingZero() {
    $list = new \Papaya\BaseObject\Collection(\stdClass::class);
    $this->assertSame(
      0,
      $list->count()
    );
  }

  /**
  * @covers \Papaya\BaseObject\Collection::count
  */
  public function testIsEmptyExpectingOne() {
    $list = new \Papaya\BaseObject\Collection(\stdClass::class);
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
    $list = new \Papaya\BaseObject\Collection(\stdClass::class);
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
    $list = new \Papaya\BaseObject\Collection(\stdClass::class);
    $this->assertFalse(
      $list->current()
    );
  }

  /**
  * @covers \Papaya\BaseObject\Collection::key
  */
  public function testKeyExpectingZero() {
    $item = new \stdClass();
    $list = new \Papaya\BaseObject\Collection(\stdClass::class);
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
    $list = new \Papaya\BaseObject\Collection(\stdClass::class);
    $this->assertNull(
      $list->key()
    );
  }

  /**
  * @covers \Papaya\BaseObject\Collection::next
  */
  public function testNext() {
    $list = new \Papaya\BaseObject\Collection(\stdClass::class);
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
    $list = new \Papaya\BaseObject\Collection(\stdClass::class);
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
    $list = new \Papaya\BaseObject\Collection(\stdClass::class);
    $list->add(new \stdClass());
    $this->assertTrue(
      $list->valid()
    );
  }

  /**
  * @covers \Papaya\BaseObject\Collection::valid
  */
  public function testValidExpectingFalse() {
    $list = new \Papaya\BaseObject\Collection(\stdClass::class);
    $this->assertFalse(
      $list->valid()
    );
  }

  /**
  * @covers \Papaya\BaseObject\Collection::offsetExists
  */
  public function testOffsetExistsExpectingTrue() {
    $list = new \Papaya\BaseObject\Collection(\stdClass::class);
    $list->add(new \stdClass());
    $this->assertTrue(
      $list->offsetExists(0)
    );
  }

  /**
  * @covers \Papaya\BaseObject\Collection::offsetExists
  */
  public function testOffsetExistsExpectingFalse() {
    $list = new \Papaya\BaseObject\Collection(\stdClass::class);
    $this->assertFalse(
      $list->offsetExists(99)
    );
  }

  /**
  * @covers \Papaya\BaseObject\Collection::offsetGet
  */
  public function testOffsetGet() {
    $item = new \stdClass();
    $list = new \Papaya\BaseObject\Collection(\stdClass::class);
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
    $list = new \Papaya\BaseObject\Collection(\stdClass::class);
    $list->offsetSet(NULL, $item);
    $this->assertAttributeSame(
      array($item),
      '_items',
      $list
    );
  }

  /**
  * @covers \Papaya\BaseObject\Collection::offsetSet
  */
  public function testOffsetSetWithExistingIndex() {
    $item = new \stdClass();
    $list = new \Papaya\BaseObject\Collection(\stdClass::class);
    $list->offsetSet(NULL, new \stdClass());
    $list->offsetSet(0, $item);
    $this->assertAttributeSame(
      array($item),
      '_items',
      $list
    );
  }

  /**
  * @covers \Papaya\BaseObject\Collection::offsetSet
  */
  public function testOffsetSetWithInvalidIndexExpectingException() {
    $list = new \Papaya\BaseObject\Collection(\stdClass::class);
    $this->expectException(\InvalidArgumentException::class);
    $list->offsetSet(99, new \stdClass);
  }

  /**
  * @covers \Papaya\BaseObject\Collection::offsetSet
  */
  public function testOffsetSetWithInvalidValueExpectingException() {
    $list = new \Papaya\BaseObject\Collection(\stdClass::class);
    $this->expectException(\InvalidArgumentException::class);
    $list->offsetSet(99, 'A String');
  }

  /**
  * @covers \Papaya\BaseObject\Collection::offsetUnset
  */
  public function testOffsetUnset() {
    $item = new \stdClass();
    $list = new \Papaya\BaseObject\Collection(\stdClass::class);
    $list->offsetSet(NULL, new \stdClass());
    $list->offsetSet(NULL, $item);
    $list->offsetUnset(0);
    $this->assertAttributeSame(
      array($item),
      '_items',
      $list
    );
  }
}
