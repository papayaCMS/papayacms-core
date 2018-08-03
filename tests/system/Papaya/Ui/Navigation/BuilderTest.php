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

class PapayaUiNavigationBuilderTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Navigation\Builder::__construct
  * @covers \Papaya\Ui\Navigation\Builder::elements
  */
  public function testConstructorWithArray() {
    $builder = new \Papaya\Ui\Navigation\Builder(array('42'));
    $this->assertEquals(
      array('42'), $builder->elements()
    );
  }

  /**
  * @covers \Papaya\Ui\Navigation\Builder::__construct
  * @covers \Papaya\Ui\Navigation\Builder::elements
  */
  public function testConstructorWithIterator() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Iterator $iterator */
    $iterator = $this->createMock(Iterator::class);
    $builder = new \Papaya\Ui\Navigation\Builder($iterator);
    $this->assertSame(
      $iterator, $builder->elements()
    );
  }

  /**
  * @covers \Papaya\Ui\Navigation\Builder::__construct
  */
  public function testConstructorWithItemClass() {
    $builder = new \Papaya\Ui\Navigation\Builder(array(), \Papaya\Ui\Navigation\Item\Text::class);
    $this->assertAttributeEquals(
      \Papaya\Ui\Navigation\Item\Text::class, '_itemClass', $builder
    );
  }

  /**
  * @covers \Papaya\Ui\Navigation\Builder::__construct
  */
  public function testConstructorWithInvalidItemClassExpectingException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Class "stdClass" is not an subclass of "Papaya\Ui\Navigation\Item".');
    new \Papaya\Ui\Navigation\Builder(array(), stdClass::class);
  }

  /**
  * @covers \Papaya\Ui\Navigation\Builder::appendTo
  */
  public function testAppendTo() {
    $items = $this->createMock(\Papaya\Ui\Navigation\Items::class);
    $items
      ->expects($this->once())
      ->method('clear');
    $items
      ->expects($this->once())
      ->method('add')
      ->with($this->isInstanceOf(\Papaya\Ui\Navigation\Item::class));
    $items
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));

    $builder = new \Papaya\Ui\Navigation\Builder(array('1' => 'Item One'));
    $builder->papaya($this->mockPapaya()->application());
    $builder->items($items);
    $builder->getXml();
  }

  /**
  * @covers \Papaya\Ui\Navigation\Builder::appendTo
  */
  public function testAppendToWithCallbacks() {
    $callbacks = $this
      ->getMockBuilder(\Papaya\Ui\Navigation\Builder\Callbacks::class)
      ->disableOriginalConstructor()
      ->setMethods(
        array('onBeforeAppend', 'onAfterAppend', 'onCreateItem', 'onAfterAppendItem', '__isset')
      )
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('onBeforeAppend')
      ->with($this->isInstanceOf(\Papaya\Ui\Navigation\Items::class));
    $callbacks
      ->expects($this->once())
      ->method('onAfterAppend')
      ->with($this->isInstanceOf(\Papaya\Ui\Navigation\Items::class));
    $callbacks
      ->expects($this->once())
      ->method('__isset')
      ->with('onCreateItem')
      ->will($this->returnValue(TRUE));
    $callbacks
      ->expects($this->once())
      ->method('onCreateItem')
      ->with('Item One', 1)
      ->will($this->returnValue(new \Papaya\Ui\Navigation\Item\Text('')));
    $callbacks
      ->expects($this->once())
      ->method('onAfterAppendItem')
      ->with($this->isInstanceOf(\Papaya\Ui\Navigation\Item::class), 'Item One', 1);

    $items = $this->createMock(\Papaya\Ui\Navigation\Items::class);
    $items
      ->expects($this->once())
      ->method('clear');
    $items
      ->expects($this->once())
      ->method('add')
      ->with($this->isInstanceOf(\Papaya\Ui\Navigation\Item::class));
    $items
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));

    $builder = new \Papaya\Ui\Navigation\Builder(array('1' => 'Item One'));
    $builder->papaya($this->mockPapaya()->application());
    $builder->items($items);
    $builder->callbacks($callbacks);
    $builder->getXml();
  }

  /**
  * @covers \Papaya\Ui\Navigation\Builder::appendTo
  */
  public function testAppendToFromAnArray() {
    $builder = new \Papaya\Ui\Navigation\Builder(array('1' => 'Item One'));
    $builder->papaya($this->mockPapaya()->application());
    $this->assertEquals(
      /** @lang XML */'<links><link href="http://www.test.tld/index.html">Item One</link></links>',
      $builder->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Navigation\Builder::items
  */
  public function testItemsGetAfterSet() {
    $items = $this
      ->getMockBuilder(\Papaya\Ui\Navigation\Items::class)
      ->disableOriginalConstructor()
      ->getMock();
    $builder = new \Papaya\Ui\Navigation\Builder(array());
    $this->assertSame(
      $items, $builder->items($items)
    );
  }

  /**
  * @covers \Papaya\Ui\Navigation\Builder::items
  */
  public function testItemsGetImpliciteCreate() {
    $builder = new \Papaya\Ui\Navigation\Builder(array());
    $items = $builder->items();
    $this->assertInstanceOf(
      \Papaya\Ui\Navigation\Items::class, $items
    );
  }

  /**
  * @covers \Papaya\Ui\Navigation\Builder::callbacks
  */
  public function testCallbacksGetAfterSet() {
    $callbacks = $this
      ->getMockBuilder(\Papaya\Ui\Navigation\Builder\Callbacks::class)
      ->disableOriginalConstructor()
      ->getMock();
    $builder = new \Papaya\Ui\Navigation\Builder(array());
    $this->assertSame(
      $callbacks, $builder->callbacks($callbacks)
    );
  }

  /**
  * @covers \Papaya\Ui\Navigation\Builder::callbacks
  */
  public function testCallbacksGetImpliciteCreate() {
    $builder = new \Papaya\Ui\Navigation\Builder(array());
    $callbacks = $builder->callbacks();
    $this->assertInstanceOf(
      \Papaya\Ui\Navigation\Builder\Callbacks::class, $callbacks
    );
  }
}
