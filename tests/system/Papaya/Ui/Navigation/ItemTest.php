<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiNavigationItemTest extends PapayaTestCase {

  /**
  * @covers PapayaUiNavigationItemText::__construct
  */
  public function testConstructor() {
    $item = new PapayaUiNavigationItemText('success', 42);
    $this->assertAttributeEquals(
      'success', '_sourceValue', $item
    );
    $this->assertAttributeEquals(
      42, '_sourceIndex', $item
    );
  }

  /**
  * @covers PapayaUiNavigationItem::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $parent = $dom->appendElement('test');
    $reference = $this->createMock(PapayaUiReference::class);
    $reference
      ->expects($this->once())
      ->method('getRelative')
      ->will($this->returnValue('test.html'));
    $item = new PapayaUiNavigationItem_TestProxy(NULL);
    $item->reference($reference);
    $this->assertInstanceOf(
      PapayaXmlElement::class, $item->appendTo($parent)
    );
    $this->assertEquals(
      '<test><link href="test.html"/></test>',
      $parent->saveXml()
    );
  }

  /**
  * @covers PapayaUiNavigationItem::appendTo
  */
  function testAppendToWithSelectedItem() {
    $dom = new PapayaXmlDocument();
    $parent = $dom->appendElement('test');
    $reference = $this->createMock(PapayaUiReference::class);
    $reference
      ->expects($this->once())
      ->method('getRelative')
      ->will($this->returnValue('test.html'));
    $item = new PapayaUiNavigationItem_TestProxy(NULL);
    $item->selected(TRUE);
    $item->reference($reference);
    $this->assertInstanceOf(
      PapayaXmlElement::class, $item->appendTo($parent)
    );
    $this->assertEquals(
      '<test><link href="test.html" selected="selected"/></test>',
      $parent->saveXml()
    );
  }

  /**
  * @covers PapayaUiNavigationItem::selected
  */
  public function testSelectedSetToTrue() {
    $item = new PapayaUiNavigationItem_TestProxy(NULL);
    $item->selected(TRUE);
    $this->assertTrue($item->selected());
  }

  /**
  * @covers PapayaUiNavigationItem::selected
  */
  public function testSelectedSetToFalse() {
    $item = new PapayaUiNavigationItem_TestProxy(NULL);
    $item->selected(FALSE);
    $this->assertFalse($item->selected());
  }

  /**
  * @covers PapayaUiNavigationItem::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->createMock(PapayaUiReference::class);
    $item = new PapayaUiNavigationItem_TestProxy(NULL);
    $this->assertSame(
      $reference, $item->reference($reference)
    );
  }

  /**
  * @covers PapayaUiNavigationItem::reference
  */
  public function testReferenceGetFromCollection() {
    $reference = $this->createMock(PapayaUiReference::class);
    $collection = $this->createMock(PapayaUiNavigationItems::class);
    $collection
      ->expects($this->once())
      ->method('reference')
      ->will($this->returnValue($reference));
    $item = new PapayaUiNavigationItem_TestProxy(NULL);
    $item->collection($collection);
    $this->assertInstanceOf(PapayaUiReference::class, $item->reference());
    $this->assertNotSame($reference, $item->reference());
  }

  /**
  * @covers PapayaUiNavigationItem::reference
  */
  public function testReferenceImpliciteCreate() {
    $item = new PapayaUiNavigationItem_TestProxy(NULL);
    $item->papaya($papaya = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      PapayaUiReference::class, $reference = $item->reference()
    );
    $this->assertSame(
      $papaya, $reference->papaya()
    );
  }

}

class PapayaUiNavigationItem_TestProxy extends PapayaUiNavigationItem {

}
