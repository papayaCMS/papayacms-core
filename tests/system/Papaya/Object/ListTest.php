<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaObjectListTest extends PapayaTestCase {

  /**
  * @covers PapayaObjectList::__construct
  */
  public function testContructorWithClass() {
    $list = new PapayaObjectList('PapayaTestCase');
    $this->assertAttributeEquals(
      'PapayaTestCase',
      '_itemClass',
      $list
    );
  }
  /**
  * @covers PapayaObjectList::__construct
  */
  public function testContructorWithoutClass() {
    $list = new PapayaObjectList();
    $this->assertAttributeEquals(
      'stdClass',
      '_itemClass',
      $list
    );
  }

  /**
  * @covers PapayaObjectList::setItemClass
  */
  public function testSetItemClassWithClass() {
    $list = new PapayaObjectList();
    $list->setItemClass('PapayaTestCase');
    $this->assertAttributeEquals(
      'PapayaTestCase',
      '_itemClass',
      $list
    );
  }

  /**
  * @covers PapayaObjectList::setItemClass
  */
  public function testSetItemClassWithInterface() {
    $list = new PapayaObjectList();
    $list->setItemClass('Iterator');
    $this->assertAttributeEquals(
      'Iterator',
      '_itemClass',
      $list
    );
  }

  /**
  * @covers PapayaObjectList::__construct
  * @covers PapayaObjectList::setItemClass
  */
  public function testSetItemClassWithInvalidArgument() {
    $list = new PapayaObjectList();
    $this->setExpectedException('InvalidArgumentException');
    $list->setItemClass('NONEXISTING_CLASSNAME');
  }

  /**
  * @covers PapayaObjectList::setItemClass
  */
  public function testSetItemClassRemovesItems() {
    $list = new PapayaObjectList();
    $list->add(new stdClass());
    $list->setItemClass('PapayaTestCase');
    $this->assertAttributeEquals(
      array(),
      '_items',
      $list
    );
  }

  /**
  * @covers PapayaObjectList::getItemClass
  * depends testContructorWithClass
  */
  public function testGetItemClass() {
    $list = new PapayaObjectList('PapayaTestCase');
    $this->assertEquals(
      'PapayaTestCase',
      $list->getItemClass()
    );
  }

  /**
  * @covers PapayaObjectList::add
  */
  public function testAdd() {
    $item = new stdClass();
    $list = new PapayaObjectList('stdClass');
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
  * @covers PapayaObjectList::clear
  */
  public function testClear() {
    $list = new PapayaObjectList('stdClass');
    $list->add(new stdClass());
    $list->clear();
    $this->assertAttributeSame(
      array(),
      '_items',
      $list
    );
  }

  /**
  * @covers PapayaObjectList::remove
  */
  public function testRemove() {
    $list = new PapayaObjectList('stdClass');
    $list->add(new stdClass());
    $list->remove(0);
    $this->assertAttributeSame(
      array(),
      '_items',
      $list
    );
  }

  /**
  * @covers PapayaObjectList::isEmpty
  */
  public function testIsEmptyExpectingTrue() {
    $list = new PapayaObjectList('stdClass');
    $this->assertTrue($list->isEmpty());
  }

  /**
  * @covers PapayaObjectList::isEmpty
  */
  public function testIsEmptyExpectingFalse() {
    $list = new PapayaObjectList('stdClass');
    $list->add(new stdClass());
    $this->assertFalse($list->isEmpty());
  }

  /**
  * @covers PapayaObjectList::count
  */
  public function testIsEmptyExpectingZero() {
    $list = new PapayaObjectList('stdClass');
    $this->assertSame(
      0,
      $list->count()
    );
  }

  /**
  * @covers PapayaObjectList::count
  */
  public function testIsEmptyExpectingOne() {
    $list = new PapayaObjectList('stdClass');
    $list->add(new stdClass());
    $this->assertSame(
      1,
      $list->count()
    );
  }

  /**
  * @covers PapayaObjectList::current
  */
  public function testCurrentExpectingItem() {
    $item = new stdClass();
    $list = new PapayaObjectList('stdClass');
    $list->add($item);
    $this->assertSame(
      $item,
      $list->current()
    );
  }

  /**
  * @covers PapayaObjectList::current
  */
  public function testCurrentExpectingNull() {
    $list = new PapayaObjectList('stdClass');
    $this->assertFalse(
      $list->current()
    );
  }

  /**
  * @covers PapayaObjectList::key
  */
  public function testKeyExpectingZero() {
    $item = new stdClass();
    $list = new PapayaObjectList('stdClass');
    $list->add($item);
    $this->assertSame(
      0,
      $list->key()
    );
  }

  /**
  * @covers PapayaObjectList::key
  */
  public function testKeyExpectingNull() {
    $list = new PapayaObjectList('stdClass');
    $this->assertNull(
      $list->key()
    );
  }

  /**
  * @covers PapayaObjectList::next
  */
  public function testNext() {
    $list = new PapayaObjectList('stdClass');
    $list->add(new stdClass());
    $list->add(new stdClass());
    $list->next();
    $this->assertSame(
      1,
      $list->key()
    );
  }

  /**
  * @covers PapayaObjectList::rewind
  */
  public function testRewind() {
    $list = new PapayaObjectList('stdClass');
    $list->add(new stdClass());
    $list->add(new stdClass());
    $list->next();
    $list->rewind();
    $this->assertSame(
      0,
      $list->key()
    );
  }


  /**
  * @covers PapayaObjectList::valid
  */
  public function testValidExpectingTrue() {
    $list = new PapayaObjectList('stdClass');
    $list->add(new stdClass());
    $this->assertTrue(
      $list->valid()
    );
  }

  /**
  * @covers PapayaObjectList::valid
  */
  public function testValidExpectingFalse() {
    $list = new PapayaObjectList('stdClass');
    $this->assertFalse(
      $list->valid()
    );
  }

  /**
  * @covers PapayaObjectList::offsetExists
  */
  public function testOffsetExistsExpectingTrue() {
    $list = new PapayaObjectList('stdClass');
    $list->add(new stdClass());
    $this->assertTrue(
      $list->offsetExists(0)
    );
  }

  /**
  * @covers PapayaObjectList::offsetExists
  */
  public function testOffsetExistsExpectingFalse() {
    $list = new PapayaObjectList('stdClass');
    $this->assertFalse(
      $list->offsetExists(99)
    );
  }

  /**
  * @covers PapayaObjectList::offsetGet
  */
  public function testOffsetGet() {
    $item = new stdClass();
    $list = new PapayaObjectList('stdClass');
    $list->add($item);
    $this->assertSame(
      $item,
      $list->offsetGet(0)
    );
  }

  /**
  * @covers PapayaObjectList::offsetSet
  * @covers PapayaObjectList::prepareItem
  */
  public function testOffsetSet() {
    $item = new stdClass();
    $list = new PapayaObjectList('stdClass');
    $list->offsetSet(NULL, $item);
    $this->assertAttributeSame(
      array($item),
      '_items',
      $list
    );
  }

  /**
  * @covers PapayaObjectList::offsetSet
  */
  public function testOffsetSetWithExistingIndex() {
    $item = new stdClass();
    $list = new PapayaObjectList('stdClass');
    $list->offsetSet(NULL, new stdClass());
    $list->offsetSet(0, $item);
    $this->assertAttributeSame(
      array($item),
      '_items',
      $list
    );
  }

  /**
  * @covers PapayaObjectList::offsetSet
  */
  public function testOffsetSetWithInvalidIndexExpectingException() {
    $list = new PapayaObjectList('stdClass');
    $this->setExpectedException('InvalidArgumentException');
    $list->offsetSet(99, new stdClass);
  }

  /**
  * @covers PapayaObjectList::offsetSet
  */
  public function testOffsetSetWithInvalidValueExpectingException() {
    $list = new PapayaObjectList('stdClass');
    $this->setExpectedException('InvalidArgumentException');
    $list->offsetSet(99, 'A String');
  }

  /**
  * @covers PapayaObjectList::offsetUnset
  */
  public function testOffsetUnset() {
    $item = new stdClass();
    $list = new PapayaObjectList('stdClass');
    $list->offsetSet(NULL, new stdClass());
    $list->offsetSet(NULL, $item);
    $list->offsetUnset(0);
    $this->assertAttributeSame(
      array($item),
      '_items',
      $list
    );
  }
}
