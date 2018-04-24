<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiNavigationItemsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiNavigationItems::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->getMock('PapayaUiReference');
    $items = new PapayaUiNavigationItems();
    $this->assertSame(
      $reference, $items->reference($reference)
    );
  }

  /**
  * @covers PapayaUiNavigationItems::reference
  */
  public function testReferenceImpliciteCreate() {
    $items = new PapayaUiNavigationItems();
    $items->papaya($papaya = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      'PapayaUiReference', $reference = $items->reference()
    );
    $this->assertSame(
      $papaya, $reference->papaya()
    );
  }

}
