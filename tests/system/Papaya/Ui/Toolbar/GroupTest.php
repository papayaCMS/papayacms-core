<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiToolbarGroupTest extends PapayaTestCase {

  /**
  * @covers PapayaUiToolbarGroup::__construct
  */
  public function testConstructor() {
    $group = new PapayaUiToolbarGroup('group caption');
    $this->assertEquals(
      'group caption', $group->caption
    );
  }

  /**
  * @covers PapayaUiToolbarGroup::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('sample');
    $group = new PapayaUiToolbarGroup('group caption');
    $elements = $this->getMock(PapayaUiToolbarElements::class, array(), array($group));
    $elements
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $elements
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));
    $group->elements($elements);
    $this->assertInstanceOf(PapayaXmlElement::class, $group->appendTo($dom->documentElement));
    $this->assertEquals(
      '<group title="group caption"/>',
      $dom->documentElement->saveFragment()
    );
  }

  /**
  * @covers PapayaUiToolbarGroup::appendTo
  */
  public function testAppendToWithoutElements() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('sample');
    $group = new PapayaUiToolbarGroup('group caption');
    $elements = $this->getMock(PapayaUiToolbarElements::class, array(), array($group));
    $elements
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(0));
    $group->elements($elements);
    $this->assertNull($group->appendTo($dom->documentElement));
    $this->assertEquals(
      '<sample/>',
      $dom->documentElement->saveXml()
    );
  }
}
