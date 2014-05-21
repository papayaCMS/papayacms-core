<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaUiDialogButtonSubmitTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogButtonSubmit::__construct
  */
  public function testConstructor() {
    $button = new PapayaUiDialogButtonSubmit('Test Caption');
    $this->assertAttributeEquals(
      'Test Caption',
      '_caption',
      $button
    );
  }

  /**
  * @covers PapayaUiDialogButtonSubmit::__construct
  */
  public function testConstructorWithAlignment() {
    $button = new PapayaUiDialogButtonSubmit(
      'Test Caption', PapayaUiDialogButton::ALIGN_LEFT
    );
    $this->assertAttributeEquals(
      PapayaUiDialogButton::ALIGN_LEFT,
      '_align',
      $button
    );
  }

  /**
  * @covers PapayaUiDialogButtonSubmit::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('test');
    $button = new PapayaUiDialogButtonSubmit('Test Caption');
    $button->appendTo($dom->documentElement);
    $this->assertEquals(
      '<test><button type="submit" align="right">Test Caption</button></test>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiDialogButtonSubmit::appendTo
  */
  public function testAppendToWithInterfaceStringObject() {
    $caption = $this->getMock('PapayaUiString', array('__toString'), array('.'));
    $caption
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('Test Caption'));
    $dom = new PapayaXmlDocument();
    $dom->appendElement('test');
    $button = new PapayaUiDialogButtonSubmit(
      $caption, PapayaUiDialogButton::ALIGN_LEFT
    );
    $button->appendTo($dom->documentElement);
    $this->assertEquals(
      '<test><button type="submit" align="left">Test Caption</button></test>',
      $dom->saveXml($dom->documentElement)
    );
  }
}