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
  * @covers \PapayaUiListviewItem::__construct
  */
  public function testConstructor() {
    $item = new \PapayaUiListviewItem('image', 'caption');
    $this->assertAttributeEquals(
      'image', '_image', $item
    );
    $this->assertAttributeEquals(
      'caption', '_caption', $item
    );
  }

  /**
  * @covers \PapayaUiListviewItem::__construct
  */
  public function testConstructorWithOptionalParameters() {
    $item = new \PapayaUiListviewItem('image', 'caption', array('id' => '42'));
    $this->assertAttributeEquals(
      array('id' => '42'), '_actionParameters', $item
    );
  }

  /**
  * @covers \PapayaUiListviewItem::setActionParameters
  */
  public function testPropertyActionParameters() {
    $item = new \PapayaUiListviewItem('', '');
    $item->actionParameters = array('id' => '42');
    $this->assertEquals(
      array('id' => '42'), $item->actionParameters
    );
  }

  /**
  * @covers \PapayaUiListviewItem::setIndentation
  */
  public function testPropertyIndentation() {
    $item = new \PapayaUiListviewItem('', '');
    $item->indentation = 2;
    $this->assertEquals(
      2, $item->indentation
    );
  }

  /**
  * @covers \PapayaUiListviewItem::setIndentation
  */
  public function testPropertyIndentationWithNegativeValueExpectingException() {
    $item = new \PapayaUiListviewItem('', '');
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('InvalidArgumentException: $indentation must be greater or equal zero.');
    $item->indentation = -2;
  }

  /**
  * @covers \PapayaUiListviewItem::getListview
  */
  public function testGetListview() {
    $listview = $this->createMock(\PapayaUiListview::class);
    $items = $this
      ->getMockBuilder(\PapayaUiListviewItems::class)
      ->setConstructorArgs(array($listview))
      ->getMock();
    $items
      ->expects($this->once())
      ->method('owner')
      ->will($this->returnValue($listview));
    $item = new \PapayaUiListviewItem('', '');
    $item->collection($items);
    $this->assertInstanceOf(
      \PapayaUiListview::class, $item->getListview()
    );
  }

  /**
  * @covers \PapayaUiListviewItem::collection
  */
  public function testCollectionGetAfterSet() {
    $items = $this
      ->getMockBuilder(\PapayaUiListviewItems::class)
      ->disableOriginalConstructor()
      ->getMock();
    $item = new \PapayaUiListviewItem('', '');
    $item->collection($items);
    $this->assertSame(
      $items, $item->collection()
    );
  }

  /**
  * @covers \PapayaUiListviewItem::subitems
  */
  public function testSubitemsGetAfterSet() {
    $item = new \PapayaUiListviewItem('', '');
    $subitems = $this
      ->getMockBuilder(\PapayaUiListviewSubitems::class)
      ->setConstructorArgs(array($item))
      ->getMock();
    $subitems
      ->expects($this->once())
      ->method('owner')
      ->with($this->isInstanceOf(\PapayaUiListviewItem::class));
    $this->assertSame(
      $subitems, $item->subitems($subitems)
    );
  }

  /**
  * @covers \PapayaUiListviewItem::subitems
  */
  public function testSubitemsImplicitCreate() {
    $item = new \PapayaUiListviewItem('', '');
    $this->assertInstanceOf(
      \PapayaUiListviewSubitems::class, $item->subitems()
    );
    $this->assertSame(
      $item, $item->subitems()->owner()
    );
  }

  /**
  * @covers \PapayaUiListviewItem::node
  */
  public function testNodeGetAfterSet() {
    $item = new \PapayaUiListviewItem('', '');
    $node = $this
      ->getMockBuilder(\PapayaUiListviewItemNode::class)
      ->setConstructorArgs(array($item))
      ->getMock();
    $this->assertSame(
      $node, $item->node($node)
    );
  }

  /**
  * @covers \PapayaUiListviewItem::node
  */
  public function testNodeImplicitCreate() {
    $item = new \PapayaUiListviewItem('', '');
    $this->assertInstanceOf(
      \PapayaUiListviewItemNode::class, $item->node()
    );
    $this->assertSame(
      $item, $item->node()->item
    );
  }

  /**
  * @covers \PapayaUiListviewItem::reference
  */
  public function testReferenceGetAfterSet() {
    $item = new \PapayaUiListviewItem('', '');
    $item->reference($reference = $this->createMock(\PapayaUiReference::class));
    $this->assertSame($reference, $item->reference());
  }


  /**
  * @covers \PapayaUiListviewItem::reference
  */
  public function testReferenceGetImplicitCreate() {
    $item = new \PapayaUiListviewItem('', '', array('foo' => 'bar'));
    $item->papaya($this->mockPapaya()->application());
    $reference = $item->reference();
    $this->assertSame($reference->papaya(), $item->papaya());
    $this->assertEquals(array('foo' => 'bar'), $item->actionParameters);
  }

  /**
  * @covers \PapayaUiListviewItem::reference
  */
  public function testReferenceGetFromCollection() {
    $reference = $this->createMock(\PapayaUiReference::class);
    $listview = $this->createMock(\PapayaUiListview::class);
    $collection = $this
      ->getMockBuilder(\PapayaUiListviewItems::class)
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
    $item = new \PapayaUiListviewItem('', '');
    $item->collection($collection);
    $this->assertInstanceOf(\PapayaUiReference::class, $item->reference());
    $this->assertNotSame($reference, $item->reference());
  }

  /**
  * @covers \PapayaUiListviewItem::appendTo
  */
  public function testAppendTo() {
    $item = new \PapayaUiListviewItem('image', 'caption');
    $node = $this
      ->getMockBuilder(\PapayaUiListviewItemNode::class)
      ->setConstructorArgs(array($item))
      ->getMock();
    $node
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $subitems = $this
      ->getMockBuilder(\PapayaUiListviewSubitems::class)
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
  * @covers \PapayaUiListviewItem::appendTo
  */
  public function testAppendToWithEmptyImage() {
    $item = new \PapayaUiListviewItem('image', 'caption');
    $subitems = $this
      ->getMockBuilder(\PapayaUiListviewSubitems::class)
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
  * @covers \PapayaUiListviewItem::appendTo
  */
  public function testAppendToWithActionParameters() {
    $listview = $this->createMock(\PapayaUiListview::class);
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
      ->getMockBuilder(\PapayaUiListviewItems::class)
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

    $item = new \PapayaUiListviewItem('image', 'caption', array('foo' => 'bar'));
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
  * @covers \PapayaUiListviewItem::appendTo
  */
  public function testAppendToWithIndentation() {
    $item = new \PapayaUiListviewItem('image', 'caption');
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
  * @covers \PapayaUiListviewItem::appendTo
  * @covers \PapayaUiListviewItem::getColumnSpan
  */
  public function testAppendToWithColumnSpan() {
    $item = new \PapayaUiListviewItem('image', 'caption');
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
  * @covers \PapayaUiListviewItem::appendTo
  */
  public function testAppendToWithSelected() {
    $item = new \PapayaUiListviewItem('image', 'caption');
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
  * @covers \PapayaUiListviewItem::appendTo
  */
  public function testAppendToWithEmphased() {
    $item = new \PapayaUiListviewItem('image', 'caption');
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
  * @covers \PapayaUiListviewItem::appendTo
  * @covers \PapayaUiListviewItem::getColumnSpan
  */
  public function testAppendToWithColumnSpanReadFromListview() {
    $columns = $this
      ->getMockBuilder(\PapayaUiListviewColumns::class)
      ->disableOriginalConstructor()
      ->getMock();
    $columns
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(42));
    $listview = $this->createMock(\PapayaUiListview::class);
    $listview
      ->expects($this->once())
      ->method('columns')
      ->will($this->returnValue($columns));
    $collection = $this
      ->getMockBuilder(\PapayaUiListviewItems::class)
      ->disableOriginalConstructor()
      ->getMock();
    $collection
      ->expects($this->once())
      ->method('owner')
      ->will($this->returnValue($listview));
    $item = new \PapayaUiListviewItem('image', 'caption');
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
  * @covers \PapayaUiListviewItem::appendTo
  */
  public function testAppendToWithText() {
    $item = new \PapayaUiListviewItem('image', 'caption');
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
