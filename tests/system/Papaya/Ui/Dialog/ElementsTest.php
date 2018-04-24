<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiDialogElementsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogElements::__construct
  */
  public function testConstructorWithOwner() {
    $dialog = $this->getMock('PapayaUiDialog', array(), array(new stdClass()));
    $elements = new PapayaUiDialogElements_TestProxy($dialog);
    $this->assertSame(
      $dialog, $elements->owner()
    );
  }

  /**
  * @covers PapayaUiDialogElements::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('dummy');
    $element = $this->getMock('PapayaUiDialogElement', array('owner', 'appendTo'));
    $element
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $elements = new PapayaUiDialogElements_TestProxy();
    $elements->add($element);
    $elements->appendTo($node);
  }

  /**
  * @covers PapayaUiDialogElements::collect
  */
  public function testCollect() {
    $element = $this->getMock(
      'PapayaUiDialogElement', array('owner', 'appendTo', 'collect')
    );
    $element
      ->expects($this->once())
      ->method('collect');
    $elements = new PapayaUiDialogElements_TestProxy();
    $elements->add($element);
    $elements->collect();
  }
}

class PapayaUiDialogElements_TestProxy extends PapayaUiDialogElements {
}
