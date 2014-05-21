<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaUiDialogElementDescriptionLinkTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogElementDescriptionLink::appendTo
  */
  public function testAppendTo() {
    $reference = $this->getMock('PapayaUiReference');
    $reference
      ->expects($this->once())
      ->method('getRelative')
      ->will($this->returnValue('./success.php'));
    $description = new PapayaUiDialogElementDescriptionLink();
    $description->reference($reference);
    $this->assertEquals(
      '<link href="./success.php"/>',
      $description->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogElementDescriptionLink::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->getMock('PapayaUiReference');
    $description = new PapayaUiDialogElementDescriptionLink();
    $this->assertSame(
      $reference, $description->reference($reference)
    );
  }

  /**
  * @covers PapayaUiDialogElementDescriptionLink::reference
  */
  public function testReferenceGetImplicitCreate() {
    $description = new PapayaUiDialogElementDescriptionLink();
    $this->assertInstanceOf(
      'PapayaUiReference', $description->reference()
    );
  }
}