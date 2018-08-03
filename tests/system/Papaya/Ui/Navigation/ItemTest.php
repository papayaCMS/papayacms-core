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

class PapayaUiNavigationItemTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\UI\Navigation\Item\Text::__construct
  */
  public function testConstructor() {
    $item = new \Papaya\UI\Navigation\Item\Text('success', 42);
    $this->assertAttributeEquals(
      'success', '_sourceValue', $item
    );
    $this->assertAttributeEquals(
      42, '_sourceIndex', $item
    );
  }

  /**
  * @covers \Papaya\UI\Navigation\Item::appendTo
  */
  public function testAppendTo() {
    $document = new \Papaya\XML\Document();
    $parent = $document->appendElement('test');
    $reference = $this->createMock(\Papaya\UI\Reference::class);
    $reference
      ->expects($this->once())
      ->method('getRelative')
      ->will($this->returnValue('test.html'));
    $item = new \PapayaUiNavigationItem_TestProxy(NULL);
    $item->reference($reference);
    $this->assertInstanceOf(
      \Papaya\XML\Element::class, $item->appendTo($parent)
    );
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<test><link href="test.html"/></test>',
      $parent->saveXML()
    );
  }

  /**
  * @covers \Papaya\UI\Navigation\Item::appendTo
  */
  public function testAppendToWithSelectedItem() {
    $document = new \Papaya\XML\Document();
    $parent = $document->appendElement('test');
    $reference = $this->createMock(\Papaya\UI\Reference::class);
    $reference
      ->expects($this->once())
      ->method('getRelative')
      ->will($this->returnValue('test.html'));
    $item = new \PapayaUiNavigationItem_TestProxy(NULL);
    $item->selected(TRUE);
    $item->reference($reference);
    $this->assertInstanceOf(
      \Papaya\XML\Element::class, $item->appendTo($parent)
    );
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<test><link href="test.html" selected="selected"/></test>',
      $parent->saveXML()
    );
  }

  /**
  * @covers \Papaya\UI\Navigation\Item::selected
  */
  public function testSelectedSetToTrue() {
    $item = new \PapayaUiNavigationItem_TestProxy(NULL);
    $item->selected(TRUE);
    $this->assertTrue($item->selected());
  }

  /**
  * @covers \Papaya\UI\Navigation\Item::selected
  */
  public function testSelectedSetToFalse() {
    $item = new \PapayaUiNavigationItem_TestProxy(NULL);
    $item->selected(FALSE);
    $this->assertFalse($item->selected());
  }

  /**
  * @covers \Papaya\UI\Navigation\Item::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->createMock(\Papaya\UI\Reference::class);
    $item = new \PapayaUiNavigationItem_TestProxy(NULL);
    $this->assertSame(
      $reference, $item->reference($reference)
    );
  }

  /**
  * @covers \Papaya\UI\Navigation\Item::reference
  */
  public function testReferenceGetFromCollection() {
    $reference = $this->createMock(\Papaya\UI\Reference::class);
    $collection = $this->createMock(\Papaya\UI\Navigation\Items::class);
    $collection
      ->expects($this->once())
      ->method('reference')
      ->will($this->returnValue($reference));
    $item = new \PapayaUiNavigationItem_TestProxy(NULL);
    $item->collection($collection);
    $this->assertInstanceOf(\Papaya\UI\Reference::class, $item->reference());
    $this->assertNotSame($reference, $item->reference());
  }

  /**
  * @covers \Papaya\UI\Navigation\Item::reference
  */
  public function testReferenceImpliciteCreate() {
    $item = new \PapayaUiNavigationItem_TestProxy(NULL);
    $item->papaya($papaya = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      \Papaya\UI\Reference::class, $reference = $item->reference()
    );
    $this->assertSame(
      $papaya, $reference->papaya()
    );
  }

}

class PapayaUiNavigationItem_TestProxy extends \Papaya\UI\Navigation\Item {

}
