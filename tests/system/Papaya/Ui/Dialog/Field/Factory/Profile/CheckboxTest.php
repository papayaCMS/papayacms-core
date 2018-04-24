<?php
require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaUiDialogFieldFactoryProfileCheckboxTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileCheckbox
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'chebkoxfield',
        'caption' => 'Label',
        'default' => TRUE
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileCheckbox();
    $profile->options($options);
    $this->assertInstanceOf(PapayaUiDialogFieldInputCheckbox::class, $field = $profile->getField());
  }
}
