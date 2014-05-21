<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaUiListviewSubitemImageTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewSubitemImage::__construct
  */
  public function testConstructor() {
    $subitem = new PapayaUiListviewSubitemImage('sample.png');
    $this->assertEquals(
      'sample.png', $subitem->image
    );
  }

  /**
  * @covers PapayaUiListviewSubitemImage::__construct
  */
  public function testConstructorWithOptionalParameters() {
    $subitem = new PapayaUiListviewSubitemImage('sample.png', 'quickinfo', array('foo' => 'bar'));
    $this->assertEquals(
      'quickinfo', $subitem->hint
    );
    $this->assertEquals(
      array('foo' => 'bar'), $subitem->actionParameters
    );
  }

  /**
  * @covers PapayaUiListviewSubitemImage::reference
  */
  public function testReferenceGetAfterSet() {
    $subitem = new PapayaUiListviewSubitemImage('sample.png', 'quickinfo', array('foo' => 'bar'));
    $subitem->reference($reference = $this->getMock('PapayaUiReference'));
    $this->assertSame(
      $reference, $subitem->reference()
    );
  }

  /**
  * @covers PapayaUiListviewSubitemImage::reference
  */
  public function testReferenceGetFromListview() {
    $reference = $this->getMock('PapayaUiReference');
    $listview = $this->getMock('PapayaUiListview');
    $listview
      ->expects($this->once())
      ->method('reference')
      ->will($this->returnValue($reference));
    $collection = $this
      ->getMockBuilder('PapayaUiListviewSubitems')
      ->disableOriginalConstructor()
      ->getMock();
    $collection
      ->expects($this->once())
      ->method('getListview')
      ->will($this->returnValue($listview));

    $subitem = new PapayaUiListviewSubitemImage('sample.png', 'quickinfo', array('foo' => 'bar'));
    $subitem->collection($collection);
    $this->assertEquals(
      $reference, $subitem->reference()
    );
  }

  /**
  * @covers PapayaUiListviewSubitemImage::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('test');
    $subitem = new PapayaUiListviewSubitemImage('image');
    $subitem->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'sample.png')))
    );
    $subitem->align = PapayaUiOptionAlign::CENTER;
    $subitem->appendTo($dom->documentElement);
    $this->assertEquals(
      '<test><subitem align="center"><glyph src="sample.png"/></subitem></test>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiListviewSubitemImage::appendTo
  */
  public function testAppendToWithHint() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('test');
    $subitem = new PapayaUiListviewSubitemImage('image', 'quickinfo');
    $subitem->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'sample.png')))
    );
    $subitem->align = PapayaUiOptionAlign::CENTER;
    $subitem->appendTo($dom->documentElement);
    $this->assertEquals(
      '<test><subitem align="center"><glyph src="sample.png" hint="quickinfo"/></subitem></test>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiListviewSubitemImage::appendTo
  * @covers PapayaUiListviewSubitemImage::getUrl
  */
  public function testAppendToWithReference() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('test');
    $reference = $this->getMock('PapayaUiReference');
    $reference
      ->expects($this->once())
      ->method('setParameters')
      ->with(array('foo' => 'bar'));
    $reference
      ->expects($this->once())
      ->method('getRelative')
      ->will($this->returnValue('sample.html'));
    $subitem = new PapayaUiListviewSubitemImage('image', '', array('foo' => 'bar'));
    $subitem->reference = $reference;
    $subitem->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'sample.png')))
    );
    $subitem->align = PapayaUiOptionAlign::CENTER;
    $subitem->appendTo($dom->documentElement);
    $this->assertEquals(
      '<test><subitem align="center"><glyph src="sample.png" href="sample.html"/></subitem></test>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiListviewSubitemImage::appendTo
  * @covers PapayaUiListviewSubitemImage::getUrl
  */
  public function testAppendToWithReferenceFromListview() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('test');
    $reference = $this->getMock('PapayaUiReference');
    $reference
      ->expects($this->once())
      ->method('setParameters')
      ->with(array('foo' => 'bar'), 'group');
    $reference
      ->expects($this->once())
      ->method('getRelative')
      ->will($this->returnValue('sample.html'));
    $listview = $this->getMock('PapayaUiListview');
    $listview
      ->expects($this->once())
      ->method('reference')
      ->will($this->returnValue($reference));
    $listview
      ->expects($this->once())
      ->method('parameterGroup')
      ->will($this->returnValue('group'));
    $collection = $this->getMock(
      'PapayaUiListviewSubitems',
      array(),
      array($this->getMock('PapayaUiListviewItem', array(), array('', '')))
    );
    $collection
      ->expects($this->exactly(2))
      ->method('getListview')
      ->will($this->returnValue($listview));
    $subitem = new PapayaUiListviewSubitemImage('image', '', array('foo' => 'bar'));
    $subitem->collection($collection);
    $subitem->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'sample.png')))
    );
    $subitem->align = PapayaUiOptionAlign::CENTER;
    $subitem->appendTo($dom->documentElement);
    $this->assertEquals(
      '<test><subitem align="center"><glyph src="sample.png" href="sample.html"/></subitem></test>',
      $dom->saveXml($dom->documentElement)
    );
  }
}