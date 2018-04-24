<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiControlCommandToolbarTest extends PapayaTestCase {

  /**
  * @covers PapayaUiControlCommandToolbar
  */
  public function testConstructor() {
    $command = new PapayaUiControlCommandToolbar_TestProxy(
      $elements = $this->getMock('PapayaUiToolbarElements')
    );
    $this->assertSame($elements, $command->elements());
  }

  /**
  * @covers PapayaUiControlCommandToolbar
  */
  public function testGetAfterSet() {
    $command = new PapayaUiControlCommandToolbar_TestProxy(
      $this->getMock('PapayaUiToolbarElements')
    );
    $command->elements($elements = $this->getMock('PapayaUiToolbarElements'));
    $this->assertSame($elements, $command->elements());
  }

  /**
  * @covers PapayaUiControlCommandToolbar
  */
  public function testAppendTo() {
    $elements = $this->getMock('PapayaUiToolbarElements');
    $elements
      ->expects($this->once())
      ->method('add')
      ->with($this->isInstanceOf('PapayaUiToolbarElement'));

    $dom = new PapayaXmlDocument();
    $dom->appendElement('test');
    $command = new PapayaUiControlCommandToolbar_TestProxy($elements);
    $command->testCase = $this;
    $command->appendTo($dom->documentElement);
    $this->assertEquals('<test/>', $dom->documentElement->saveXml());
  }

}

class PapayaUiControlCommandToolbar_TestProxy extends PapayaUiControlCommandToolbar {

  public $testCase = NULL;

  public function appendToolbarElements() {
    $this->elements()->add($this->testCase->getMock('PapayaUiToolbarElement'));
  }
}
