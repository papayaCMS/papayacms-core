<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiHierarchyItemTest extends PapayaTestCase {

  /**
  * @covers PapayaUiHierarchyItem::__construct
  */
  public function testConstructor() {
    $item = new PapayaUiHierarchyItem('sample');
    $this->assertEquals('sample', $item->caption);
  }

  /**
   * @covers PapayaUiHierarchyItem::appendTo
   */
  public function testAppendTo() {
    $item = new PapayaUiHierarchyItem('sample');
    $item->image = 'items-page';
    $item->papaya(
      $this->mockPapaya()->application(
        array(
          'Images' => array(
            'items-page' => 'page.png'
          )
        )
      )
    );
    $this->assertAppendedXmlEqualsXmlFragment(
      '<item caption="sample" image="page.png" mode="both"/>',
      $item
    );
  }

  /**
   * @covers PapayaUiHierarchyItem::appendTo
   */
  public function testAppendToWithHint() {
    $item = new PapayaUiHierarchyItem('sample');
    $item->image = 'items-page';
    $item->hint = 'Quick Info';
    $item->papaya(
      $this->mockPapaya()->application(
        array(
          'Images' => array(
            'items-page' => 'page.png'
          )
        )
      )
    );
    $this->assertAppendedXmlEqualsXmlFragment(
      '<item caption="sample" hint="Quick Info" image="page.png" mode="both"/>',
      $item
    );
  }

  /**
  * @covers PapayaUiHierarchyItem::appendTo
  */
  public function testAppendToWithReference() {
    $reference = $this->getMock('PapayaUiReference');
    $reference
      ->expects($this->once())
      ->method('getRelative')
      ->will($this->returnValue('link.html'));

    $item = new PapayaUiHierarchyItem('sample');
    $item->image = 'items-page';
    $item->reference($reference);
    $item->papaya(
      $this->mockPapaya()->application(
        array(
          'Images' => array(
            'items-page' => 'page.png'
          )
        )
      )
    );
    $this->assertAppendedXmlEqualsXmlFragment(
      '<item caption="sample" image="page.png" mode="both" href="link.html"/>',
      $item
    );
  }

  /**
  * @covers PapayaUiHierarchyItem::reference
  */
  public function testItemsGetAfterSet() {
    $item = new PapayaUiHierarchyItem('sample');
    $reference = $this->getMock('PapayaUiReference');
    $this->assertSame(
      $reference, $item->reference($reference)
    );
  }

  /**
  * @covers PapayaUiHierarchyItem::reference
  */
  public function testItemsGetWithImpliciteCreate() {
    $item = new PapayaUiHierarchyItem('sample');
    $item->papaya($papaya = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      'PapayaUiReference', $item->reference()
    );
    $this->assertSame(
      $papaya, $item->papaya()
    );
  }

  /**
  * @covers PapayaUiHierarchyItem::setDisplayMode
  */
  public function testSetDisplayMode() {
    $item = new PapayaUiHierarchyItem('sample');
    $item->displayMode = PapayaUiHierarchyItem::DISPLAY_TEXT_ONLY;
    $this->assertEquals(
      PapayaUiHierarchyItem::DISPLAY_TEXT_ONLY, $item->displayMode
    );
  }

  /**
  * @covers PapayaUiHierarchyItem::setDisplayMode
  */
  public function testSetDisplayModeExpectingException() {
    $item = new PapayaUiHierarchyItem('sample');
    try {
      $item->displayMode = -99;
    } catch (OutOfBoundsException $e) {
      $this->assertEquals(
        'Invalid display mode for "PapayaUiHierarchyItem".',
        $e->getMessage()
      );
    }
  }
}
