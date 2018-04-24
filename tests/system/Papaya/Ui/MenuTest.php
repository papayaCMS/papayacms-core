<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaUiMenuTest extends PapayaTestCase {

  /**
  * @covers PapayaUiMenu::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('sample');
    $menu = new PapayaUiMenu();
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
    $menu->appendTo($dom->documentElement);
    $this->assertEquals(
      '<sample><menu/></sample>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiMenu::appendTo
  */
  public function testAppendToWithIdentifier() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('sample');
    $menu = new PapayaUiMenu();
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
    $menu->identifier = 'sample_id';
    $menu->appendTo($dom->documentElement);
    $this->assertEquals(
      '<sample><menu ident="sample_id"/></sample>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiMenu::appendTo
  */
  public function testAppendToWithoutElements() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('sample');
    $menu = new PapayaUiMenu();
    $elements = $this->getMock('PapayaUiToolbarElements', array(), array($menu));
    $elements
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(0));
    $menu->elements($elements);
    $menu->appendTo($dom->documentElement);
    $this->assertEquals(
      '<sample/>',
      $dom->saveXml($dom->documentElement)
    );
  }
}
