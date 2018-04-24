<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiDialogButtonsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogButtons::add
  */
  public function testAdd() {
    $button = $this->getMock('PapayaUiDialogButton', array('owner', 'appendTo'));
    $buttons = new PapayaUiDialogButtons();
    $buttons->add($button);
    $this->assertAttributeEquals(
      array($button), '_items', $buttons
    );
  }
}
