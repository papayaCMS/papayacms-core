<?php
require_once(dirname(__FILE__).'/../../../../../../../bootstrap.php');

class PapayaUiDialogFieldFactoryProfileColorTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileColor
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'colorfield',
        'caption' => 'Color',
        'default' => '#FFF'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileColor();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldInputColor', $profile->getField());
  }
}