<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaUiListviewItemTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewItem::__construct
  */
  public function testConstructor() {
    $item = new PapayaUiListviewItem('image', 'caption');
    $this->assertAttributeEquals(
      'image', '_image', $item
    );
    $this->assertAttributeEquals(
      'caption', '_caption', $item
    );
  }

  /**
  * @covers PapayaUiListviewItem::__construct
  */
  public function testConstructorWithOptionalParameters() {
    $item = new PapayaUiListviewItem('image', 'caption', array('id' => '42'));
    $this->assertAttributeEquals(
      array('id' => '42'), '_actionParameters', $item
    );
  }

  /**
  * @covers PapayaUiListviewItem::setActionParameters
  */
  public function testPropertyActionParameters() {
    $item = new PapayaUiListviewItem('', '');
    $item->actionParameters = array('id' => '42');
    $this->assertEquals(
      array('id' => '42'), $item->actionParameters
    );
  }

  /**
  * @covers PapayaUiListviewItem::setIndentation
  */
  public function testPropertyIndentation() {
    $item = new PapayaUiListviewItem('', '');
    $item->indentation = 2;
    $this->assertEquals(
      2, $item->indentation
    );
  }

  /**
  * @covers PapayaUiListviewItem::setIndentation
  */
  public function testPropertyIndentationWithNegativeValueExpectingException() {
    $item = new PapayaUiListviewItem('', '');
    $this->setExpectedException(
      'InvalidArgumentException',
      'InvalidArgumentException: $indentation must be greater or equal zero.'
    );
    $item->indentation = -2;
  }

  /**
  * @covers PapayaUiListviewItem::getListview
  */
  public function testGetListview() {
    $listview = $this->getMock('PapayaUiListview');
    $items = $this->getMock('PapayaUiListviewItems', array(), array($listview));
    $items
      ->expects($this->once())
      ->method('owner')
      ->will($this->returnValue($listview));
    $item = new PapayaUiListviewItem('', '');
    $item->collection($items);
    $this->assertInstanceOf(
      'PapayaUiListview', $item->getListview()
    );
  }

  /**
  * @covers PapayaUiListviewItem::collection
  */
  public function testCollectionGetAfterSet() {
    $items = $this
      ->getMockBuilder('PapayaUiListviewItems')
      ->disableOriginalConstructor()
      ->getMock();
    $item = new PapayaUiListviewItem('', '');
    $item->collection($items);
    $this->assertSame(
      $items, $item->collection()
    );
  }

  /**
  * @covers PapayaUiListviewItem::subitems
  */
  public function testSubitemsGetAfterSet() {
    $item = new PapayaUiListviewItem('', '');
    $subitems = $this->getMock('PapayaUiListviewSubitems', array(), array($item));
    $subitems
      ->expects($this->once())
      ->method('owner')
      ->with($this->isInstanceOf('PapayaUiListviewItem'));
    $this->assertSame(
      $subitems, $item->subitems($subitems)
    );
  }

  /**
  * @covers PapayaUiListviewItem::subitems
  */
  public function testSubitemsImplicitCreate() {
    $item = new PapayaUiListviewItem('', '');
    $this->assertInstanceOf(
      'PapayaUiListviewSubitems', $item->subitems()
    );
    $this->assertSame(
      $item, $item->subitems()->owner()
    );
  }

  /**
  * @covers PapayaUiListviewItem::node
  */
  public function testNodeGetAfterSet() {
    $item = new PapayaUiListviewItem('', '');
    $node = $this->getMock('PapayaUiListviewItemNode', array(), array($item));
    $this->assertSame(
      $node, $item->node($node)
    );
  }

  /**
  * @covers PapayaUiListviewItem::node
  */
  public function testNodeImplicitCreate() {
    $item = new PapayaUiListviewItem('', '');
    $this->assertInstanceOf(
      'PapayaUiListviewItemNode', $item->node()
    );
    $this->assertSame(
      $item, $item->node()->item
    );
  }

  /**
  * @covers PapayaUiListviewItem::reference
  */
  public function testReferenceGetAfterSet() {
    $item = new PapayaUiListviewItem('', '');
    $item->reference($reference = $this->getMock('PapayaUiReference'));
    $this->assertSame($reference, $item->reference());
  }


  /**
  * @covers PapayaUiListviewItem::reference
  */
  public function testReferenceGetImpliciteCreate() {
    $item = new PapayaUiListviewItem('', '', array('foo' => 'bar'));
    $item->papaya($this->mockPapaya()->application());
    $reference = $item->reference();
    $this->assertSame($reference->papaya(), $item->papaya());
    $this->assertEquals(array('foo' => 'bar'), $item->actionParameters);
  }

  /**
  * @covers PapayaUiListviewItem::reference
  */
  public function testReferenceGetFromCollection() {
    $reference = $this->getMock('PapayaUiReference');
    $listview = $this->getMock('PapayaUiListview');
    $collection = $this->getMock('PapayaUiListviewItems', array(), array($listview));
    $collection
      ->expects($this->once())
      ->method('reference')
      ->will($this->returnValue($reference));
    $collection
      ->expects($this->once())
      ->method('owner')
      ->will($this->returnValue($listview));
    $item = new PapayaUiListviewItem('', '');
    $item->collection($collection);
    $this->assertInstanceOf('PapayaUiReference', $item->reference());
    $this->assertNotSame($reference, $item->reference());
  }

  /**
  * @covers PapayaUiListviewItem::appendTo
  */
  public function testAppendTo() {
    $item = new PapayaUiListviewItem('image', 'caption');
    $node = $this->getMock('PapayaUiListviewItemNode', array(), array($item));
    $node
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $subitems = $this->getMock('PapayaUiListviewSubitems', array(), array($item));
    $subitems
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $item->node($node);
    $item->subitems($subitems);
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $this->assertEquals(
      '<listitem title="caption" image="test.gif"/>',
      $item->getXml()
    );
  }

  /**
  * @covers PapayaUiListviewItem::appendTo
  */
  public function testAppendToWithEmptyImage() {
    $item = new PapayaUiListviewItem('image', 'caption');
    $subitems = $this->getMock('PapayaUiListviewSubitems', array(), array($item));
    $subitems
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $item->subitems($subitems);
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => '')))
    );
    $this->assertEquals(
      '<listitem title="caption"/>',
      $item->getXml()
    );
  }

  /**
  * @covers PapayaUiListviewItem::appendTo
  */
  public function testAppendToWithActionParameters() {
    $listview = $this->getMock('PapayaUiListview');
    $listview
      ->expects($this->once())
      ->method('parameterGroup')
      ->will($this->returnValue('group'));
    $reference = $this->getMock('PapayaUiReference');
    $reference
      ->expects($this->once())
      ->method('setParameters')
      ->with(array('foo' => 'bar'), 'group');
    $reference
      ->expects($this->once())
      ->method('getRelative')
      ->will($this->returnValue('#success'));
    $collection = $this->getMock('PapayaUiListviewItems', array(), array($listview));
    $collection
      ->expects($this->once())
      ->method('reference')
      ->will($this->returnValue($reference));
    $collection
      ->expects($this->once())
      ->method('owner')
      ->will($this->returnValue($listview));

    $item = new PapayaUiListviewItem('image', 'caption', array('foo' => 'bar'));
    $item->collection($collection);
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $this->assertEquals(
      '<listitem title="caption" image="test.gif" href="#success"/>',
      $item->getXml()
    );
  }

  /**
  * @covers PapayaUiListviewItem::appendTo
  */
  public function testAppendToWithIndentation() {
    $item = new PapayaUiListviewItem('image', 'caption');
    $item->indentation = 3;
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $this->assertEquals(
      '<listitem title="caption" image="test.gif" indent="3"/>',
      $item->getXml()
    );
  }

  /**
  * @covers PapayaUiListviewItem::appendTo
  * @covers PapayaUiListviewItem::getColumnSpan
  */
  public function testAppendToWithColumnSpan() {
    $item = new PapayaUiListviewItem('image', 'caption');
    $item->columnSpan = 3;
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $this->assertEquals(
      '<listitem title="caption" image="test.gif" span="3"/>',
      $item->getXml()
    );
  }

  /**
  * @covers PapayaUiListviewItem::appendTo
  */
  public function testAppendToWithSelected() {
    $item = new PapayaUiListviewItem('image', 'caption');
    $item->selected = TRUE;
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $this->assertEquals(
      '<listitem title="caption" image="test.gif" selected="selected"/>',
      $item->getXml()
    );
  }

  /**
  * @covers PapayaUiListviewItem::appendTo
  */
  public function testAppendToWithEmphased() {
    $item = new PapayaUiListviewItem('image', 'caption');
    $item->emphased = TRUE;
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $this->assertEquals(
      '<listitem title="caption" image="test.gif" emphased="emphased"/>',
      $item->getXml()
    );
  }

  /**
  * @covers PapayaUiListviewItem::appendTo
  * @covers PapayaUiListviewItem::getColumnSpan
  */
  public function testAppendToWithColumnSpanReadFromListview() {
    $columns = $this
      ->getMockBuilder('PapayaUiListviewColumns')
      ->disableOriginalConstructor()
      ->getMock();
    $columns
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(42));
    $listview = $this->getMock('PapayaUiListview');
    $listview
      ->expects($this->once())
      ->method('columns')
      ->will($this->returnValue($columns));
    $collection = $this
      ->getMockBuilder('PapayaUiListviewItems')
      ->disableOriginalConstructor()
      ->getMock();
    $collection
      ->expects($this->once())
      ->method('owner')
      ->will($this->returnValue($listview));
    $item = new PapayaUiListviewItem('image', 'caption');
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $item->collection($collection);
    $item->columnSpan = -1;
    $this->assertEquals(
      '<listitem title="caption" image="test.gif" span="42"/>',
      $item->getXml()
    );
  }

  /**
  * @covers PapayaUiListviewItem::appendTo
  */
  public function testAppendToWithText() {
    $item = new PapayaUiListviewItem('image', 'caption');
    $item->text = 'sample text';
    $item->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'test.gif')))
    );
    $this->assertEquals(
      '<listitem title="caption" image="test.gif" subtitle="sample text"/>',
      $item->getXml()
    );
  }
}