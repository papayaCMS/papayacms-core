<?php
require_once __DIR__.'/../../../../../../../../../../bootstrap.php';

class PapayaUiDialogFieldFactoryProfileInputMediaImageResizedTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInputMediaImageResized::getField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileInputMediaImageResized();
    $profile->options($options);
    $this->assertInstanceOf(PapayaUiDialogFieldInputMediaImageResized::class, $field = $profile->getField());
  }
}
