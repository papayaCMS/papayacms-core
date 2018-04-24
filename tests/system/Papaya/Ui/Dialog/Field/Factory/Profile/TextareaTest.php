<?php
require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaUiDialogFieldFactoryProfileTextareaTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileTextarea::getField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'textareafield',
        'caption' => 'Input',
        'default' => 'some value'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileTextarea();
    $profile->options($options);
    $this->assertInstanceOf('PapayaUiDialogFieldTextarea', $field = $profile->getField());
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileTextarea::getField
   */
  public function testGetFieldDisabled() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'textareafield',
        'caption' => 'Input',
        'default' => 'some value',
        'disabled' => TRUE
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileTextarea();
    $profile->options($options);
    $field = $profile->getField();
    $this->assertTrue($field->getDisabled());
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileTextarea::getField
   */
  public function testGetFieldWithHint() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'textareafield',
        'caption' => 'Input',
        'default' => 'some value',
        'hint' => 'Some hint text'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileTextarea();
    $profile->options($options);
    $field = $profile->getField();
    $this->assertSame('Some hint text', $field->getHint());
  }
}
