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

class PapayaUiListviewItemTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Listview\Item::__construct
  */
  public function testConstructor() {
    $item = new \Papaya\Ui\Listview\Item('image', 'caption');
    $this->assertAttributeEquals(
      'image', '_image', $item
    );
    $this->assertAttributeEquals(
      'caption', '_caption', $item
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Item::__construct
  */
  public function testConstructorWithOptionalParameters() {
    $item = new \Papaya\Ui\Listview\Item('image', 'caption', array('id' => '42'));
    $this->assertAttributeEquals(
      array('id' => '42'), '_actionParameters', $item
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Item::setActionParameters
  */
  public function testPropertyActionParameters() {
    $item = new \Papaya\Ui\Listview\Item('', '');
    $item->actionParameters = array('id' => '42');
    $this->assertEquals(
      array('id' => '42'), $item->actionParameters
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Item::setIndentation
  */
  public function testPropertyIndentation() {
    $item = new \Papaya\Ui\Listview\Item('', '');
    $item->indentation = 2;
    $this->assertEquals(
      2, $item->indentation
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Item::setIndentation
  */
  public function testPropertyIndentationWithNegativeValueExpectingException() {
    $item = new \Papaya\Ui\Listview\Item('', '');
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('InvalidArgumentException: $indentation must be greater or equal zero.');
    $item->indentation = -2;
  }

  /**
  * @covers \Papaya\Ui\Listview\Item::getListview
  */
  public function testGetListview() {
    $listview = $this->createMock(\Papaya\Ui\Listview::class);
    $items = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Items::class)
      ->setConstructorArgs(array($listview))
      ->getMock();
    $items
      ->expects($this->once())
      ->method('owner')
      ->will($this->returnValue($listview));
    $item = new \Papaya\Ui\Listview\Item('', '');
    $item->collection($items);
    $this->assertInstanceOf(
      \Papaya\Ui\Listview::class, $item->getListview()
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Item::collection
  */
  public function testCollectionGetAfterSet() {
    $items = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Items::class)
      ->disableOriginalConstructor()
      ->getMock();
    $item = new \Papaya\Ui\Listview\Item('', '');
    $item->collection($items);
    $this->assertSame(
      $items, $item->collection()
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Item::subitems
  */
  public function testSubitemsGetAfterSet() {
    $item = new \Papaya\Ui\Listview\Item('', '');
    $subitems = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Subitems::class)
      ->setConstructorArgs(array($item))
      ->getMock();
    $subitems
      ->expects($this->once())
      ->method('owner')
      ->with($this->isInstanceOf(\Papaya\Ui\Listview\Item::class));
    $this->assertSame(
      $subitems, $item->subitems($subitems)
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Item::subitems
  */
  public function testSubitemsImplicitCreate() {
    $item = new \Papaya\Ui\Listview\Item('', '');
    $this->assertInstanceOf(
      \Papaya\Ui\Listview\Subitems::class, $item->subitems()
    );
    $this->assertSame(
      $item, $item->subitems()->owner()
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Item::node
  */
  public function testNodeGetAfterSet() {
    $item = new \Papaya\Ui\Listview\Item('', '');
    $node = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Item\Node::class)
      ->setConstructorArgs(array($item))
      ->getMock();
    $this->assertSame(
      $node, $item->node($node)
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Item::node
  */
  public function testNodeImplicitCreate() {
    $item = new \Papaya\Ui\Listview\Item('', '');
    $this->assertInstanceOf(
      \Papaya\Ui\Listview\Item\Node::class, $item->node()
    );
    $this->assertSame(
      $item, $item->node()->item
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Item::reference
  */
  public function testReferenceGetAfterSet() {
    $item = new \Papaya\Ui\Listview\Item('', '');
    $item->reference($reference = $this->createMock(\PapayaUiReference::class));
    $this->assertSame($reference, $item->reference());
  }


  /**
  * @covers \Papaya\Ui\Listview\Item::reference
  */
  public function testReferenceGetImplicitCreate() {
    $item = new \Papaya\Ui\Listview\Item('', '', array('foo' => 'bar'));
    $item->papaya($this->mockPapaya()->application());
    $reference = $item->reference();
    $this->assertSame($reference->papaya(), $item->papaya());
    $this->assertEquals(array('foo' => 'bar'), $item->actionParameters);
  }

  /**
  * @covers \Papaya\Ui\Listview\Item::reference
  */
  public function testReferenceGetFromCollection() {
    $reference = $this->createMock(\PapayaUiReference::class);
    $listview = $this->createMock(\Papaya\Ui\Listview::class);
    $collection = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Items::class)
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
    $item = new \Papaya\Ui\Listview\Item('', '');
    $item->collection($collection);
    $this->assertInstanceOf(\PapayaUiReference::class, $item->reference());
    $this->assertNotSame($reference, $item->reference());
  }

  /**
  * @covers \Papaya\Ui\Listview\Item::appendTo
  */
  public function testAppendTo() {
    $item = new \Papaya\Ui\Listview\Item('image', 'caption');
    $node = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Item\Node::class)
      ->setConstructorArgs(array($item))
      ->getMock();
    $node
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $subitems = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Subitems::class)
      ->setConstructorArgs(array($item))
      ->getMock();
    $subitems
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $item->node($node);
    $item->subitems($subitems);
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<listitem title="caption" image="test.gif"/>',
      $item->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Item::appendTo
  */
  public function testAppendToWithEmptyImage() {
    $item = new \Papaya\Ui\Listview\Item('image', 'caption');
    $subitems = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Subitems::class)
      ->setConstructorArgs(array($item))
      ->getMock();
    $subitems
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $item->subitems($subitems);
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => '')))
    );
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<listitem title="caption"/>',
      $item->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Item::appendTo
  */
  public function testAppendToWithActionParameters() {
    $listview = $this->createMock(\Papaya\Ui\Listview::class);
    $listview
      ->expects($this->once())
      ->method('parameterGroup')
      ->will($this->returnValue('group'));
    $reference = $this->createMock(\PapayaUiReference::class);
    $reference
      ->expects($this->once())
      ->method('setParameters')
      ->with(array('foo' => 'bar'), 'group');
    $reference
      ->expects($this->once())
      ->method('getRelative')
      ->will($this->returnValue('#success'));
    $collection = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Items::class)
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

    $item = new \Papaya\Ui\Listview\Item('image', 'caption', array('foo' => 'bar'));
    $item->collection($collection);
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<listitem title="caption" image="test.gif" href="#success"/>',
      $item->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Item::appendTo
  */
  public function testAppendToWithIndentation() {
    $item = new \Papaya\Ui\Listview\Item('image', 'caption');
    $item->indentation = 3;
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<listitem title="caption" image="test.gif" indent="3"/>',
      $item->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Item::appendTo
  * @covers \Papaya\Ui\Listview\Item::getColumnSpan
  */
  public function testAppendToWithColumnSpan() {
    $item = new \Papaya\Ui\Listview\Item('image', 'caption');
    $item->columnSpan = 3;
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<listitem title="caption" image="test.gif" span="3"/>',
      $item->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Item::appendTo
  */
  public function testAppendToWithSelected() {
    $item = new \Papaya\Ui\Listview\Item('image', 'caption');
    $item->selected = TRUE;
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<listitem title="caption" image="test.gif" selected="selected"/>',
      $item->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Item::appendTo
  */
  public function testAppendToWithEmphased() {
    $item = new \Papaya\Ui\Listview\Item('image', 'caption');
    $item->emphased = TRUE;
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<listitem title="caption" image="test.gif" emphased="emphased"/>',
      $item->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Item::appendTo
  * @covers \Papaya\Ui\Listview\Item::getColumnSpan
  */
  public function testAppendToWithColumnSpanReadFromListview() {
    $columns = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Columns::class)
      ->disableOriginalConstructor()
      ->getMock();
    $columns
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(42));
    $listview = $this->createMock(\Papaya\Ui\Listview::class);
    $listview
      ->expects($this->once())
      ->method('columns')
      ->will($this->returnValue($columns));
    $collection = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Items::class)
      ->disableOriginalConstructor()
      ->getMock();
    $collection
      ->expects($this->once())
      ->method('owner')
      ->will($this->returnValue($listview));
    $item = new \Papaya\Ui\Listview\Item('image', 'caption');
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $item->collection($collection);
    $item->columnSpan = -1;
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<listitem title="caption" image="test.gif" span="42"/>',
      $item->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Item::appendTo
  */
  public function testAppendToWithText() {
    $item = new \Papaya\Ui\Listview\Item('image', 'caption');
    $item->text = 'sample text';
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<listitem title="caption" image="test.gif" subtitle="sample text"/>',
      $item->getXml()
    );
  }
}
