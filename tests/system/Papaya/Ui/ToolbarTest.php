<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaUiToolbarTest extends PapayaTestCase {

  /**
  * @covers PapayaUiToolbar::elements
  */
  public function testElementsGetAfterSet() {
    $menu = new PapayaUiToolbar();
    $elements = $this->getMock('PapayaUiToolbarElements', array(), array($menu));
    $elements
      ->expects($this->once())
      ->method('owner')
      ->with($this->isInstanceOf('PapayaUiToolbar'));
    $this->assertSame(
      $elements, $menu->elements($elements)
    );
  }

  /**
  * @covers PapayaUiToolbar::elements
  */
  public function testElementsImplicitCreate() {
    $menu = new PapayaUiToolbar();
    $this->assertInstanceOf(
      'PapayaUiToolbarElements', $menu->elements()
    );
    $this->assertSame(
      $menu, $menu->elements()->owner()
    );
  }

  /**
  * @covers PapayaUiToolbar::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $parent = $dom->appendElement('sample');
    $menu = new PapayaUiToolbar();
    $elements = $this->getMock('PapayaUiToolbarElements', array(), array($menu));
    $elements
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $elements
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $menu->elements($elements);
    $menu->appendTo($parent);
    $this->assertEquals(
      '<sample><toolbar/></sample>',
      $dom->saveXml($parent)
    );
  }

  /**
  * @covers PapayaUiToolbar::appendTo
  */
  public function testAppendToWithoutElements() {
    $dom = new PapayaXmlDocument();
    $parent = $dom->appendElement('sample');
    $menu = new PapayaUiToolbar();
    $elements = $this->getMock('PapayaUiToolbarElements', array(), array($menu));
    $elements
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(0));
    $menu->elements($elements);
    $menu->appendTo($parent);
    $this->assertEquals(
      '<sample/>',
      $dom->saveXml($parent)
    );
  }
}
