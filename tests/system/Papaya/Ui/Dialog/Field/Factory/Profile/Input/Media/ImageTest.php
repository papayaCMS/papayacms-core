<?php
require_once __DIR__.'/../../../../../../../../../bootstrap.php';

class PapayaUiDialogFieldFactoryProfileInputMediaImageTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInputMediaImage::getField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileInputMediaImage();
    $profile->options($options);
    $this->assertInstanceOf(PapayaUiDialogFieldInputMediaImage::class, $field = $profile->getField());
  }
}
