<?php
require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaUiDialogFieldFactoryProfileMessageTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileMessage::getField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'default' => 'some value',
        'parameters' => PapayaMessage::SEVERITY_INFO
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileMessage();
    $profile->options($options);
    $this->assertInstanceOf(PapayaUiDialogFieldMessage::class, $field = $profile->getField());
  }
}
