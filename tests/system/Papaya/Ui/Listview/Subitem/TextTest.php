<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiListviewSubitemTextTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewSubitemText::__construct
  */
  public function testConstructor() {
    $subitem = new PapayaUiListviewSubitemText('Sample text');
    $this->assertEquals(
      'Sample text', $subitem->text
    );
  }

  /**
  * @covers PapayaUiListviewSubitemText::__construct
  */
  public function testConstructorWithOptionalParameters() {
    $subitem = new PapayaUiListviewSubitemText('Sample text', array('foo' => 'bar'));
    $this->assertEquals(
      array('foo' => 'bar'), $subitem->actionParameters
    );
  }

  /**
  * @covers PapayaUiListviewSubitemText::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('test');
    $subitem = new PapayaUiListviewSubitemText('Sample text');
    $subitem->align = PapayaUiOptionAlign::RIGHT;
    $subitem->appendTo($dom->documentElement);
    $this->assertEquals(
      '<test><subitem align="right">Sample text</subitem></test>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
   * @covers PapayaUiListviewSubitemText::appendTo
   * @covers PapayaUiListviewSubitemText::getUrl
   */
  public function testAppendToWithActionParameters() {
    $reference = $this->mockPapaya()->reference('http://www.example.html');
    $reference->expects($this->once())->method('setParameters')->with(array('foo' => 'bar'));
    $dom = new PapayaXmlDocument();
    $dom->appendElement('test');
    $subitem = new PapayaUiListviewSubitemText('Sample text');
    $subitem->reference($reference);
    $subitem->setActionParameters(array('foo' => 'bar'));
    $subitem->appendTo($dom->documentElement);
    $this->assertEquals(
      '<test><subitem align="left"><a href="http://www.example.html">'
      .'Sample text</a></subitem></test>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiListviewSubitemText::reference
  */
  public function testReferenceGetSet() {
    $reference = $this->mockPapaya()->reference();
    $subitem = new PapayaUiListviewSubitemText('Sample Text');
    $subitem->reference($reference);
    $this->assertSame($reference, $subitem->reference());
  }

  /**
  * @covers PapayaUiListviewSubitemText::reference
  */
  public function testReferenceGetFromListview() {
    $reference = $this->createMock(PapayaUiReference::class);
    $listview = $this->createMock(PapayaUiListview::class);
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
    $subitem = new PapayaUiListviewSubitemText('quickinfo', array('foo' => 'bar'));
    $subitem->collection($collection);
    $this->assertEquals(
      $reference, $subitem->reference()
    );
  }
}
