<?php
require_once(dirname(__FILE__).'/../../../../../../../../bootstrap.php');

class PapayaUiDialogFieldFactoryProfileInputPasswordTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInputPassword::getField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 'some value'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileInputPassword();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldInputPassword', $field = $profile->getField());
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInputPassword::getField
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
    $profile = new PapayaUiDialogFieldFactoryProfileInputPassword();
    $profile->options($options);
    $field = $profile->getField();
    $this->assertSame('Some hint text', $field->getHint());
  }
}