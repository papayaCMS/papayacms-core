<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiHierarchyMenuTest extends PapayaTestCase {

  /**
  * @covers PapayaUiHierarchyMenu::appendTo
  */
  public function testAppendTo() {
    $items = $this->createMock(PapayaUiHierarchyItems::class);
    $items
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $items
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));

    $menu = new PapayaUiHierarchyMenu();
    $menu->items($items);

    $this->assertAppendedXmlEqualsXmlFragment(
      '<hierarchy-menu/>', $menu
    );
  }

  /**
  * @covers PapayaUiHierarchyMenu::appendTo
  */
  public function testAppendToWithoutItemsExpectingEmptyString() {
    $items = $this->createMock(PapayaUiHierarchyItems::class);
    $items
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(0));
    $menu = new PapayaUiHierarchyMenu();
    $menu->items($items);

    $this->assertAppendedXmlEqualsXmlFragment(
      '', $menu
    );
  }

  /**
  * @covers PapayaUiHierarchyMenu::items
  */
  public function testItemsGetAfterSet() {
    $menu = new PapayaUiHierarchyMenu();
    $items = $this->createMock(PapayaUiHierarchyItems::class);
    $this->assertSame(
      $items, $menu->items($items)
    );
  }

  /**
  * @covers PapayaUiHierarchyMenu::items
  */
  public function testItemsGetWithImpliciteCreate() {
    $menu = new PapayaUiHierarchyMenu();
    $menu->papaya($papaya = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      PapayaUiHierarchyItems::class, $menu->items()
    );
    $this->assertSame(
      $papaya, $menu->papaya()
    );
  }
}
