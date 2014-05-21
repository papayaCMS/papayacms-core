<?php
require_once(dirname(__FILE__).'/../../../../../../../../bootstrap.php');

class PapayaUiDialogFieldFactoryProfileInputDateTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInputDate::getField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 'some value'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileInputDate();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldInputDate', $field = $profile->getField());
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInputDate::getField
   */
  public function testGetFieldWithHint() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 'some value',
        'hint' => 'Some hint text'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileInputDate();
    $profile->options($options);
    $field = $profile->getField();
    $this->assertSame('Some hint text', $field->getHint());
  }
}