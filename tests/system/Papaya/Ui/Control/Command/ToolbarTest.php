<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiControlCommandToolbarTest extends PapayaTestCase {

  /**
  * @covers PapayaUiControlCommandToolbar
  */
  public function testConstructor() {
    $command = new PapayaUiControlCommandToolbar_TestProxy(
      $elements = $this->createMock(PapayaUiToolbarElements::class)
    );
    $this->assertSame($elements, $command->elements());
  }

  /**
  * @covers PapayaUiControlCommandToolbar
  */
  public function testGetAfterSet() {
    $command = new PapayaUiControlCommandToolbar_TestProxy(
      $this->createMock(PapayaUiToolbarElements::class)
    );
    $command->elements($elements = $this->createMock(PapayaUiToolbarElements::class));
    $this->assertSame($elements, $command->elements());
  }

  /**
  * @covers PapayaUiControlCommandToolbar
  */
  public function testAppendTo() {
    $elements = $this->createMock(PapayaUiToolbarElements::class);
    $elements
      ->expects($this->once())
      ->method('add')
      ->with($this->isInstanceOf(PapayaUiToolbarElement::class));

    $dom = new PapayaXmlDocument();
    $dom->appendElement('test');
    $command = new PapayaUiControlCommandToolbar_TestProxy($elements);
    $command->testElement = $this->createMock(PapayaUiToolbarElement::class);
    $command->appendTo($dom->documentElement);
    $this->assertEquals('<test/>', $dom->documentElement->saveXml());
  }

}

class PapayaUiControlCommandToolbar_TestProxy extends PapayaUiControlCommandToolbar {

  public $testElement;

  public function appendToolbarElements() {
    $this->elements()->add($this->testElement);
  }
}
