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
    /** @lang XML */'<hierarchy-menu/>', $menu
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
