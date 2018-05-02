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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiNavigationBuilderTest extends PapayaTestCase {

  /**
  * @covers PapayaUiNavigationBuilder::__construct
  * @covers PapayaUiNavigationBuilder::elements
  */
  public function testConstructorWithArray() {
    $builder = new PapayaUiNavigationBuilder(array('42'));
    $this->assertEquals(
      array('42'), $builder->elements()
    );
  }

  /**
  * @covers PapayaUiNavigationBuilder::__construct
  * @covers PapayaUiNavigationBuilder::elements
  */
  public function testConstructorWithIterator() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Iterator $iterator */
    $iterator = $this->createMock(Iterator::class);
    $builder = new PapayaUiNavigationBuilder($iterator);
    $this->assertSame(
      $iterator, $builder->elements()
    );
  }

  /**
  * @covers PapayaUiNavigationBuilder::__construct
  */
  public function testConstructorWithItemClass() {
    $builder = new PapayaUiNavigationBuilder(array(), PapayaUiNavigationItemText::class);
    $this->assertAttributeEquals(
      PapayaUiNavigationItemText::class, '_itemClass', $builder
    );
  }

  /**
  * @covers PapayaUiNavigationBuilder::__construct
  */
  public function testConstructorWithInvalidItemClassExpectingException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Class "stdClass" is not an subclass of "PapayaUiNavigationItem".');
    new PapayaUiNavigationBuilder(array(), stdClass::class);
  }

  /**
  * @covers PapayaUiNavigationBuilder::appendTo
  */
  public function testAppendTo() {
    $items = $this->createMock(PapayaUiNavigationItems::class);
    $items
      ->expects($this->once())
      ->method('clear');
    $items
      ->expects($this->once())
      ->method('add')
      ->with($this->isInstanceOf(PapayaUiNavigationItem::class));
    $items
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));

    $builder = new PapayaUiNavigationBuilder(array('1' => 'Item One'));
    $builder->papaya($this->mockPapaya()->application());
    $builder->items($items);
    $builder->getXml();
  }

  /**
  * @covers PapayaUiNavigationBuilder::appendTo
  */
  public function testAppendToWithCallbacks() {
    $callbacks = $this
      ->getMockBuilder(PapayaUiNavigationBuilderCallbacks::class)
      ->disableOriginalConstructor()
      ->setMethods(
        array('onBeforeAppend', 'onAfterAppend', 'onCreateItem', 'onAfterAppendItem', '__isset')
      )
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('onBeforeAppend')
      ->with($this->isInstanceOf(PapayaUiNavigationItems::class));
    $callbacks
      ->expects($this->once())
      ->method('onAfterAppend')
      ->with($this->isInstanceOf(PapayaUiNavigationItems::class));
    $callbacks
      ->expects($this->once())
      ->method('__isset')
      ->with('onCreateItem')
      ->will($this->returnValue(TRUE));
    $callbacks
      ->expects($this->once())
      ->method('onCreateItem')
      ->with('Item One', 1)
      ->will($this->returnValue(new PapayaUiNavigationItemText('')));
    $callbacks
      ->expects($this->once())
      ->method('onAfterAppendItem')
      ->with($this->isInstanceOf(PapayaUiNavigationItem::class), 'Item One', 1);

    $items = $this->createMock(PapayaUiNavigationItems::class);
    $items
      ->expects($this->once())
      ->method('clear');
    $items
      ->expects($this->once())
      ->method('add')
      ->with($this->isInstanceOf(PapayaUiNavigationItem::class));
    $items
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));

    $builder = new PapayaUiNavigationBuilder(array('1' => 'Item One'));
    $builder->papaya($this->mockPapaya()->application());
    $builder->items($items);
    $builder->callbacks($callbacks);
    $builder->getXml();
  }

  /**
  * @covers PapayaUiNavigationBuilder::appendTo
  */
  public function testAppendToFromAnArray() {
    $builder = new PapayaUiNavigationBuilder(array('1' => 'Item One'));
    $builder->papaya($this->mockPapaya()->application());
    $this->assertEquals(
      '<links><link href="http://www.test.tld/index.html">Item One</link></links>',
      $builder->getXml()
    );
  }

  /**
  * @covers PapayaUiNavigationBuilder::items
  */
  public function testItemsGetAfterSet() {
    $items = $this
      ->getMockBuilder(PapayaUiNavigationItems::class)
      ->disableOriginalConstructor()
      ->getMock();
    $builder = new PapayaUiNavigationBuilder(array());
    $this->assertSame(
      $items, $builder->items($items)
    );
  }

  /**
  * @covers PapayaUiNavigationBuilder::items
  */
  public function testItemsGetImpliciteCreate() {
    $builder = new PapayaUiNavigationBuilder(array());
    $items = $builder->items();
    $this->assertInstanceOf(
      PapayaUiNavigationItems::class, $items
    );
  }

  /**
  * @covers PapayaUiNavigationBuilder::callbacks
  */
  public function testCallbacksGetAfterSet() {
    $callbacks = $this
      ->getMockBuilder(PapayaUiNavigationBuilderCallbacks::class)
      ->disableOriginalConstructor()
      ->getMock();
    $builder = new PapayaUiNavigationBuilder(array());
    $this->assertSame(
      $callbacks, $builder->callbacks($callbacks)
    );
  }

  /**
  * @covers PapayaUiNavigationBuilder::callbacks
  */
  public function testCallbacksGetImpliciteCreate() {
    $builder = new PapayaUiNavigationBuilder(array());
    $callbacks = $builder->callbacks();
    $this->assertInstanceOf(
      PapayaUiNavigationBuilderCallbacks::class, $callbacks
    );
  }
}
