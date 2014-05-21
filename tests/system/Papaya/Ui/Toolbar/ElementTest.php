<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaUiToolbarElementTest extends PapayaTestCase {

  /**
  * @covers PapayaUiToolbarElement::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->getMock('PapayaUiReference');
    $button = new PapayaUiToolbarElement_TestProxy();
    $button->reference($reference);
    $this->assertSame(
      $reference, $button->reference()
    );
  }

  /**
  * @covers PapayaUiToolbarElement::reference
  */
  public function testReferenceGetImplicitCreate() {
    $button = new PapayaUiToolbarElement_TestProxy();
    $button->papaya(
      $application = $this->mockPapaya()->application()
    );
    $this->assertInstanceOf(
      'PapayaUiReference', $button->reference()
    );
    $this->assertSame(
      $application, $button->reference()->papaya()
    );
  }

}

class PapayaUiToolbarElement_TestProxy extends PapayaUiToolbarElement {

  public function appendTo(PapayaXmlElement $parent) {
  }
}
