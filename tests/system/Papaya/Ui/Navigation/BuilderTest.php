<?php
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
    $builder = new PapayaUiNavigationBuilder($iterator = $this->getMock('Iterator'));
    $this->assertSame(
      $iterator, $builder->elements()
    );
  }

  /**
  * @covers PapayaUiNavigationBuilder::__construct
  */
  public function testConstructorWithItemClass() {
    $builder = new PapayaUiNavigationBuilder(array(), 'PapayaUiNavigationItemText');
    $this->assertAttributeEquals(
      'PapayaUiNavigationItemText', '_itemClass', $builder
    );
  }

  /**
  * @covers PapayaUiNavigationBuilder::__construct
  */
  public function testConstructorWithInvalidItemClassExpectingException() {
    $this->setExpectedException(
      'InvalidArgumentException',
      'Class "stdClass" is not an subclass of "PapayaUiNavigationItem".'
    );
    $builder = new PapayaUiNavigationBuilder(array(), 'stdClass');
  }

  /**
  * @covers PapayaUiNavigationBuilder::appendTo
  */
  public function testAppendTo() {
    $items = $this->getMock('PapayaUiNavigationItems');
    $items
      ->expects($this->once())
      ->method('clear');
    $items
      ->expects($this->once())
      ->method('add')
      ->with($this->isInstanceOf('PapayaUiNavigationItem'));
    $items
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));

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
      ->getMockBuilder('PapayaUiNavigationBuilderCallbacks')
      ->disableOriginalConstructor()
      ->setMethods(
        array('onBeforeAppend', 'onAfterAppend', 'onCreateItem', 'onAfterAppendItem', '__isset')
      )
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('onBeforeAppend')
      ->with($this->isInstanceOf('PapayaUiNavigationItems'));
    $callbacks
      ->expects($this->once())
      ->method('onAfterAppend')
      ->with($this->isInstanceOf('PapayaUiNavigationItems'));
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
      ->with($this->isInstanceOf('PapayaUiNavigationItem'), 'Item One', 1);

    $items = $this->getMock('PapayaUiNavigationItems');
    $items
      ->expects($this->once())
      ->method('clear');
    $items
      ->expects($this->once())
      ->method('add')
      ->with($this->isInstanceOf('PapayaUiNavigationItem'));
    $items
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));

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
      ->getMockBuilder('PapayaUiNavigationItems')
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
      'PapayaUiNavigationItems', $items
    );
  }

  /**
  * @covers PapayaUiNavigationBuilder::callbacks
  */
  public function testCallbacksGetAfterSet() {
    $callbacks = $this
      ->getMockBuilder('PapayaUiNavigationBuilderCallbacks')
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
      'PapayaUiNavigationBuilderCallbacks', $callbacks
    );
  }
}
