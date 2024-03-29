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

namespace Papaya\UI\ListView;
require_once __DIR__.'/../../../../bootstrap.php';

/**
 * @covers \Papaya\UI\ListView\Item
 */
class ItemTest extends \Papaya\TestFramework\TestCase {

  public function testPropertyActionParameters() {
    $item = new Item('', '');
    $item->actionParameters = array('id' => '42');
    $this->assertEquals(
      array('id' => '42'), $item->actionParameters
    );
  }

  public function testPropertyIndentation() {
    $item = new Item('', '');
    $item->indentation = 2;
    $this->assertEquals(
      2, $item->indentation
    );
  }

  public function testPropertyIndentationWithNegativeValueExpectingException() {
    $item = new Item('', '');
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('InvalidArgumentException: $indentation must be greater or equal zero.');
    $item->indentation = -2;
  }

  public function testGetListView() {
    $listview = $this->createMock(\Papaya\UI\ListView::class);
    $items = $this
      ->getMockBuilder(Items::class)
      ->setConstructorArgs(array($listview))
      ->getMock();
    $items
      ->expects($this->once())
      ->method('owner')
      ->will($this->returnValue($listview));
    $item = new Item('', '');
    $item->collection($items);
    $this->assertInstanceOf(
      \Papaya\UI\ListView::class, $item->getListView()
    );
  }

  public function testCollectionGetAfterSet() {
    $items = $this
      ->getMockBuilder(Items::class)
      ->disableOriginalConstructor()
      ->getMock();
    $item = new Item('', '');
    $item->collection($items);
    $this->assertSame(
      $items, $item->collection()
    );
  }

  public function testSubitemsGetAfterSet() {
    $item = new Item('', '');
    $subitems = $this
      ->getMockBuilder(SubItems::class)
      ->setConstructorArgs(array($item))
      ->getMock();
    $subitems
      ->expects($this->once())
      ->method('owner')
      ->with($this->isInstanceOf(Item::class));
    $this->assertSame(
      $subitems, $item->subitems($subitems)
    );
  }

  public function testSubitemsImplicitCreate() {
    $item = new Item('', '');
    $this->assertInstanceOf(
      SubItems::class, $item->subitems()
    );
    $this->assertSame(
      $item, $item->subitems()->owner()
    );
  }

  public function testNodeGetAfterSet() {
    $item = new Item('', '');
    $node = $this
      ->getMockBuilder(Item\Node::class)
      ->setConstructorArgs(array($item))
      ->getMock();
    $this->assertSame(
      $node, $item->node($node)
    );
  }

  public function testNodeImplicitCreate() {
    $item = new Item('', '');
    $this->assertInstanceOf(
      Item\Node::class, $item->node()
    );
    $this->assertSame(
      $item, $item->node()->item
    );
  }

  public function testReferenceGetAfterSet() {
    $item = new Item('', '');
    $item->reference($reference = $this->createMock(\Papaya\UI\Reference::class));
    $this->assertSame($reference, $item->reference());
  }


  public function testReferenceGetImplicitCreate() {
    $item = new Item('', '', array('foo' => 'bar'));
    $item->papaya($this->mockPapaya()->application());
    $reference = $item->reference();
    $this->assertSame($reference->papaya(), $item->papaya());
    $this->assertEquals(array('foo' => 'bar'), $item->actionParameters);
  }

  /**
   * @covers \Papaya\UI\ListView\Item::reference
   */
  public function testReferenceGetFromCollection() {
    $reference = $this->createMock(\Papaya\UI\Reference::class);
    $listview = $this->createMock(\Papaya\UI\ListView::class);
    $collection = $this
      ->getMockBuilder(Items::class)
      ->setConstructorArgs(array($listview))
      ->getMock();
    $collection
      ->expects($this->once())
      ->method('reference')
      ->will($this->returnValue($reference));
    $collection
      ->expects($this->once())
      ->method('owner')
      ->will($this->returnValue($listview));
    $item = new Item('', '');
    $item->collection($collection);
    $this->assertInstanceOf(\Papaya\UI\Reference::class, $item->reference());
    $this->assertNotSame($reference, $item->reference());
  }

  public function testAppendTo() {
    $item = new Item('image', 'caption');
    $node = $this
      ->getMockBuilder(Item\Node::class)
      ->setConstructorArgs(array($item))
      ->getMock();
    $node
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));
    $subitems = $this
      ->getMockBuilder(SubItems::class)
      ->setConstructorArgs(array($item))
      ->getMock();
    $subitems
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));
    $item->node($node);
    $item->subitems($subitems);
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<listitem title="caption" image="test.gif"/>',
      $item->getXML()
    );
  }

  public function testAppendToWithEmptyImage() {
    $item = new Item('image', 'caption');
    $subitems = $this
      ->getMockBuilder(SubItems::class)
      ->setConstructorArgs(array($item))
      ->getMock();
    $subitems
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));
    $item->subitems($subitems);
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => '')))
    );
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<listitem title="caption"/>',
      $item->getXML()
    );
  }

  public function testAppendToWithActionParameters() {
    $listview = $this->createMock(\Papaya\UI\ListView::class);
    $listview
      ->expects($this->once())
      ->method('parameterGroup')
      ->will($this->returnValue('group'));
    $reference = $this->createMock(\Papaya\UI\Reference::class);
    $reference
      ->expects($this->once())
      ->method('setParameters')
      ->with(array('foo' => 'bar'), 'group');
    $reference
      ->expects($this->once())
      ->method('getRelative')
      ->will($this->returnValue('#success'));
    $collection = $this
      ->getMockBuilder(Items::class)
      ->setConstructorArgs(array($listview))
      ->getMock();
    $collection
      ->expects($this->once())
      ->method('reference')
      ->will($this->returnValue($reference));
    $collection
      ->expects($this->once())
      ->method('owner')
      ->will($this->returnValue($listview));

    $item = new Item('image', 'caption', array('foo' => 'bar'));
    $item->collection($collection);
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<listitem title="caption" image="test.gif" href="#success"/>',
      $item->getXML()
    );
  }

  public function testAppendToWithIndentation() {
    $item = new Item('image', 'caption');
    $item->indentation = 3;
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<listitem title="caption" image="test.gif" indent="3"/>',
      $item->getXML()
    );
  }

  public function testAppendToWithColumnSpan() {
    $item = new Item('image', 'caption');
    $item->columnSpan = 3;
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<listitem title="caption" image="test.gif" span="3"/>',
      $item->getXML()
    );
  }

  public function testAppendToWithSelected() {
    $item = new Item('image', 'caption');
    $item->selected = TRUE;
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<listitem title="caption" image="test.gif" selected="selected"/>',
      $item->getXML()
    );
  }

  public function testAppendToWithEmphasized() {
    $item = new Item('image', 'caption');
    $item->emphased = TRUE;
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<listitem title="caption" image="test.gif" emphasized="emphasized" emphased="emphased"/>',
      $item->getXML()
    );
  }

  public function testAppendToWithColumnSpanReadFromListView() {
    $columns = $this
      ->getMockBuilder(Columns::class)
      ->disableOriginalConstructor()
      ->getMock();
    $columns
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(42));
    $listview = $this->createMock(\Papaya\UI\ListView::class);
    $listview
      ->expects($this->once())
      ->method('columns')
      ->will($this->returnValue($columns));
    $collection = $this
      ->getMockBuilder(Items::class)
      ->disableOriginalConstructor()
      ->getMock();
    $collection
      ->expects($this->once())
      ->method('owner')
      ->will($this->returnValue($listview));
    $item = new Item('image', 'caption');
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $item->collection($collection);
    $item->columnSpan = -1;
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<listitem title="caption" image="test.gif" span="42"/>',
      $item->getXML()
    );
  }

  public function testAppendToWithText() {
    $item = new Item('image', 'caption');
    $item->text = 'sample text';
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<listitem title="caption" image="test.gif" subtitle="sample text"/>',
      $item->getXML()
    );
  }

  public function testAppendToWithHint() {
    $item = new Item('image', 'caption');
    $item->hint = 'sample hint';
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<listitem title="caption" image="test.gif" hint="sample hint"/>',
      $item->getXML()
    );
  }
}
